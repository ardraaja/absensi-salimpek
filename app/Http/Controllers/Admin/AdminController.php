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
        $bulanDipilih = $request->get('bulan', date('Y-m'));
        $tahun = explode('-', $bulanDipilih)[0];
        $bulan = explode('-', $bulanDipilih)[1];

        $sekarang = Carbon::now('Asia/Jakarta');
        $hariIni = Carbon::today('Asia/Jakarta');

        $kantorLat = DB::table('settings')->where('key', 'kantor_latitude')->value('value') ?? '-1.0825000';
        $kantorLng = DB::table('settings')->where('key', 'kantor_longitude')->value('value') ?? '100.8250000';
        $kantorRadius = DB::table('settings')->where('key', 'kantor_radius_meter')->value('value') ?? '50';

        $totalPegawai = DB::table('users')->where('role', 'pegawai')->count();

        $absenHariIni = DB::table('absensi')
            ->whereDate('tanggal', $hariIni)
            ->get();

        $hadir = $absenHariIni->where('status_masuk', 'Tepat Waktu')->count();
        $terlambat = $absenHariIni->whereIn('status_masuk', ['TL 1', 'TL 2', 'TL 3', 'TL 4'])->count();
        
        if ($sekarang->format('H:i') < '07:30') {
            $belumAbsen = 0;
        } else {
            $belumAbsen = $totalPegawai - $absenHariIni->count();
            if ($belumAbsen < 0) $belumAbsen = 0;
        }

        $daftarPegawai = DB::table('users')
            ->where('role', 'pegawai')
            ->get()
            ->map(function($pegawai) use ($tahun, $bulan, $bulanDipilih, $sekarang, $hariIni) {
                $absenBulanIni = DB::table('absensi')
                    ->where('user_id', $pegawai->id)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->get();

                $pegawai->count_hadir = $absenBulanIni->where('status_masuk', 'Tepat Waktu')->count();
                $pegawai->count_telat = $absenBulanIni->whereIn('status_masuk', ['TL 1', 'TL 2', 'TL 3', 'TL 4'])->count();
                
                $tanggalDibuat = Carbon::parse($pegawai->created_at)->setTimezone('Asia/Jakarta')->startOfDay();
                $awalBulan = Carbon::createFromDate($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
                $akhirBulan = Carbon::createFromDate($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta')->endOfMonth();

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

                $pegawai->riwayat_json = $absenBulanIni->map(function($a) {
                    return [
                        'hari' => Carbon::parse($a->tanggal)->translatedFormat('l, d F Y'),
                        'jam_masuk' => $a->jam_masuk ? date('H:i', strtotime($a->jam_masuk)) . ' WIB' : '-',
                        'status_masuk' => $a->status_masuk ?? '-',
                        'jam_pulang' => $a->jam_pulang ? date('H:i', strtotime($a->jam_pulang)) . ' WIB' : '-',
                        'status_pulang' => $a->status_pulang ?? '-',
                        'lat' => $a->latitude ?? '-',
                        'lng' => $a->longitude ?? '-'
                    ];
                });

                return $pegawai;
            });

        return view('admin.dashboard', compact(
            'totalPegawai', 
            'hadir', 
            'terlambat', 
            'belumAbsen', 
            'bulanDipilih', 
            'daftarPegawai',
            'kantorLat',
            'kantorLng',
            'kantorRadius'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'jabatan' => 'required|string|max:255',
            'status_kerja' => 'required|string',
        ]);

        DB::table('users')->insert([
            'name' => $request->name,
            'nip' => $request->nip ?? '-',
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'jabatan' => $request->jabatan,
            'status_kerja' => $request->status_kerja,
            'role' => 'pegawai',
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Akun pegawai baru berhasil didaftarkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
        ]);

        DB::table('users')->where('id', $id)->update([
            'name' => $request->name,
            'nip' => $request->nip ?? '-',
            'jabatan' => $request->jabatan,
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Data akun pegawai berhasil diperbarui!');
    }

    public function updateLokasiKantor(Request $request)
    {
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $radius = $request->input('radius');

        if (!$lat || !$lng || !$radius) {
            return response()->json([
                'success' => false,
                'message' => 'Data latitude, longitude, atau radius tidak boleh kosong!'
            ], 400);
        }

        // Eksekusi Update ke Database
        DB::table('settings')->where('key', 'kantor_latitude')->update([
            'value' => (string)$lat,
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        DB::table('settings')->where('key', 'kantor_longitude')->update([
            'value' => (string)$lng,
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        DB::table('settings')->where('key', 'kantor_radius_meter')->update([
            'value' => (string)$radius,
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi kantor berhasil diperbarui ke database!'
        ]);
    }

    public function destroy($id)
    {
        DB::table('absensi')->where('user_id', $id)->delete();
        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Akun pegawai dan seluruh riwayat absensinya telah dihapus permanen!');
    }
}