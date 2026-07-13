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

        // 2. Tentukan batas awal dan akhir bulan yang dipilih secara objektif
        $awalBulan = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $akhirBulan = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
        
        $tanggalDibuat = Carbon::parse($user->created_at)->startOfDay();
        $hariIni = Carbon::today();

        // Tentukan batas awal hitung untuk bulan tersebut
        // Jika sedang melihat bulan berjalan dan akun baru dibuat bulan ini, hitung sejak akun dibuat
        if ($tanggalDibuat->format('Y-m') === $bulanDipilih) {
            $tanggalMulaiHitung = $tanggalDibuat->copy();
        } else {
            $tanggalMulaiHitung = $awalBulan->copy();
        }

        // Tentukan batas akhir hitung untuk bulan tersebut
        if ($hariIni->format('Y-m') === $bulanDipilih) {
            // Jika bulan sekarang, batasnya hanya sampai HARI INI
            $tanggalAkhirHitung = $hariIni->copy();
        } else {
            // Jika bulan lalu, batasnya sampai akhir bulan tersebut
            $tanggalAkhirHitung = $akhirBulan->copy();
        }

        // 3. Jalankan Loop Hitung Hari Kerja (Hanya jika rentang tanggalnya valid/masuk akal)
        $totalHariKerja = 0;
        if ($tanggalMulaiHitung->lte($tanggalAkhirHitung) && $tanggalDibuat->startOfMonth()->lte($akhirBulan)) {
            $tempTanggal = $tanggalMulaiHitung->copy();
            while ($tempTanggal->lte($tanggalAkhirHitung)) {
                if ($tempTanggal->isWeekday()) {
                    $totalHariKerja++;
                }
                $tempTanggal->addDay();
            }
        }

        // 4. Tarik data kehadiran dari database
        $riwayatAbsen = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'desc')
            ->get();

        // 5. Hitung statistik riil (Tepat Waktu & Hadir digabung jadi satu badge)
        $totalHadir = $riwayatAbsen->whereIn('status', ['Tepat Waktu', 'Hadir'])->count();
        $totalTerlambat = $riwayatAbsen->where('status', 'Terlambat')->count();
        
        // Alpa = Total hari kerja dilalui dikurangi jumlah data absen yang terisi
        $totalPresensiIsi = $riwayatAbsen->count();
        $tanpaKeterangan = $totalHariKerja - $totalPresensiIsi;
        if ($tanpaKeterangan < 0) $tanpaKeterangan = 0;

        // 6. Cek Absen Hari Ini
        $absenHariIni = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        if ($absenHariIni) {
            $statusHariIni = 'Sudah Absen (' . $absenHariIni->status . ' - ' . date('H:i', strtotime($absenHariIni->jam_masuk)) . ')';
        } else {
            $statusHariIni = 'Belum Absen';
        }

        return view('pegawai.dashboard', compact(
            'riwayatAbsen', 
            'totalHariKerja', 
            'totalHadir', 
            'totalTerlambat', 
            'tanpaKeterangan',
            'bulanDipilih',
            'statusHariIni',
            'absenHariIni'
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