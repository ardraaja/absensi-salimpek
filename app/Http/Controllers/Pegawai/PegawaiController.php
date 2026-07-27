<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PegawaiController extends Controller
{
    private function hitungTL($jamMasuk)
    {
        $jamBuka = Carbon::createFromFormat('H:i:s', '07:30:00');
        $waktuAbsen = Carbon::createFromFormat('H:i:s', $jamMasuk);

        if ($waktuAbsen->lte($jamBuka)) {
            return 'Tepat Waktu';
        }

        $selisihMenit = $jamBuka->diffInMinutes($waktuAbsen);

        if ($selisihMenit >= 1 && $selisihMenit <= 30) {
            return 'TL 1';
        } elseif ($selisihMenit >= 31 && $selisihMenit <= 60) {
            return 'TL 2';
        } elseif ($selisihMenit >= 61 && $selisihMenit <= 90) {
            return 'TL 3';
        } else {
            return 'TL 4';
        }
    }

    private function hitungPSW($jamPulang)
    {
        $jamPulangResmi = Carbon::createFromFormat('H:i:s', '16:00:00');
        $waktuAbsen = Carbon::createFromFormat('H:i:s', $jamPulang);

        if ($waktuAbsen->gte($jamPulangResmi)) {
            return 'Tepat Waktu';
        }

        $selisihMenit = $waktuAbsen->diffInMinutes($jamPulangResmi);

        if ($selisihMenit >= 1 && $selisihMenit <= 30) {
            return 'PSW 1';
        } elseif ($selisihMenit >= 31 && $selisihMenit <= 60) {
            return 'PSW 2';
        } elseif ($selisihMenit >= 61 && $selisihMenit <= 90) {
            return 'PSW 3';
        } else {
            return 'PSW 4';
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $bulanDipilih = $request->get('bulan', date('Y-m'));
        $tahun = explode('-', $bulanDipilih)[0];
        $bulan = explode('-', $bulanDipilih)[1];

        $awalBulan = Carbon::createFromDate($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $akhirBulan = Carbon::createFromDate($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta')->endOfMonth();
        
        $tanggalDibuat = Carbon::parse($user->created_at)->setTimezone('Asia/Jakarta')->startOfDay();
        $sekarang = Carbon::now('Asia/Jakarta');
        $hariIni = Carbon::today('Asia/Jakarta');

        $totalHariKerja = 0;

        if ($awalBulan->format('Y-m') < $tanggalDibuat->format('Y-m') || $awalBulan->format('Y-m') > $sekarang->format('Y-m')) {
            $totalHariKerja = 0;
        } else {
            if ($tanggalDibuat->format('Y-m') === $bulanDipilih) {
                $tanggalMulaiHitung = $tanggalDibuat->copy();
            } else {
                $tanggalMulaiHitung = $awalBulan->copy();
            }

            if ($hariIni->format('Y-m') === $bulanDipilih) {
                if ($sekarang->format('H:i') < '07:30') {
                    $tanggalAkhirHitung = $hariIni->copy()->subDay();
                } else {
                    $tanggalAkhirHitung = $hariIni->copy();
                }
            } else {
                $tanggalAkhirHitung = $akhirBulan->copy();
            }

            if ($tanggalMulaiHitung->lte($tanggalAkhirHitung)) {
                $tempTanggal = $tanggalMulaiHitung->copy();
                while ($tempTanggal->lte($tanggalAkhirHitung)) {
                    if ($tempTanggal->isWeekday()) {
                        $totalHariKerja++;
                    }
                    $tempTanggal->addDay();
                }
            }
        }

        $riwayatAbsen = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalHadir = $riwayatAbsen->where('status_masuk', 'Tepat Waktu')->count();
        $totalTerlambat = $riwayatAbsen->whereIn('status_masuk', ['TL 1', 'TL 2', 'TL 3', 'TL 4'])->count();
        
        $totalPresensiIsi = $riwayatAbsen->count();
        $tanpaKeterangan = $totalHariKerja - $totalPresensiIsi;
        if ($tanpaKeterangan < 0) $tanpaKeterangan = 0;

        $absenHariIni = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $hariIni->format('Y-m-d'))
            ->first();

        if ($absenHariIni) {
            if ($absenHariIni->jam_pulang != null) {
                $statusHariIni = 'Sudah Absen Lengkap (Masuk: ' . $absenHariIni->status_masuk . ' | Pulang: ' . $absenHariIni->status_pulang . ')';
            } else {
                $statusHariIni = 'Sudah Absen Masuk (' . $absenHariIni->status_masuk . ' - ' . date('H:i', strtotime($absenHariIni->jam_masuk)) . ')';
            }
        } else {
            if ($sekarang->format('H:i') < '07:30' || $sekarang->format('H:i') > '17:00') {
                $statusHariIni = 'Sekarang di Luar Jam Kerja';
            } else {
                $statusHariIni = 'Belum Absen';
            }
        }

        $isJamKerja = ($sekarang->format('H:i') >= '07:30' && $sekarang->format('H:i') <= '17:00');

        $kantorLat = DB::table('settings')->where('key', 'kantor_latitude')->value('value') ?? '-1.0825000';
        $kantorLng = DB::table('settings')->where('key', 'kantor_longitude')->value('value') ?? '100.8250000';

        return view('pegawai.dashboard', compact(
            'riwayatAbsen', 
            'totalHariKerja', 
            'totalHadir', 
            'totalTerlambat', 
            'tanpaKeterangan',
            'bulanDipilih',
            'statusHariIni',
            'absenHariIni',
            'isJamKerja',
            'kantorLat',
            'kantorLng'
        ));
    }

    public function store(Request $request)
    {
        $sekarang = Carbon::now('Asia/Jakarta');
        $jamSekarang = $sekarang->format('H:i:s');
        $tanggalHariIni = $sekarang->format('Y-m-d');

        if ($jamSekarang < '07:30' || $jamSekarang > '17:00') {
            return response()->json([
                'success' => false,
                'message' => 'Absen ditolak! Absen hanya bisa dilakukan antara jam 07:30 - 17:00 WIB.'
            ], 400);
        }

        $pegawaiLat = $request->latitude;
        $pegawaiLng = $request->longitude;

        $settingLat = DB::table('settings')->where('key', 'kantor_latitude')->value('value');
        $settingLng = DB::table('settings')->where('key', 'kantor_longitude')->value('value');
        $settingRadius = DB::table('settings')->where('key', 'kantor_radius_meter')->value('value');

        $kantorLat = $settingLat ?? -1.0825000;
        $kantorLng = $settingLng ?? 100.8250000;
        $radiusMaksimal = $settingRadius ?? 50;

        $earthRadius = 6371000; 
        
        $latFrom = deg2rad($pegawaiLat);
        $lonFrom = deg2rad($pegawaiLng);
        $latTo = deg2rad($kantorLat);
        $lonTo = deg2rad($kantorLng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $jarakMeter = $angle * $earthRadius;

        if ($jarakMeter > $radiusMaksimal) {
            return response()->json([
                'success' => false,
                'message' => 'Anda berada di luar radius kantor! Jarak Anda: ' . round($jarakMeter) . ' meter.'
            ], 400);
        }

        $absenHariIni = DB::table('absensi')
            ->where('user_id', Auth::id())
            ->whereDate('tanggal', $tanggalHariIni)
            ->first();

        if (!$absenHariIni) {
            $statusTL = $this->hitungTL($jamSekarang);

            DB::table('absensi')->insert([
                'user_id' => Auth::id(),
                'tanggal' => $tanggalHariIni,
                'jam_masuk' => $jamSekarang,
                'latitude' => $pegawaiLat,
                'longitude' => $pegawaiLng,
                'status_masuk' => $statusTL,
                'status' => $statusTL,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absen Masuk Berhasil! Status: ' . $statusTL
            ]);

        } else {
            if ($absenHariIni->jam_pulang != null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan Absen Masuk dan Absen Pulang untuk hari ini!'
                ], 400);
            }

            $statusPSW = $this->hitungPSW($jamSekarang);

            DB::table('absensi')
                ->where('id', $absenHariIni->id)
                ->update([
                    'jam_pulang' => $jamSekarang,
                    'status_pulang' => $statusPSW,
                    'updated_at' => $sekarang,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Absen Pulang Berhasil! Status Pulang: ' . $statusPSW
            ]);
        }
    }
}