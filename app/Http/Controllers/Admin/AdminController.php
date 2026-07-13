<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil filter bulan (default: bulan sekarang 'YYYY-MM')
        $bulanDipilih = $request->get('bulan', date('Y-m'));
        $tahun = explode('-', $bulanDipilih)[0];
        $bulan = explode('-', $bulanDipilih)[1];

        // 2. Hitung statistik ringkasan untuk hari ini
        $totalPegawai = DB::table('users')->where('role', 'pegawai')->count();

        $absenHariIni = DB::table('absensi')
            ->whereDate('tanggal', \Carbon\Carbon::today())
            ->get();

        $hadir = $absenHariIni->whereIn('status', ['Tepat Waktu', 'Hadir'])->count();
        $terlambat = $absenHariIni->where('status', 'Terlambat')->count();
        
        $belumAbsen = $totalPegawai - $absenHariIni->count();
        if ($belumAbsen < 0) $belumAbsen = 0;

        // 3. Tarik seluruh data pegawai beserta hitungan rekap bulanan dinamis
        $daftarPegawai = DB::table('users')
            ->where('role', 'pegawai')
            ->get()
            ->map(function($pegawai) use ($tahun, $bulan) {
                // Ambil semua absensi milik pegawai ini di bulan yang dipilih
                $absenBulanIni = DB::table('absensi')
                    ->where('user_id', $pegawai->id)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->get();

                // Hitung masing-masing status
                $pegawai->count_hadir = $absenBulanIni->whereIn('status', ['Tepat Waktu', 'Hadir'])->count();
                $pegawai->count_telat = $absenBulanIni->where('status', 'Terlambat')->count();
                
                // Untuk Alpa (Sementara diset 0 atau bisa dikalkulasikan dari total hari kerja jika diperlukan)
                $pegawai->count_alpa = 0; 

                return $pegawai;
            });

        return view('admin.dashboard', compact(
            'totalPegawai', 'hadir', 'terlambat', 'belumAbsen', 'bulanDipilih', 'daftarPegawai'
        ));
    }
}