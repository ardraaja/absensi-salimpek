<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pegawai - Wali Nagari Salimpek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { padding-bottom: 80px; padding-top: 0; }
        .sidebar-left { display: none !important; } 
        
        .header-gradient-mobile {
            background: linear-gradient(180deg, #198754 0%, #1eac6d 60%, #f8f9fa 100%);
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            padding: 30px 20px 40px 20px;
            color: white !important;
            margin-top: 0;
        }
        .header-gradient-mobile h4, 
        .header-gradient-mobile p {
            color: white !important;
        }
        
        .header-gradient-mobile .alert-secondary {
            background: #ffffff !important;
            border: none !important;
            color: #212529 !important; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05) !important;
        }

        .text-success-light { color: #198754 !important; font-weight: 700 !important; }
        .header-gradient-mobile .text-warning { color: #b8860b !important; font-weight: 700 !important; } 

        .rekap-card {
            background: linear-gradient(135deg, #115e3a 0%, #198754 100%);
            border-radius: 20px;
            color: white !important;
            padding: 20px;
            border: none;
        }
        .rekap-card h5,
        .rekap-card span,
        .rekap-card div {
            color: white !important;
        }
        .icon-box {
            background: rgba(255, 255, 255, 0.15);
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .badge-num {
            background: rgba(255, 255, 255, 0.2);
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 50rem;
            display: inline-block;
            font-size: 13px;
            min-width: 40px;
        }
        .label-status {
            font-size: 10px;
            opacity: 0.85;
            display: block;
            margin-top: 4px;
        }

        .nav-bottom { position: fixed; bottom: 0; left: 0; right: 0; height: 65px; background: white; box-shadow: 0 -2px 15px rgba(0,0,0,0.1); z-index: 1000; }
        .nav-item-box { flex: 1; text-align: center; color: #6c757d; cursor: pointer; padding: 10px 0; }
        .nav-item-box.active { color: #198754; font-weight: bold; }
        
        .center-absen-wrapper { flex: 1; display: flex; justify-content: center; position: relative; }
        .btn-hadir-mobile { 
            position: absolute; 
            top: -25px; 
            width: 65px; 
            height: 65px; 
            border-radius: 50%; 
            border: 5px solid #fff; 
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            font-size: 10px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            line-height: 1.1;
        }
        .profile-banner-solid {
            background-color: #198754 !important;
            padding: 25px 20px;
        }
        @media (min-width: 768px) {
            .profile-banner-solid {
                background: none !important;
                padding: 0 !important;
            }
        }

        .page-content { display: none; }
        .page-content.active { display: block; }
        .desktop-absen-card { display: none; } 

        @media (min-width: 768px) {
            body { padding-bottom: 0; padding-left: 260px; }
            .nav-bottom { display: none !important; }
            .desktop-absen-card { display: block; } 
            
            .header-gradient-mobile {
                background: none !important;
                color: initial !important;
                padding: 0 !important;
                margin-bottom: 1.5rem !important;
            }
            .header-gradient-mobile h4 { color: #212529 !important; }
            .header-gradient-mobile p { color: #6c757d !important; }
            .header-gradient-mobile .alert-secondary {
                background-color: #e2e3e5 !important;
                border-color: #d3d6d8 !important;
                color: #41464b !important;
                box-shadow: none !important;
            }
            .header-gradient-mobile .text-warning { color: #ffc107 !important; }
            .text-success-light { color: #198754 !important; }
            
            .sidebar-left { 
                display: flex !important; 
                flex-direction: column;
                position: fixed; 
                top: 0; 
                left: 0; 
                bottom: 0; 
                width: 250px; 
                background: white; 
                box-shadow: 2px 0 10px rgba(0,0,0,0.05); 
                z-index: 1000;
                padding: 20px;
            }
            .sidebar-item {
                display: flex;
                align-items: center;
                color: #6c757d;
                padding: 12px 15px;
                border-radius: 8px;
                cursor: pointer;
                margin-bottom: 5px;
                text-decoration: none;
            }
            .sidebar-item i { font-size: 20px; margin-right: 15px; }
            .sidebar-item.active { background: #198754; color: white; font-weight: bold; }
            .sidebar-item:hover:not(.active) { background: #f8f9fa; color: #198754; }
        }
    </style>
</head>
<body class="bg-light">

<div class="sidebar-left border-end d-flex flex-column justify-content-between">
    <div>
        <div class="mb-4 px-2">
            <h5 class="fw-bold text-success mb-0">Absensi Digital</h5>
            <span class="text-muted small">Pegawai Nagari Salimpek</span>
        </div>
        
        <div class="sidebar-item active" onclick="switchPage('utama', this)">
            <i class="bi bi-clock-history"></i> <span>Presensi & Rekap</span>
        </div>
        <div class="sidebar-item" onclick="switchPage('profil', this)">
            <i class="bi bi-person-bounding-box"></i> <span>Profil & Akun</span>
        </div>
    </div>

    <div class="p-2 border-top bg-light rounded text-center small">
        <span class="fw-bold d-block">{{ Auth::user()->name }}</span>
        <span class="text-muted text-truncate d-block">{{ Auth::user()->email }}</span>
    </div>
</div>

<div id="page-utama" class="page-content active container py-4">
    <div class="header-gradient-mobile text-center text-md-start mb-4 mx-[-12px] mx-md-0">
        <h4 class="fw-bold mb-0">{{ Auth::user()->name }} ({{ Auth::user()->status_kerja }})</h4>
        <p class="mb-2 opacity-90">{{ Auth::user()->jabatan }}</p>
        
        <div class="mb-1 small opacity-90">
            <i class="bi bi-calendar3 me-1"></i> <span id="realtime-date">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
            <span class="mx-1">|</span>
            <i class="bi bi-clock me-1"></i> <span id="realtime-clock">00:00:00</span> WIB
        </div>

        <div class="mb-2 small opacity-90">
            <i class="bi bi-alarm me-1"></i> Jam Kerja Resmi: 
            <strong>{{ date('H:i', strtotime($jamMasukSetting)) }} - {{ date('H:i', strtotime($jamPulangSetting)) }} WIB</strong>
        </div>
        
        <div class="alert alert-secondary py-2 px-3 d-inline-block small mb-0 shadow-sm">
            <i class="bi bi-info-circle-fill me-1"></i> Status Hari Ini: 
            <span id="status-lokasi" class="{{ $absenHariIni ? 'text-success-light fw-bold' : 'text-warning fw-bold' }}">
                {{ $statusHariIni }}
            </span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4 text-center desktop-absen-card">
            <div class="card border-0 shadow-sm p-4 d-flex flex-column align-items-center justify-content-center h-100">
                <h5 class="fw-semibold mb-3">Ambil Presensi</h5>
                
                @if(!$absenHariIni)
                    <button type="button" class="btn btn-success btn-lg rounded-circle shadow my-2 btn-absen-trigger" style="width: 130px; height: 130px;" {{ !$isJamKerja ? 'disabled' : '' }}>
                        <span class="fw-bold">ABSEN<br>MASUK</span>
                    </button>
                @elseif($absenHariIni && $absenHariIni->jam_pulang == null)
                    <button type="button" class="btn btn-warning text-dark btn-lg rounded-circle shadow my-2 btn-absen-trigger" style="width: 130px; height: 130px;" {{ !$isJamKerja ? 'disabled' : '' }}>
                        <span class="fw-bold">ABSEN<br>PULANG</span>
                    </button>
                @else
                    <button type="button" class="btn btn-secondary btn-lg rounded-circle shadow my-2 btn-absen-trigger" style="width: 130px; height: 130px;" disabled>
                        <span class="fw-bold" style="font-size: 13px;">PRESENSI<br>LENGKAP</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="col-md-8">
            <div class="card rekap-card shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-box me-3">
                            <i class="bi bi-calendar2-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0" style="font-size: 16px;">Rekap Presensi Bulan Ini</h5>
                            <span class="small opacity-75">Bulan {{ \Carbon\Carbon::parse($bulanDipilih . '-01')->translatedFormat('F Y') }}</span>
                        </div>
                    </div>
                    <div class="pt-1">
                        <span class="small fw-semibold opacity-90">{{ $totalHariKerja }} Hari Kerja</span>
                    </div>
                </div>

                <!-- KOTAK REKAP 5 KOLOM (PEGAWAI) -->
                <div class="d-flex flex-wrap justify-content-center text-center gap-2">
                    <div style="flex: 1; min-width: 55px;">
                        <div class="badge-num">{{ $totalHadir }}</div>
                        <span class="label-status">Hadir</span>
                    </div>
                    <div style="flex: 1; min-width: 55px;">
                        <div class="badge-num">{{ $totalTerlambat }}</div>
                        <span class="label-status">Telat</span>
                    </div>
                    <div style="flex: 1; min-width: 55px;">
                        <div class="badge-num">{{ $totalIzin }}</div>
                        <span class="label-status">Izin/Sakit</span>
                    </div>
                    <div style="flex: 1; min-width: 55px;">
                        <div class="badge-num">{{ $totalDL }}</div>
                        <span class="label-status">Dinas Luar</span>
                    </div>
                    <div style="flex: 1; min-width: 55px;">
                        <div class="badge-num">{{ $tanpaKeterangan }}</div>
                        <span class="label-status">Alpa</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-3 mt-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-3 gap-2">
            <h5 class="fw-bold mb-0">Detail Presensi Harian</h5>
            <form action="/pegawai/dashboard" method="GET" id="form-bulan" class="input-group" style="max-width: 250px;">
                <span class="input-group-text bg-white small" style="font-size: 13px;">Bulan</span>
                <input type="month" name="bulan" class="form-control form-control-sm" value="{{ $bulanDipilih }}" onchange="document.getElementById('form-bulan').submit();">
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th>Hari / Tanggal</th>
                        <th class="text-center">Jam Masuk</th>
                        <th class="text-center">Status Masuk</th>
                        <th class="text-center">Jam Pulang</th>
                        <th class="text-center">Status Pulang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatAbsen as $absen)
                        <tr>
                            <td><strong>{{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('l, d F Y') }}</strong></td>
                            <td class="text-center">
                                {{ $absen->jam_masuk ? date('H:i', strtotime($absen->jam_masuk)) . ' WIB' : '-' }}
                            </td>
                            <td class="text-center">
                                @if($absen->status_masuk == 'Tepat Waktu')
                                    <span class="badge bg-success">Tepat Waktu</span>
                                @elseif(in_array($absen->status_masuk, ['TL 1', 'TL 2', 'TL 3', 'TL 4']))
                                    <span class="badge bg-warning text-dark">{{ $absen->status_masuk }}</span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $absen->jam_pulang ? date('H:i', strtotime($absen->jam_pulang)) . ' WIB' : '-' }}
                            </td>
                            <td class="text-center">
                                @if($absen->status_pulang == 'Tepat Waktu')
                                    <span class="badge bg-success">Tepat Waktu</span>
                                @elseif(in_array($absen->status_pulang, ['PSW 1', 'PSW 2', 'PSW 3', 'PSW 4']))
                                    <span class="badge bg-danger">{{ $absen->status_pulang }}</span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat presensi di bulan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-3 mt-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-3 gap-2">
            <div>
                <h5 class="fw-bold mb-0">Pengajuan Izin & Dinas Luar</h5>
                <span class="text-muted small">Riwayat permohonan Sakit, Cuti, dan DL</span>
            </div>
            <button type="button" class="btn btn-sm btn-success fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPengajuanIzin">
                <i class="bi bi-envelope-plus-fill me-1"></i> Buat Pengajuan
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th>Tipe Pengajuan</th>
                        <th>Tanggal Rentang</th>
                        <th>Alasan Keterangan</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatPengajuan ?? [] as $izin)
                        <tr>
                            <td>
                                @if($izin->tipe_pengajuan == 'sakit')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Sakit</span>
                                @elseif($izin->tipe_pengajuan == 'cuti')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Cuti</span>
                                @elseif($izin->tipe_pengajuan == 'izin_pribadi')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Izin Pribadi</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Dinas Luar</span>
                                @endif
                            </td>
                            <td>
                                @if($izin->tanggal_mulai == $izin->tanggal_selesai)
                                    {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d M Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M') }} s/d {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->translatedFormat('d M Y') }}
                                @endif
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $izin->alasan }}">
                                    {{ $izin->alasan }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($izin->status == 'pending')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending</span>
                                @elseif($izin->status == 'approved')
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Disetujui</span>
                                @else
                                    <span class="badge bg-danger" title="{{ $izin->catatan_admin }}"><i class="bi bi-x-circle"></i> Ditolak</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat pengajuan Izin atau DL.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="page-profil" class="page-content container py-4">
    <div class="profile-banner-solid text-center text-md-start mb-4 mx-[-12px] mx-md-0">
        <h4 class="fw-bold mb-0 text-dark">Manajemen Profil</h4>
    </div>

    <div class="row g-4 justify-content-center justify-content-md-start">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4 text-center h-100">
                <div class="mx-auto bg-success text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 75px; height: 75px;">
                    <i class="bi bi-person-badge fs-1"></i>
                </div>
                
                <h5 class="fw-bold mb-1">{{ Auth::user()->name }} ({{ Auth::user()->status_kerja }})</h5>
                <p class="text-success badge bg-success-subtle border border-success-subtle mb-4 px-3">{{ Auth::user()->jabatan }}</p>

                <div class="text-start border-top pt-3 small text-muted">
                    <div class="mb-2 d-flex justify-content-between">
                        <span><i class="bi bi-envelope me-2"></i>Akun (Email):</span>
                        <strong class="text-dark">{{ Auth::user()->email }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="bi bi-card-text me-2"></i>NIP:</span>
                        <strong class="text-dark">{{ Auth::user()->nip ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold text-muted mb-3 px-2">Pusat Pengaturan & Akses</h6>
                
                <div class="list-group list-group-flush">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $kantorLat }},{{ $kantorLng }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 rounded mb-2 bg-light text-decoration-none">
                        <div>
                            <i class="bi bi-geo-alt-fill text-danger me-3 fs-5"></i>
                            <span class="fw-semibold text-dark">Lokasi Kantor Wali Nagari</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>

                    <form action="{{ route('logout') }}" method="POST" class="w-100 mt-2">
                        @csrf
                        <button type="submit" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 rounded text-danger fw-bold bg-danger-subtle">
                            <div>
                                <i class="bi bi-box-arrow-left me-3 fs-5"></i>
                                <span>Keluar</span>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPengajuanIzin" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" style="font-size: 16px;">
                    <i class="bi bi-envelope-paper me-2"></i>Form Pengajuan Izin & DL
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pegawai.izin.ajukan') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    
                    <div class="alert alert-warning small mb-3 p-2 border-warning" style="font-size: 11px;">
                        <strong><i class="bi bi-exclamation-triangle-fill"></i> Perhatian:</strong> Pengajuan <strong>Cuti, Izin Pribadi, dan Dinas Luar</strong> wajib dilakukan minimal <strong>H-2</strong>. Jika mendadak (hari H), silakan hubungi langsung Admin / Wali Nagari via WA.
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold">Tipe Pengajuan</label>
                        <select name="tipe_pengajuan" class="form-select form-select-sm" required>
                            <option value="" disabled selected>-- Pilih Tipe --</option>
                            <option value="sakit">Sakit (Bisa Hari-H)</option>
                            <option value="cuti">Cuti (Minimal H-2)</option>
                            <option value="izin_pribadi">Izin Kepentingan Pribadi (Minimal H-2)</option>
                            <option value="dinas_luar">Dinas Luar / DL (Minimal H-2)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold">Alasan / Keterangan</label>
                        <textarea name="alasan" class="form-control form-control-sm" rows="2" placeholder="Tuliskan keterangan lengkap..." required></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold">File Lampiran <span class="text-muted fw-normal">(Foto/PDF)</span></label>
                        <input type="file" name="file_bukti" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text" style="font-size: 10px;">
                            <span class="text-danger fw-bold">*Wajib</span> untuk pengajuan <strong>Dinas Luar</strong> (Surat Tugas) atau <strong>Sakit</strong> (Surat Dokter). Max 2MB.
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="nav-bottom d-flex align-items-center border-top">
    <div class="nav-item-box active" onclick="switchPage('utama', this)">
        <i class="bi bi-clock-history fs-4"></i><br>
        <span style="font-size: 11px;">Presensi</span>
    </div>
    
    <div class="center-absen-wrapper">
        @if(!$absenHariIni)
            <button type="button" class="btn btn-success btn-hadir-mobile btn-absen-trigger" {{ !$isJamKerja ? 'disabled' : '' }}>
                ABSEN<br>MASUK
            </button>
        @elseif($absenHariIni && $absenHariIni->jam_pulang == null)
            <button type="button" class="btn btn-warning text-dark btn-hadir-mobile btn-absen-trigger" {{ !$isJamKerja ? 'disabled' : '' }}>
                ABSEN<br>PULANG
            </button>
        @else
            <button type="button" class="btn btn-secondary btn-hadir-mobile btn-absen-trigger" disabled>
                LENGKAP
            </button>
        @endif
    </div>
    
    <div class="nav-item-box" onclick="switchPage('profil', this)">
        <i class="bi bi-person-bounding-box fs-4"></i><br>
        <span style="font-size: 11px;">Profil</span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        confirmButtonColor: '#198754'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal Memproses!',
        text: "{{ session('error') }}",
        confirmButtonColor: '#dc3545'
    });
</script>
@endif

<script>
    const statusLokasi = document.getElementById('status-lokasi');

    function updateClock() {
        const now = new Date();
        const opsiTanggal = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        const tanggalIndo = now.toLocaleDateString('id-ID', opsiTanggal);
        
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        if (document.getElementById('realtime-date')) {
            document.getElementById('realtime-date').innerText = tanggalIndo;
        }
        if (document.getElementById('realtime-clock')) {
            document.getElementById('realtime-clock').innerText = `${hours}:${minutes}:${seconds}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
    
    document.querySelectorAll('.btn-absen-trigger').forEach(btn => {
        btn.addEventListener('click', function() {
            let isAbsenPulang = {{ (isset($absenHariIni) && $absenHariIni->jam_masuk && !$absenHariIni->jam_pulang) ? 'true' : 'false' }};

            if (isAbsenPulang) {
                let jamPulangResmi = "{{ date('H:i', strtotime($jamPulangSetting)) }}";

                Swal.fire({
                    title: 'Konfirmasi Absen Pulang',
                    html: `Apakah Anda yakin ingin menyelesaikan tugas dan Absen Pulang sekarang?<br><br><span class="badge bg-secondary p-2" style="font-size: 13px;">Jam Pulang Resmi: <strong>${jamPulangResmi} WIB</strong></span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Absen Pulang!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiKirimPresensi();
                    }
                });
            } else {
                eksekusiKirimPresensi();
            }
        });
    });

    function eksekusiKirimPresensi() {
        Swal.fire({
            title: 'Mendapatkan GPS & Memproses...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                let lat = position.coords.latitude;
                let lng = position.coords.longitude;
                
                fetch('/pegawai/absen', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ latitude: lat, longitude: lng })
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal memproses absensi.');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#198754'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Absen',
                            text: data.message,
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Absen Ditolak',
                        text: error.message,
                        confirmButtonColor: '#dc3545'
                    });
                });
                
            }, function(error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Membaca GPS',
                    text: 'Pastikan fitur Lokasi (GPS) di HP/Browser Anda sudah diaktifkan.',
                    confirmButtonColor: '#dc3545'
                });
            }, { enableHighAccuracy: true });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Tidak Didukung',
                text: 'Browser Anda tidak mendukung Geolocation GPS.',
                confirmButtonColor: '#dc3545'
            });
        }
    }

    function switchPage(pageId, element) {
        document.querySelectorAll('.page-content').forEach(page => page.classList.remove('active'));
        document.querySelectorAll('.nav-item-box, .sidebar-item').forEach(item => item.classList.remove('active'));
        
        document.getElementById('page-' + pageId).classList.add('active');
        
        const targetClass = element.classList.contains('sidebar-item') ? '.sidebar-item' : '.nav-item-box';
        const items = Array.from(element.parentNode.querySelectorAll(targetClass));
        const index = items.indexOf(element);

        if(index !== -1) {
            if(document.querySelectorAll('.sidebar-item')[index]) document.querySelectorAll('.sidebar-item')[index].classList.add('active');
            if(document.querySelectorAll('.nav-item-box')[index]) document.querySelectorAll('.nav-item-box')[index].classList.add('active');
        }
    }
</script>
</body>
</html>