<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\PengajuanIzin; // TAMBAHAN: Import model PengajuanIzin
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PegawaiController extends Controller
{
    private function hitungTL($jamAbsen)
    {
        $jamMasukSetting = DB::table('settings')->where('key', 'jam_masuk_resmi')->value('value') ?? '07:30';
        $jamBuka = Carbon::createFromFormat('H:i:s', $jamMasukSetting . ':00');
        $waktuAbsen = Carbon::createFromFormat('H:i:s', $jamAbsen);

        // Batas paling awal presensi masuk (2 jam sebelum jam masuk resmi)
        $jamMulaiAbsen = $jamBuka->copy()->subHours(2);

        if ($waktuAbsen->gte($jamMulaiAbsen) && $waktuAbsen->lte($jamBuka)) {
            return 'Tepat Waktu';
        }

        if ($waktuAbsen->gt($jamBuka)) {
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

        return 'Tepat Waktu';
    }

    private function hitungPSW($jamPulang)
    {
        $jamPulangSetting = DB::table('settings')->where('key', 'jam_pulang_resmi')->value('value') ?? '16:00';
        $jamPulangResmi = Carbon::createFromFormat('H:i:s', $jamPulangSetting . ':00');
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

        $jamMasukSetting = DB::table('settings')->where('key', 'jam_masuk_resmi')->value('value') ?? '07:30';
        $jamPulangSetting = DB::table('settings')->where('key', 'jam_pulang_resmi')->value('value') ?? '16:00';
        
        $jamBukaPresensi = Carbon::createFromFormat('H:i', $jamMasukSetting, 'Asia/Jakarta')->subHours(2)->format('H:i');
        $jamTutupPresensi = '21:00';

        $totalHariKerja = 0;

        if ($awalBulan->format('Y-m') >= $tanggalDibuat->format('Y-m') && $awalBulan->format('Y-m') <= $sekarang->format('Y-m')) {
            $tanggalMulaiHitung = ($tanggalDibuat->format('Y-m') === $bulanDipilih) ? $tanggalDibuat->copy() : $awalBulan->copy();

            if ($hariIni->format('Y-m') === $bulanDipilih) {
                $tanggalAkhirHitung = ($sekarang->format('H:i') < $jamMasukSetting) ? $hariIni->copy()->subDay() : $hariIni->copy();
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
        
        // Hitung Total Izin & DL yang sudah disetujui bulan ini
        $approvedIzins = PengajuanIzin::where('user_id', $user->id)
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

        $tanpaKeterangan = $totalHariKerja - $riwayatAbsen->count() - $totalIzin - $totalDL;
        if ($tanpaKeterangan < 0) $tanpaKeterangan = 0;

        $absenHariIni = DB::table('absensi')
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $hariIni->format('Y-m-d'))
            ->first();

        $jamSekarangStr = $sekarang->format('H:i');
        $isJamKerja = ($jamSekarangStr >= $jamBukaPresensi && $jamSekarangStr <= $jamTutupPresensi);

        if ($absenHariIni) {
            if ($absenHariIni->jam_pulang != null) {
                $statusHariIni = 'Sudah Absen Lengkap (Masuk: ' . $absenHariIni->status_masuk . ' | Pulang: ' . $absenHariIni->status_pulang . ')';
            } else {
                $statusHariIni = 'Sudah Absen Masuk (' . $absenHariIni->status_masuk . ' - ' . date('H:i', strtotime($absenHariIni->jam_masuk)) . ')';
            }
        } else {
            if (!$isJamKerja) {
                $statusHariIni = 'Sekarang di Luar Jam Kerja';
            } else {
                $statusHariIni = 'Belum Absen';
            }
        }

        $kantorLat = DB::table('settings')->where('key', 'kantor_latitude')->value('value') ?? '-1.0825000';
        $kantorLng = DB::table('settings')->where('key', 'kantor_longitude')->value('value') ?? '100.8250000';

        $riwayatPengajuan = PengajuanIzin::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pegawai.dashboard', compact(
            'riwayatAbsen', 
            'totalHariKerja', 
            'totalHadir', 
            'totalTerlambat', 
            'totalIzin',
            'totalDL',
            'tanpaKeterangan',
            'bulanDipilih',
            'statusHariIni',
            'absenHariIni',
            'isJamKerja',
            'kantorLat',
            'kantorLng',
            'jamMasukSetting',
            'jamPulangSetting',
            'riwayatPengajuan'
        ));
    }

    public function store(Request $request)
    {
        $sekarang = Carbon::now('Asia/Jakarta');
        $jamSekarang = $sekarang->format('H:i:s');
        $tanggalHariIni = $sekarang->format('Y-m-d');

        $jamMasukSetting = DB::table('settings')->where('key', 'jam_masuk_resmi')->value('value') ?? '07:30';
        $jamBukaPresensi = Carbon::createFromFormat('H:i', $jamMasukSetting, 'Asia/Jakarta')->subHours(2)->format('H:i:s');
        $jamTutupPresensi = '21:00:00';

        if ($jamSekarang < $jamBukaPresensi || $jamSekarang > $jamTutupPresensi) {
            return response()->json([
                'success' => false,
                'message' => 'Absen ditolak! Sistem presensi belum dibuka/sudah ditutup (Hanya aktif jam ' . substr($jamBukaPresensi, 0, 5) . ' - 21:00 WIB).'
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

    // =========================================================================
    // TAMBAHAN: FUNGSI UNTUK MENGIRIM PENGAJUAN IZIN & DINAS LUAR
    // =========================================================================
    public function ajukanIzin(Request $request)
    {
        $request->validate([
            'tipe_pengajuan' => 'required|in:sakit,cuti,izin_pribadi,dinas_luar',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string',
            'file_bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $sekarang = Carbon::today('Asia/Jakarta');
        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();

        // 1. RULE H-2 KHUSUS CUTI, IZIN PRIBADI, DINAS LUAR (Sakit dikecualikan)
        if ($request->tipe_pengajuan !== 'sakit') {
            $batasH2 = $sekarang->copy()->addDays(2);
            if ($tanggalMulai->lt($batasH2)) {
                return back()->with('error', 'Gagal! Pengajuan Cuti, Izin Pribadi, dan Dinas Luar wajib dilakukan minimal H-2. Untuk izin mendadak, silakan hubungi Admin / Wali Nagari.');
            }
        }

        // 2. RULE WAJIB FILE BUKTI KHUSUS DINAS LUAR
        if ($request->tipe_pengajuan === 'dinas_luar' && !$request->hasFile('file_bukti')) {
            return back()->with('error', 'Gagal! Pengajuan Dinas Luar WAJIB melampirkan Surat Tugas atau Nota Dinas.');
        }

        // 3. PROSES SIMPAN FILE (Jika ada file diupload)
        $namaFile = null;
        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            // Format nama: timestamp_namafileasli
            $namaFile = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            // Simpan ke folder storage/app/public/bukti_izin
            $file->storeAs('bukti_izin', $namaFile, 'public');
        }

        // 4. SIMPAN KE DATABASE
        PengajuanIzin::create([
            'user_id' => Auth::id(),
            'tipe_pengajuan' => $request->tipe_pengajuan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'file_bukti' => $namaFile,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Pengajuan berhasil dikirim! Menunggu persetujuan Wali Nagari.');
    }
}