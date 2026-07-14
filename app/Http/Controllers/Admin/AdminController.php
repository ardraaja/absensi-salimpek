<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil filter bulan (default: bulan sekarang 'YYYY-MM')
        $bulanDipilih = $request->get('bulan', date('Y-m'));
        $tahun = explode('-', $bulanDipilih)[0];
        $bulan = explode('-', $bulanDipilih)[1];

        $sekarang = \Carbon\Carbon::now('Asia/Jakarta');
        $hariIni = \Carbon\Carbon::today('Asia/Jakarta');

        // 2. Hitung statistik ringkasan untuk hari ini (Hanya aktif jika sudah masuk jam kerja 07:30)
        $totalPegawai = DB::table('users')->where('role', 'pegawai')->count();

        $absenHariIni = DB::table('absensi')
            ->whereDate('tanggal', $hariIni)
            ->get();

        $hadir = $absenHariIni->whereIn('status', ['Tepat Waktu', 'Hadir'])->count();
        $terlambat = $absenHariIni->where('status', 'Terlambat')->count();
        
        // FIX: Sebelum jam 07:30 WIB, status 'Belum Absen' dikunci di angka 0
        if ($sekarang->format('H:i') < '07:30') {
            $belumAbsen = 0;
        } else {
            $belumAbsen = $totalPegawai - $absenHariIni->count();
            if ($belumAbsen < 0) $belumAbsen = 0;
        }

        // 3. Tarik seluruh data pegawai beserta hitungan rekap akumulasi bulanan dinamis
        $daftarPegawai = DB::table('users')
            ->where('role', 'pegawai')
            ->get()
            ->map(function($pegawai) use ($tahun, $bulan, $bulanDipilih, $sekarang, $hariIni) {
                // Ambil semua absensi milik pegawai ini di bulan yang dipilih
                $absenBulanIni = DB::table('absensi')
                    ->where('user_id', $pegawai->id)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->get();

                $pegawai->count_hadir = $absenBulanIni->whereIn('status', ['Tepat Waktu', 'Hadir'])->count();
                $pegawai->count_telat = $absenBulanIni->where('status', 'Terlambat')->count();
                
                // Hitung hari kerja dinamis untuk pegawai ini berdasarkan tanggal terdaftar (created_at)
                $tanggalDibuat = \Carbon\Carbon::parse($pegawai->created_at)->setTimezone('Asia/Jakarta')->startOfDay();
                $awalBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
                $akhirBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta')->endOfMonth();

                $totalHariKerjaPegawai = 0;

                if ($awalBulan->format('Y-m') >= $tanggalDibuat->format('Y-m') && $awalBulan->format('Y-m') <= $sekarang->format('Y-m')) {
                    $tanggalMulaiHitung = ($tanggalDibuat->format('Y-m') === $bulanDipilih) ? $tanggalDibuat->copy() : $awalBulan->copy();
                    
                    if ($hariIni->format('Y-m') === $bulanDipilih) {
                        $tanggalAkhirHitung = ($sekarang->format('H:i') < '07:30') ? $hariIni->copy()->subDay() : $hariIni->copy();
                    } else {
                        $tanggalAkhirHitung = $akhirBulan->copy();
                    }

                    if ($tanggalMulaiHitung->lte($tanggalAkhirHitung)) {
                        $tempTanggal = $tanggalMulaiHitung->copy();
                        while ($tempTanggal->lte($tanggalAkhirHitung)) {
                            if ($tempTanggal->isWeekday()) {
                                $totalHariKerjaPegawai++;
                            }
                            $tempTanggal->addDay();
                        }
                    }
                }

                $tanpaKeterangan = $totalHariKerjaPegawai - $absenBulanIni->count();
                $pegawai->count_alpa = ($tanpaKeterangan < 0) ? 0 : $tanpaKeterangan;

                // Lampirkan data riwayat mentah untuk keperluan parsing modal detail via Javascript JSON
                $pegawai->riwayat_json = $absenBulanIni->map(function($a) {
                    return [
                        'hari' => \Carbon\Carbon::parse($a->tanggal)->translatedFormat('l, d F Y'),
                        'jam' => $a->jam_masuk ? date('H:i', strtotime($a->jam_masuk)) . ' WIB' : '-',
                        'status' => $a->status,
                        'lat' => $a->latitude ?? '-',
                        'lng' => $a->longitude ?? '-'
                    ];
                });

                return $pegawai;
            });

        return view('admin.dashboard', compact(
            'totalPegawai', 'hadir', 'terlambat', 'belumAbsen', 'bulanDipilih', 'daftarPegawai'
        ));
    }
    public function store(Request $request)
    {
        // 1. Validasi input data dari form
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'jabatan' => 'required|string|max:255',
            'status_kerja' => 'required|string',
        ]);

        // 2. Simpan data pegawai baru ke tabel users dengan role 'pegawai'
        DB::table('users')->insert([
            'name' => $request->name,
            'nip' => $request->nip ?? '-',
            'email' => $request->email,
            'password' => Hash::make($request->password), // Password di-encrypt aman
            'jabatan' => $request->jabatan,
            'status_kerja' => $request->status_kerja,
            'role' => 'pegawai', // Mengunci hak akses sebagai pegawai
            'created_at' => \Carbon\Carbon::now('Asia/Jakarta'),
            'updated_at' => \Carbon\Carbon::now('Asia/Jakarta'),
        ]);

        // 3. Kembali ke dashboard dengan membawa pesan sukses popup
        return redirect()->route('admin.dashboard')->with('success', 'Akun pegawai baru berhasil didaftarkan!');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
        ]);

        // Update data nama, nip, dan jabatan pegawai ke database
        DB::table('users')->where('id', $id)->update([
            'name' => $request->name,
            'nip' => $request->nip ?? '-',
            'jabatan' => $request->jabatan,
            'updated_at' => \Carbon\Carbon::now('Asia/Jakarta'),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Data akun pegawai berhasil diperbarui!');
    }

    // Fungsi untuk menghapus akun pegawai (Delete)
    public function destroy($id)
    {
        // 1. Hapus semua data riwayat absensi yang terikat dengan user_id pegawai ini terlebih dahulu
        DB::table('absensi')->where('user_id', $id)->delete();

        // 2. Hapus data utama akun pegawai dari tabel users
        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Akun pegawai dan seluruh riwayat absensinya telah dihapus permanen!');
    }
}