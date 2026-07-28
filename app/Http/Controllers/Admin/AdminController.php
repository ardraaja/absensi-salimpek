<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\PengajuanIzin;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $bulanDipilih = $request->get('bulan', date('Y-m'));
        $tahun = explode('-', $bulanDipilih)[0];
        $bulan = explode('-', $bulanDipilih)[1];

        $sekarang = Carbon::now('Asia/Jakarta');
        $hariIni = Carbon::today('Asia/Jakarta');

        // 1. Ambil Pengaturan Lokasi & Jam Kerja Kantor dari Database
        $kantorLat = DB::table('settings')->where('key', 'kantor_latitude')->value('value') ?? '-1.0825000';
        $kantorLng = DB::table('settings')->where('key', 'kantor_longitude')->value('value') ?? '100.8250000';
        $kantorRadius = DB::table('settings')->where('key', 'kantor_radius_meter')->value('value') ?? '50';
        
        $jamMasuk = DB::table('settings')->where('key', 'jam_masuk_resmi')->value('value') ?? '07:30';
        $jamPulang = DB::table('settings')->where('key', 'jam_pulang_resmi')->value('value') ?? '16:00';

        $totalPegawai = DB::table('users')->where('role', 'pegawai')->count();

        // 2. Data Absensi Hari Ini
        $absenHariIni = DB::table('absensi')
            ->whereDate('tanggal', $hariIni)
            ->get();

        $hadir = $absenHariIni->where('status_masuk', 'Tepat Waktu')->count();
        $terlambat = $absenHariIni->whereIn('status_masuk', ['TL 1', 'TL 2', 'TL 3', 'TL 4'])->count();
        
        // TAMBAHAN: Hitung Izin dan DL HARI INI
        $izinDanDlHariIni = PengajuanIzin::where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $hariIni)
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->get();

        $izinHariIni = $izinDanDlHariIni->whereIn('tipe_pengajuan', ['sakit', 'cuti', 'izin_pribadi'])->count();
        $dlHariIni = $izinDanDlHariIni->where('tipe_pengajuan', 'dinas_luar')->count();

        if ($sekarang->format('H:i') < $jamMasuk) {
            $belumAbsen = 0;
        } else {
            // Pengurangan Alpa otomatis jika hari ini ada yang Izin/DL
            $belumAbsen = $totalPegawai - $absenHariIni->count() - $izinHariIni - $dlHariIni;
            if ($belumAbsen < 0) $belumAbsen = 0;
        }

        // 3. Rekapitulasi Daftar Pegawai (Bulanan)
        $daftarPegawai = DB::table('users')
            ->where('role', 'pegawai')
            ->get()
            ->map(function($pegawai) use ($tahun, $bulan, $bulanDipilih, $sekarang, $hariIni, $jamMasuk) {
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
                        $tanggalAkhirHitung = ($sekarang->format('H:i') < $jamMasuk) ? $hariIni->copy()->subDay() : $hariIni->copy();
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

                $approvedIzins = PengajuanIzin::where('user_id', $pegawai->id)
                    ->where('status', 'approved')
                    ->get();

                $totalIzin = 0;
                $totalDL = 0;

                foreach ($approvedIzins as $izin) {
                    $start = Carbon::parse($izin->tanggal_mulai);
                    $end = Carbon::parse($izin->tanggal_selesai);
                    
                    for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                        if ($d->format('Y-m') === $bulanDipilih && $d->isWeekday()) {
                            if (isset($tanggalMulaiHitung) && isset($tanggalAkhirHitung) && $d->between($tanggalMulaiHitung, $tanggalAkhirHitung)) {
                                if ($izin->tipe_pengajuan == 'dinas_luar') {
                                    $totalDL++;
                                } else {
                                    $totalIzin++;
                                }
                            }
                        }
                    }
                }

                $tanpaKeterangan = $totalHariKerjaPegawai - $absenBulanIni->count() - $totalIzin - $totalDL;
                
                $pegawai->count_izin = $totalIzin;
                $pegawai->count_dl = $totalDL;
                $pegawai->count_alpa = ($tanpaKeterangan < 0) ? 0 : $tanpaKeterangan;

                $pegawai->riwayat_json = $absenBulanIni->map(function($a) {
                    return [
                        'id' => $a->id,
                        'hari' => Carbon::parse($a->tanggal)->translatedFormat('l, d F Y'),
                        'jam_masuk' => $a->jam_masuk ? date('H:i:s', strtotime($a->jam_masuk)) : '',
                        'status_masuk' => $a->status_masuk ?? '-',
                        'jam_pulang' => $a->jam_pulang ? date('H:i:s', strtotime($a->jam_pulang)) : '',
                        'status_pulang' => $a->status_pulang ?? '-',
                        'lat' => $a->latitude ?? '-',
                        'lng' => $a->longitude ?? '-'
                    ];
                });

                return $pegawai;
            });

        // 4. Ambil Daftar Pengajuan yang Masih Pending
        $pengajuanPending = PengajuanIzin::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.dashboard', compact(
            'totalPegawai', 
            'hadir', 
            'terlambat', 
            'izinHariIni', 
            'dlHariIni', 
            'belumAbsen', 
            'bulanDipilih', 
            'daftarPegawai',
            'kantorLat',
            'kantorLng',
            'kantorRadius',
            'jamMasuk',
            'jamPulang',
            'pengajuanPending'
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

        DB::table('settings')->updateOrInsert(
            ['key' => 'kantor_latitude'],
            ['value' => (string)$lat, 'updated_at' => Carbon::now('Asia/Jakarta')]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'kantor_longitude'],
            ['value' => (string)$lng, 'updated_at' => Carbon::now('Asia/Jakarta')]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'kantor_radius_meter'],
            ['value' => (string)$radius, 'updated_at' => Carbon::now('Asia/Jakarta')]
        );

        return response()->json([
            'success' => true,
            'message' => 'Lokasi kantor berhasil diperbarui ke database!'
        ]);
    }

    public function updateJamKerja(Request $request)
    {
        $request->validate([
            'jam_masuk' => 'required',
            'jam_pulang' => 'required',
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => 'jam_masuk_resmi'],
            ['value' => $request->jam_masuk, 'updated_at' => Carbon::now('Asia/Jakarta')]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'jam_pulang_resmi'],
            ['value' => $request->jam_pulang, 'updated_at' => Carbon::now('Asia/Jakarta')]
        );

        return response()->json([
            'success' => true,
            'message' => 'Jam kerja resmi berhasil diperbarui!'
        ]);
    }

    public function destroy($id)
    {
        DB::table('absensi')->where('user_id', $id)->delete();
        DB::table('users')->where('id', $id)->delete();
        PengajuanIzin::where('user_id', $id)->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Akun pegawai dan seluruh riwayat absensinya telah dihapus permanen!');
    }

    public function setujuiIzin($id)
    {
        $pengajuan = PengajuanIzin::findOrFail($id);
        $pengajuan->update(['status' => 'approved']);
        
        return redirect()->route('admin.dashboard')->with('success', 'Pengajuan Izin/DL berhasil DISETUJUI.');
    }

    public function tolakIzin(Request $request, $id)
    {
        $request->validate(['catatan_admin' => 'required|string']);
        
        $pengajuan = PengajuanIzin::findOrFail($id);
        $pengajuan->update([
            'status' => 'rejected',
            'catatan_admin' => $request->catatan_admin
        ]);
        
        return redirect()->route('admin.dashboard')->with('success', 'Pengajuan Izin/DL telah DITOLAK.');
    }

    public function tambahIzinManual(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'tipe_pengajuan' => 'required|in:sakit,cuti,izin_pribadi,dinas_luar',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string',
        ]);

        PengajuanIzin::create([
            'user_id' => $request->user_id,
            'tipe_pengajuan' => $request->tipe_pengajuan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan . ' (Diinput Langsung Oleh Admin)',
            'status' => 'approved'
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Status Izin/DL manual berhasil ditambahkan ke rekap pegawai!');
    }

    public function updateAbsenManual(Request $request, $id)
    {
        $request->validate([
            'jam_masuk' => 'nullable',
            'status_masuk' => 'required|string',
            'jam_pulang' => 'nullable',
            'status_pulang' => 'required|string',
        ]);

        DB::table('absensi')->where('id', $id)->update([
            'jam_masuk' => $request->jam_masuk ?: null,
            'status_masuk' => $request->status_masuk,
            'jam_pulang' => $request->jam_pulang ?: null,
            'status_pulang' => $request->status_pulang,
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Data status absensi harian berhasil dikoreksi!');
    }

    // ====================================================================
    // FUNGSI UNTUK GENERATE LAPORAN LENGKAP & DINAMIS
    // ====================================================================
    public function cetakLaporan(Request $request)
    {
        $bulanDipilih = $request->get('bulan', date('Y-m'));
        $tahun = explode('-', $bulanDipilih)[0];
        $bulan = explode('-', $bulanDipilih)[1];

        $hariIni = Carbon::today('Asia/Jakarta');

        // Mengambil data penandatangan dari tabel settings
        $namaWali = DB::table('settings')->where('key', 'nama_wali_nagari')->value('value') ?? 'NAMA WALI NAGARI';
        $nipWali = DB::table('settings')->where('key', 'nip_wali_nagari')->value('value') ?? '-';

        $daftarPegawai = DB::table('users')
            ->where('role', 'pegawai')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($daftarPegawai as $pegawai) {
            $absenBulanIni = DB::table('absensi')
                ->where('user_id', $pegawai->id)
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->get()
                ->keyBy('tanggal');

            $approvedIzins = PengajuanIzin::where('user_id', $pegawai->id)->where('status', 'approved')->get();

            $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
            $detailHarian = [];
            
            $hadir = 0; $telat = 0; $izin = 0; $dl = 0; $alpa = 0;

            // 1. Tentukan tanggal mulai (Mulai tgl 1 ATAU sejak tanggal akun pegawai dibuat)
            $tanggalDibuat = Carbon::parse($pegawai->created_at)->setTimezone('Asia/Jakarta')->startOfDay();
            $startDay = 1;
            if ($tanggalDibuat->format('Y-m') === $bulanDipilih) {
                $startDay = $tanggalDibuat->day; // Jika dibuat bulan ini, mulai dari tgl dibuat
            } elseif ($tanggalDibuat->format('Y-m') > $bulanDipilih) {
                // Jika akun dibuat SETELAH bulan laporan yang dipilih, lewati saja
                $startDay = 1;
                $daysInMonth = 0; 
            }

            // 2. Tentukan batas akhir (Sampai akhir bulan ATAU mentok sampai hari ini saja)
            $endDay = $daysInMonth;
            if ($hariIni->format('Y-m') === $bulanDipilih) {
                $endDay = $hariIni->day; 
            }

            for ($i = $startDay; $i <= $endDay; $i++) {
                $tanggalStr = sprintf('%04d-%02d-%02d', $tahun, $bulan, $i);
                $tanggalObj = Carbon::parse($tanggalStr);
                
                // 3. SKIP HARI LIBUR / WEEKEND (Sabtu & Minggu tidak masuk laporan cetak)
                if ($tanggalObj->isWeekend()) {
                    continue; 
                }

                if (isset($absenBulanIni[$tanggalStr])) {
                    $a = $absenBulanIni[$tanggalStr];
                    $hadir++;
                    if (in_array($a->status_masuk, ['TL 1', 'TL 2', 'TL 3', 'TL 4'])) $telat++;

                    $detailHarian[] = [
                        'tanggal' => $tanggalObj->translatedFormat('d M Y'),
                        'masuk' => $a->jam_masuk ? date('H:i', strtotime($a->jam_masuk)) : '-',
                        'status_masuk' => $a->status_masuk ?? '-',
                        'pulang' => $a->jam_pulang ? date('H:i', strtotime($a->jam_pulang)) : '-',
                        'status_pulang' => $a->status_pulang ?? '-',
                        'keterangan' => 'Hadir'
                    ];
                } else {
                    $statusIzin = null;
                    foreach ($approvedIzins as $iz) {
                        if ($tanggalObj->between(Carbon::parse($iz->tanggal_mulai)->startOfDay(), Carbon::parse($iz->tanggal_selesai)->endOfDay())) {
                            $statusIzin = $iz->tipe_pengajuan; break;
                        }
                    }

                    if ($statusIzin) {
                        $label = 'Izin';
                        if($statusIzin == 'sakit') $label = 'Sakit';
                        elseif($statusIzin == 'cuti') $label = 'Cuti';
                        elseif($statusIzin == 'dinas_luar') $label = 'Dinas Luar';

                        if ($statusIzin == 'dinas_luar') $dl++; else $izin++;

                        $detailHarian[] = [
                            'tanggal' => $tanggalObj->translatedFormat('d M Y'),
                            'masuk' => '-', 'status_masuk' => '-', 'pulang' => '-', 'status_pulang' => '-',
                            'keterangan' => $label
                        ];
                    } else {
                        // Jika hari berlalu dan tidak ada absen & bukan izin = Alpa
                        if ($tanggalObj->isPast() && !$tanggalObj->isToday()) {
                            $alpa++;
                            $detailHarian[] = [
                                'tanggal' => $tanggalObj->translatedFormat('d M Y'),
                                'masuk' => '-', 'status_masuk' => '-', 'pulang' => '-', 'status_pulang' => '-',
                                'keterangan' => 'Tanpa Keterangan (Alpa)'
                            ];
                        } else {
                            // Untuk kasus jika belum absen di "Hari Ini"
                            $detailHarian[] = [
                                'tanggal' => $tanggalObj->translatedFormat('d M Y'),
                                'masuk' => '-', 'status_masuk' => '-', 'pulang' => '-', 'status_pulang' => '-',
                                'keterangan' => '-'
                            ];
                        }
                    }
                }
            }

            $pegawai->rekap_harian = $detailHarian;
            $pegawai->total_hadir = $hadir;
            $pegawai->total_telat = $telat;
            $pegawai->total_izin = $izin;
            $pegawai->total_dl = $dl;
            $pegawai->total_alpa = $alpa;
        }

        return view('admin.cetak-laporan', compact('daftarPegawai', 'bulanDipilih', 'tahun', 'bulan', 'namaWali', 'nipWali'));
    }

    public function updatePenandatangan(Request $request)
    {
        $request->validate([
            'nama_wali' => 'required|string|max:255',
            'nip_wali' => 'nullable|string|max:255',
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => 'nama_wali_nagari'],
            ['value' => $request->nama_wali, 'updated_at' => Carbon::now('Asia/Jakarta')]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'nip_wali_nagari'],
            ['value' => $request->nip_wali ?: '-', 'updated_at' => Carbon::now('Asia/Jakarta')]
        );

        return redirect()->route('admin.dashboard')->with('success', 'Identitas penandatangan laporan berhasil diperbarui!');
    }
}