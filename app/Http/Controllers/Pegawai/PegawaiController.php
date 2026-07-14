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
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 1. Ambil filter bulan (default: bulan sekarang 'YYYY-MM')
        $bulanDipilih = $request->get('bulan', date('Y-m'));
        $tahun = explode('-', $bulanDipilih)[0];
        $bulan = explode('-', $bulanDipilih)[1];

        // Buat objek Carbon dari bulan yang dipilih
        $awalBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $akhirBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta')->endOfMonth();
        
        $tanggalDibuat = \Carbon\Carbon::parse($user->created_at)->setTimezone('Asia/Jakarta')->startOfDay();
        $sekarang = \Carbon\Carbon::now('Asia/Jakarta');
        $hariIni = \Carbon\Carbon::today('Asia/Jakarta');

        $totalHariKerja = 0;

        // ATURAN 1: Pengecekan apakah pegawai sudah terdaftar/aktif di bulan yang dipilih
        // Jika bulan yang dipilih terjadi SEBELUM akun dibuat, atau SEBELUM bulan berjalan (Masa Depan)
        if ($awalBulan->format('Y-m') < $tanggalDibuat->format('Y-m') || $awalBulan->format('Y-m') > $sekarang->format('Y-m')) {
            // Hari kerja langsung 0, tidak perlu jalankan loop
            $totalHariKerja = 0;
        } else {
            // Tentukan batas awal hitung loop
            if ($tanggalDibuat->format('Y-m') === $bulanDipilih) {
                $tanggalMulaiHitung = $tanggalDibuat->copy();
            } else {
                $tanggalMulaiHitung = $awalBulan->copy();
            }

            // Tentukan batas akhir hitung loop
            if ($hariIni->format('Y-m') === $bulanDipilih) {
                // ATURAN 2: Jika jam sekarang sebelum 07:30 WIB, hari ini belum dihitung sebagai hari kerja
                if ($sekarang->format('H:i') < '07:30') {
                    $tanggalAkhirHitung = $hariIni->copy()->subDay(); // Cuma hitung sampai kemarin
                } else {
                    $tanggalAkhirHitung = $hariIni->copy(); // Sudah masuk jam kerja, hitung sampai hari ini
                }
            } else {
                $tanggalAkhirHitung = $akhirBulan->copy();
            }

            // Jalankan Loop Perhitungan Hari Kerja (Senin - Jumat)
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

        // 4. Tarik data kehadiran dari database
        $riwayatAbsen = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'desc')
            ->get();

        // 5. Hitung statistik riil
        $totalHadir = $riwayatAbsen->whereIn('status', ['Tepat Waktu', 'Hadir'])->count();
        $totalTerlambat = $riwayatAbsen->where('status', 'Terlambat')->count();
        
        $totalPresensiIsi = $riwayatAbsen->count();
        $tanpaKeterangan = $totalHariKerja - $totalPresensiIsi;
        if ($tanpaKeterangan < 0) $tanpaKeterangan = 0;

        // 6. Pencarian data absensi hari ini (WIB)
        $absenHariIni = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $hariIni->format('Y-m-d'))
            ->first();

        if ($absenHariIni) {
            $statusHariIni = 'Sudah Absen (' . $absenHariIni->status . ' - ' . date('H:i', strtotime($absenHariIni->jam_masuk)) . ')';
        } else {
            if ($sekarang->format('H:i') < '07:30' || $sekarang->format('H:i') > '17:00') {
                $statusHariIni = 'Sekarang di Luar Jam Kerja';
            } else {
                $statusHariIni = 'Belum Absen';
            }
        }

        $isJamKerja = ($sekarang->format('H:i') >= '07:30' && $sekarang->format('H:i') <= '17:00');

        return view('pegawai.dashboard', compact(
            'riwayatAbsen', 
            'totalHariKerja', 
            'totalHadir', 
            'totalTerlambat', 
            'tanpaKeterangan',
            'bulanDipilih',
            'statusHariIni',
            'absenHariIni',
            'isJamKerja'
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

        $kantorLat = env('KANTOR_LATITUDE');
        $kantorLng = env('KANTOR_LONGITUDE');
        $radiusMaksimal = env('KANTOR_RADIUS_METER', 20);

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

        $status = ($jamSekarang <= '08:30:00') ? 'Tepat Waktu' : 'Terlambat';

        DB::table('absensi')->insert([
            'user_id' => Auth::id(),
            'tanggal' => $tanggalHariIni,
            'jam_masuk' => $jamSekarang,
            'latitude' => $pegawaiLat,
            'longitude' => $pegawaiLng,
            'status' => $status,
            'created_at' => $sekarang,
            'updated_at' => $sekarang,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen berhasil dicatat! Status: ' . $status
        ]);
    }
}