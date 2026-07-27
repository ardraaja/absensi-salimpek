<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Wali Nagari Salimpek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { padding-bottom: 80px; padding-top: 0; }
        .sidebar-left { display: none !important; } 
        
        .admin-banner-solid {
            background-color: #198754 !important;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            padding: 25px 20px;
        }

        .rekap-card {
            background: linear-gradient(135deg, #115e3a 0%, #198754 100%);
            border-radius: 20px;
            color: white !important;
            padding: 20px;
            border: none;
        }
        .rekap-card h5, .rekap-card span, .rekap-card div { color: white !important; }
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
            padding: 4px 16px;
            border-radius: 50rem;
            display: inline-block;
            font-size: 14px;
            min-width: 48px;
        }
        .label-status { font-size: 11px; opacity: 0.85; display: block; margin-top: 4px; }

        .nav-bottom { position: fixed; bottom: 0; left: 0; right: 0; height: 65px; background: white; box-shadow: 0 -2px 15px rgba(0,0,0,0.1); z-index: 1000; }
        .nav-item-box { flex: 1; text-align: center; color: #6c757d; cursor: pointer; padding: 10px 0; }
        .nav-item-box.active { color: #198754; font-weight: bold; }

        .page-content { display: none; }
        .page-content.active { display: block; }

        @media (min-width: 768px) {
            body { padding-bottom: 0; padding-left: 260px; }
            .nav-bottom { display: none !important; }
            
            .admin-banner-solid {
                background: none !important;
                padding: 0 !important;
            }
            
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
            <span class="text-muted small">Wali Nagari Salimpek</span>
        </div>
        
        <div class="sidebar-item active" onclick="switchPage('utama', this)">
            <i class="bi bi-speedometer2"></i> <span>Dashboard Admin</span>
        </div>
        <div class="sidebar-item" onclick="switchPage('profil', this)">
            <i class="bi bi-person-workspace"></i> <span>Profil & Kelola</span>
        </div>
    </div>

    <div class="p-2 border-top bg-light rounded text-center small">
        <span class="fw-bold d-block">{{ Auth::user()->name }}</span>
        <span class="text-muted text-truncate d-block">{{ Auth::user()->email }}</span>
    </div>
</div>

<div id="page-utama" class="page-content active container py-4">
    <div class="mb-4 text-center text-md-start">
        <h4 class="fw-bold mb-0 text-dark">Dashboard Wali Nagari</h4>
        <p class="text-muted small mb-2">Nagari Salimpek, Kecamatan Lembah Gumanti</p>
        
        <div class="d-inline-flex flex-wrap align-items-center gap-2 px-3 py-2 bg-success text-white rounded-3 shadow-sm small">
            <div>
                <i class="bi bi-calendar3 me-1"></i> <span id="admin-realtime-date">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
                <span class="mx-1">|</span>
                <i class="bi bi-clock me-1"></i> <span id="admin-realtime-clock">00:00:00</span> WIB
            </div>
            <span class="d-none d-sm-inline">|</span>
            <div>
                <i class="bi bi-alarm me-1"></i> Jam Kerja: 
                <strong>{{ date('H:i', strtotime($jamMasuk)) }} - {{ date('H:i', strtotime($jamPulang)) }} WIB</strong>
            </div>
        </div>
    </div>

    <!-- KOTAK REKAP 5 KOLOM (DIPERBARUI) -->
    <div class="card rekap-card shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-box me-3">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0" style="font-size: 16px;">Rekap Kehadiran Hari Ini</h5>
                    <span class="small opacity-75">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>
            <div class="pt-1">
                <span class="small fw-semibold opacity-90">{{ $totalPegawai }} Total Pegawai</span>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-center text-center gap-3">
            <div style="flex: 1; min-width: 60px;">
                <div class="badge-num bg-success">{{ $hadir }}</div>
                <span class="label-status">Hadir<br>(Tepat Waktu)</span>
            </div>
            <div style="flex: 1; min-width: 60px;">
                <div class="badge-num bg-warning text-dark">{{ $terlambat }}</div>
                <span class="label-status">Terlambat<br>(TL)</span>
            </div>
            <div style="flex: 1; min-width: 60px;">
                <div class="badge-num bg-info text-dark">{{ $izinHariIni ?? 0 }}</div>
                <span class="label-status">Izin / Sakit<br>(Hari Ini)</span>
            </div>
            <div style="flex: 1; min-width: 60px;">
                <div class="badge-num bg-primary">{{ $dlHariIni ?? 0 }}</div>
                <span class="label-status">Dinas Luar<br>(Hari Ini)</span>
            </div>
            <div style="flex: 1; min-width: 60px;">
                <div class="badge-num bg-danger">{{ $belumAbsen }}</div>
                <span class="label-status">Tanpa Ket.<br>(Alpa/Belum)</span>
            </div>
        </div>
    </div>

    <!-- DAFTAR PERSETUJUAN IZIN & DL -->
    <div class="card border-0 shadow-sm p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-envelope-exclamation-fill text-warning me-2"></i>Menunggu Persetujuan
            </h5>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalInputIzinManual">
                <i class="bi bi-plus-circle me-1"></i> Input Izin Manual
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th>Pegawai</th>
                        <th>Tipe Pengajuan</th>
                        <th>Rentang Tanggal</th>
                        <th>Alasan & Bukti</th>
                        <th class="text-center">Aksi Keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuanPending as $pending)
                        <tr>
                            <td>
                                <strong>{{ $pending->user->name }}</strong><br>
                                <span class="text-muted" style="font-size: 11px;">Pengajuan: {{ \Carbon\Carbon::parse($pending->created_at)->diffForHumans() }}</span>
                            </td>
                            <td>
                                @if($pending->tipe_pengajuan == 'sakit')
                                    <span class="badge bg-danger">Sakit</span>
                                @elseif($pending->tipe_pengajuan == 'cuti')
                                    <span class="badge bg-info">Cuti</span>
                                @elseif($pending->tipe_pengajuan == 'izin_pribadi')
                                    <span class="badge bg-warning text-dark">Izin Pribadi</span>
                                @else
                                    <span class="badge bg-primary">Dinas Luar</span>
                                @endif
                            </td>
                            <td>
                                @if($pending->tanggal_mulai == $pending->tanggal_selesai)
                                    {{ \Carbon\Carbon::parse($pending->tanggal_mulai)->translatedFormat('d M Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($pending->tanggal_mulai)->format('d M') }} s/d {{ \Carbon\Carbon::parse($pending->tanggal_selesai)->translatedFormat('d M Y') }}
                                @endif
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $pending->alasan }}">{{ $pending->alasan }}</span><br>
                                @if($pending->file_bukti)
                                    <a href="{{ asset('storage/bukti_izin/' . $pending->file_bukti) }}" target="_blank" class="text-decoration-none small text-primary fw-bold">
                                        <i class="bi bi-paperclip"></i> Lihat Lampiran
                                    </a>
                                @else
                                    <span class="text-muted small">Tidak ada lampiran</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <!-- Tombol Setujui -->
                                    <form action="{{ route('admin.izin.setujui', $pending->id) }}" method="POST" id="form-setujui-{{ $pending->id }}" class="m-0">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-success px-2 py-1" title="Setujui" onclick="konfirmasiSetujui('{{ $pending->id }}')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    
                                    <!-- Tombol Tolak -->
                                    <button type="button" class="btn btn-sm btn-danger px-2 py-1" title="Tolak" onclick="bukaModalTolakIzin('{{ $pending->id }}')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Belum ada pengajuan baru yang perlu diproses.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- REKAPITULASI BULANAN PEGAWAI -->
    <div class="card border-0 shadow-sm p-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-3 gap-2">
            <h5 class="fw-bold mb-0">Rekapitulasi Kinerja Bulanan</h5>
            
            <form action="/admin/dashboard" method="GET" id="form-bulan" class="input-group" style="max-width: 250px;">
                <span class="input-group-text bg-white small" style="font-size: 13px;">Bulan</span>
                <input type="month" name="bulan" class="form-control form-control-sm" value="{{ $bulanDipilih ?? date('Y-m') }}" onchange="document.getElementById('form-bulan').submit();">
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th>Pegawai & Identitas</th>
                        <th>Jabatan</th>
                        <th class="text-center text-success">Hadir</th>
                        <th class="text-center text-warning">Telat</th>
                        <th class="text-center text-primary">Izin/DL</th>
                        <th class="text-center text-danger">Alpa</th>
                        <th class="text-center">Aksi Jurnal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftarPegawai as $pegawai)
                        <tr>
                            <td>
                                <strong>{{ $pegawai->name }}</strong><br>
                                <span class="text-muted small">NIP: {{ $pegawai->nip ?? '-' }}</span>
                            </td>
                            <td>{{ $pegawai->jabatan ?? 'Belum Diatur' }}</td>
                            <td class="text-center fw-bold text-success">{{ $pegawai->count_hadir }}</td>
                            <td class="text-center fw-bold text-warning">{{ $pegawai->count_telat }}</td>
                            <td class="text-center fw-bold text-primary">{{ $pegawai->count_izin + $pegawai->count_dl }}</td>
                            <td class="text-center fw-bold text-danger">{{ $pegawai->count_alpa }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-success px-2 py-1" style="font-size: 11px;" onclick="bukaModalDetail('{{ $pegawai->name }}', '{{ $pegawai->jabatan }}', '{{ $pegawai->nip }}', '{{ json_encode($pegawai->riwayat_json) }}')">
                                    <i class="bi bi-journal-richtext"></i> Log Absen
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data akun pegawai terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="page-profil" class="page-content container py-4">
    <div class="admin-banner-solid text-center text-md-start mb-4 mx-[-12px] mx-md-0">
        <h4 class="fw-bold mb-0 text-dark">Manajemen Profil</h4>
        <p class="text-dark small mb-0 opacity-75">Pusat kendali dan administrasi aplikasi</p>
    </div>

    <div class="row g-4 justify-content-center justify-content-md-start">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4 text-center h-100">
                <div class="mx-auto bg-success text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 75px; height: 75px;">
                    <i class="bi bi-person-check fs-1"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ Auth::user()->name }}</h5>
                <p class="text-success badge bg-success-subtle border border-success-subtle mb-4 px-3">Wali Nagari (Admin)</p>

                <div class="text-start border-top pt-3 small text-muted">
                    <div class="mb-2 d-flex justify-content-between">
                        <span><i class="bi bi-envelope me-2"></i>Email:</span>
                        <strong class="text-dark">{{ Auth::user()->email }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold text-muted mb-3 px-2">Pusat Pengaturan & Akses</h6>
                
                <div class="list-group list-group-flush">
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 rounded mb-2 bg-light" data-bs-toggle="modal" data-bs-target="#modalKelolaPegawai">
                        <div>
                            <i class="bi bi-people-fill text-primary me-3 fs-5"></i>
                            <span class="fw-semibold">Kelola Akun Pegawai</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </button>

                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 rounded mb-2 bg-light text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalSettingLokasi">
                        <div>
                            <i class="bi bi-geo-alt-fill text-danger me-3 fs-5"></i>
                            <span class="fw-semibold text-dark">Atur Lokasi Koordinat Kantor</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </button>

                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 rounded mb-2 bg-light text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalSettingJamKerja">
                        <div>
                            <i class="bi bi-clock-history text-warning me-3 fs-5"></i>
                            <span class="fw-semibold text-dark">Atur Jam Kerja Resmi</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </button>

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

<!-- ================= MODAL TAMBAH IZIN MANUAL ================= -->
<div class="modal fade" id="modalInputIzinManual" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold small"><i class="bi bi-plus-circle me-2"></i>Input Izin/DL Pegawai Manual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.izin.manual') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted mb-3"><i class="bi bi-info-circle text-primary"></i> Fitur ini digunakan jika pegawai berhalangan/sakit mendadak dan melapor langsung via WA/Telepon.</p>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Pilih Pegawai</label>
                        <select name="user_id" class="form-select form-select-sm" required>
                            <option value="" disabled selected>-- Pilih Akun Pegawai --</option>
                            @foreach($daftarPegawai as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Tipe Absen</label>
                        <select name="tipe_pengajuan" class="form-select form-select-sm" required>
                            <option value="sakit">Sakit</option>
                            <option value="cuti">Cuti</option>
                            <option value="izin_pribadi">Izin Kepentingan Pribadi</option>
                            <option value="dinas_luar">Dinas Luar</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Dari Tanggal</label>
                            <input type="date" name="tanggal_mulai" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Sampai Tanggal</label>
                            <input type="date" name="tanggal_selesai" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Keterangan / Alasan</label>
                        <textarea name="alasan" class="form-control form-control-sm" rows="2" placeholder="Sakit demam berdarah..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan ke Rekap</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL TOLAK IZIN ================= -->
<div class="modal fade" id="modalTolakIzin" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-x-circle me-2"></i>Tolak Pengajuan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="form-tolak-izin">
                @csrf
                <div class="modal-body py-3">
                    <label class="form-label small fw-semibold text-danger">Alasan Penolakan:</label>
                    <textarea name="catatan_admin" class="form-control form-control-sm" rows="3" placeholder="Contoh: Bukti surat tidak valid / Kuota cuti habis" required></textarea>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL SETTING LOKASI KANTOR -->
<div class="modal fade" id="modalSettingLokasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pin-map-fill me-2"></i>Pengaturan Lokasi & Radius Kantor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <p class="text-muted small mb-2">
                    <i class="bi bi-info-circle me-1"></i> Klik pada peta atau geser <i>marker</i> merah untuk menentukan posisi persis kantor Wali Nagari.
                </p>
                <div id="map-kantor" style="height: 350px; border-radius: 12px;" class="mb-3 border"></div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Latitude</label>
                        <input type="text" id="input-lat" class="form-control form-control-sm bg-light" value="{{ $kantorLat }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Longitude</label>
                        <input type="text" id="input-lng" class="form-control form-control-sm bg-light" value="{{ $kantorLng }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Radius Absen (Meter)</label>
                        <input type="number" id="input-radius" class="form-control form-control-sm" value="{{ $kantorRadius }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm fw-bold" onclick="simpanLokasiKantor()">
                    <i class="bi bi-save me-1"></i> Simpan Lokasi Baru
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SETTING JAM KERJA RESMI -->
<div class="modal fade" id="modalSettingJamKerja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clock-fill me-2"></i>Pengaturan Jam Kerja Resmi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <p class="text-muted small mb-3">
                    <i class="bi bi-info-circle me-1"></i> Jam kerja ini digunakan sebagai acuan perhitungan otomatis Keterlambatan (**TL 1–4**) dan Pulang Sebelum Waktu (**PSW 1–4**).
                </p>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Jam Masuk Resmi</label>
                        <input type="time" id="input-jam-masuk" class="form-control" value="{{ $jamMasuk }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Jam Pulang Resmi</label>
                        <input type="time" id="input-jam-pulang" class="form-control" value="{{ $jamPulang }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning btn-sm fw-bold text-dark" onclick="simpanJamKerja()">
                    <i class="bi bi-save me-1"></i> Simpan Jam Kerja
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL JURNAL ABSENSI -->
<div class="modal fade" id="modalDetailPegawai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="detail-title-nama">Jurnal Absensi Staf</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="bg-light p-3 rounded mb-3 border">
                    <h6 class="fw-bold text-success mb-1" id="detail-info-nama">-</h6>
                    <span class="text-muted small" id="detail-info-meta">Jabatan: - | NIP: -</span>
                </div>
                <h6 class="fw-bold text-muted small mb-2"><i class="bi bi-journal-text me-1"></i>Log Riwayat Kehadiran Bulanan</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 11px;">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Hari / Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Status Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status Pulang</th>
                                <th>Peta Lokasi (Koor)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detail-table-body">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup Jurnal</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL EDIT ABSEN HARIAN MANUAL (BARU) ================= -->
<div class="modal fade" id="modalEditAbsenHarian" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title fw-bold small"><i class="bi bi-pencil-square me-2"></i>Koreksi Jurnal Absensi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST" id="form-edit-absen">
                @csrf
                <div class="modal-body p-3">
                    <div class="alert alert-info small py-2 px-3 mb-3">
                        <i class="bi bi-calendar-event me-1"></i> <strong>Tanggal:</strong> <span id="edit-absen-tanggal">-</span>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Masuk</label>
                            <input type="time" step="1" name="jam_masuk" id="edit-absen-jam-masuk" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Status Masuk</label>
                            <select name="status_masuk" id="edit-absen-status-masuk" class="form-select form-select-sm" required>
                                <option value="Tepat Waktu">Tepat Waktu</option>
                                <option value="TL 1">TL 1</option>
                                <option value="TL 2">TL 2</option>
                                <option value="TL 3">TL 3</option>
                                <option value="TL 4">TL 4</option>
                                <option value="-">-</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Pulang</label>
                            <input type="time" step="1" name="jam_pulang" id="edit-absen-jam-pulang" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Status Pulang</label>
                            <select name="status_pulang" id="edit-absen-status-pulang" class="form-select form-select-sm" required>
                                <option value="Tepat Waktu">Tepat Waktu</option>
                                <option value="PSW 1">PSW 1</option>
                                <option value="PSW 2">PSW 2</option>
                                <option value="PSW 3">PSW 3</option>
                                <option value="PSW 4">PSW 4</option>
                                <option value="-">-</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL KELOLA PEGAWAI -->
<div class="modal fade" id="modalKelolaPegawai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-sliders me-2"></i>Kelola Akun Staf Pegawai</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <button class="btn btn-success btn-sm mb-3 fw-semibold shadow-sm" data-bs-toggle="collapse" data-bs-target="#collapseTambahPegawai">
                    <i class="bi bi-person-plus-fill me-1"></i> Daftarkan Pegawai Baru
                </button>

                <div class="collapse mb-3" id="collapseTambahPegawai">
                    <div class="card p-3 border border-success-subtle bg-light shadow-sm">
                        <form action="{{ route('admin.pegawai.store') }}" method="POST">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">NIP</label>
                                    <input type="text" name="nip" class="form-control form-control-sm" placeholder="Isi '-' jika tidak ada">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">Email (Akun)</label>
                                    <input type="email" name="email" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">Password</label>
                                    <input type="password" name="password" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">Jabatan</label>
                                    <input type="text" name="jabatan" class="form-control form-control-sm" placeholder="Contoh: Kaur Keuangan" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">Status Kerja</label>
                                    <select name="status_kerja" class="form-select form-select-sm" required>
                                        <option value="Aktif">Aktif</option>
                                        <option value="Kontrak">Kontrak</option>
                                        <option value="Magang">Magang</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm w-100 mt-2">Simpan Akun Pegawai</button>
                        </form>
                    </div>
                </div>

                <h6 class="fw-bold text-muted mb-2 small mt-2">Daftar Akun Terdaftar</h6>
                <div class="list-group gap-2">
                    @forelse($daftarPegawai as $p)
                    <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-dark">{{ $p->name }}</strong>
                            <div class="text-muted" style="font-size: 11px;">{{ $p->jabatan }} | {{ $p->email }}</div>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-outline-primary btn-sm px-2 py-1" style="font-size: 11px;" onclick="bukaModalEdit('{{ $p->id }}', '{{ $p->name }}', '{{ $p->nip }}', '{{ $p->jabatan }}')"><i class="bi bi-pencil-square"></i></button>
                            <button class="btn btn-outline-danger btn-sm px-2 py-1" style="font-size: 11px;" onclick="konfirmasiHapus('{{ $p->id }}', '{{ $p->name }}')"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    @empty
                    <div class="text-center p-3 text-muted small">Belum ada pegawai terdaftar.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditAkun" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold small"><i class="bi bi-pencil-square me-2"></i>Edit Data Akun Pegawai</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST" id="form-edit-pegawai">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" id="edit-nama" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">NIP</label>
                        <input type="text" id="edit-nip" name="nip" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Jabatan</label>
                        <input type="text" id="edit-jabatan" name="jabatan" class="form-control form-control-sm" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKonfirmasiHapus" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Peringatan Hapus</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1 small">Apakah Anda beneran yakin ingin menghapus akun pegawai bernama:</p>
                <h6 class="fw-bold text-danger" id="hapus-nama-target">Nama Pegawai</h6>
                <span class="text-muted d-block mt-2" style="font-size: 10px;">Tindakan ini permanen dan menghapus seluruh riwayat absen terkait.</span>
            </div>
            <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <form action="#" method="POST" id="form-hapus-pegawai">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus Akun</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="nav-bottom d-flex align-items-center border-top">
    <div class="nav-item-box active" onclick="switchPage('utama', this)">
        <i class="bi bi-speedometer2 fs-4"></i><br>
        <span style="font-size: 11px;">Dashboard</span>
    </div>
    <div class="nav-item-box" onclick="switchPage('profil', this)">
        <i class="bi bi-person-workspace fs-4"></i><br>
        <span style="font-size: 11px;">Profil</span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Sukses!', text: "{{ session('success') }}", confirmButtonColor: '#198754' });
</script>
@endif
@if(session('error'))
<script>
    Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}", confirmButtonColor: '#dc3545' });
</script>
@endif

<script>
    let map, marker, circle;

    function updateClockAdmin() {
        const now = new Date();
        const opsiTanggal = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        const tanggalIndo = now.toLocaleDateString('id-ID', opsiTanggal);
        
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        if (document.getElementById('admin-realtime-date')) {
            document.getElementById('admin-realtime-date').innerText = tanggalIndo;
        }
        if (document.getElementById('admin-realtime-clock')) {
            document.getElementById('admin-realtime-clock').innerText = `${hours}:${minutes}:${seconds}`;
        }
    }
    setInterval(updateClockAdmin, 1000);
    updateClockAdmin();

    document.getElementById('modalSettingLokasi').addEventListener('shown.bs.modal', function () {
        let defaultLat = parseFloat(document.getElementById('input-lat').value) || -1.0825000;
        let defaultLng = parseFloat(document.getElementById('input-lng').value) || 100.8250000;
        let defaultRadius = parseInt(document.getElementById('input-radius').value) || 50;

        if (!map) {
            map = L.map('map-kantor').setView([defaultLat, defaultLng], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);
            marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
            circle = L.circle([defaultLat, defaultLng], { color: '#198754', fillColor: '#198754', fillOpacity: 0.2, radius: defaultRadius }).addTo(map);

            marker.on('dragend', function () {
                let position = marker.getLatLng();
                updateInputs(position.lat, position.lng);
            });

            map.on('click', function (e) {
                updateInputs(e.latlng.lat, e.latlng.lng);
            });

            document.getElementById('input-radius').addEventListener('input', function() {
                let rad = parseInt(this.value) || 0;
                circle.setRadius(rad);
            });

        } else {
            map.setView([defaultLat, defaultLng], 17);
            marker.setLatLng([defaultLat, defaultLng]);
            circle.setLatLng([defaultLat, defaultLng]);
            circle.setRadius(defaultRadius);
            map.invalidateSize();
        }
    });

    function updateInputs(lat, lng) {
        let formattedLat = parseFloat(lat).toFixed(7);
        let formattedLng = parseFloat(lng).toFixed(7);

        document.getElementById('input-lat').value = formattedLat;
        document.getElementById('input-lng').value = formattedLng;

        if (marker) marker.setLatLng([formattedLat, formattedLng]);
        if (circle) circle.setLatLng([formattedLat, formattedLng]);
    }

    function konfirmasiSetujui(id) {
        Swal.fire({
            title: 'Setujui Pengajuan?',
            text: "Status absensi pegawai akan otomatis disesuaikan dengan pengajuan ini.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Setujui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-setujui-' + id).submit();
            }
        });
    }

    function simpanLokasiKantor() {
        let lat = document.getElementById('input-lat').value;
        let lng = document.getElementById('input-lng').value;
        let radius = document.getElementById('input-radius').value;

        Swal.fire({ title: 'Menyimpan Lokasi...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        fetch('{{ route("admin.updateLokasi") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ latitude: String(lat), longitude: String(lng), radius: parseInt(radius) })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal menyimpan ke database');
            return data;
        })
        .then(data => {
            if (data.success) { Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, confirmButtonColor: '#198754' }).then(() => location.reload()); }
            else { Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, confirmButtonColor: '#dc3545' }); }
        }).catch(error => { Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: error.message, confirmButtonColor: '#dc3545' }); });
    }

    function simpanJamKerja() {
        let jMasuk = document.getElementById('input-jam-masuk').value;
        let jPulang = document.getElementById('input-jam-pulang').value;

        Swal.fire({ title: 'Menyimpan Jam Kerja...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        fetch('{{ route("admin.updateJamKerja") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ jam_masuk: jMasuk, jam_pulang: jPulang })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal menyimpan jam kerja');
            return data;
        })
        .then(data => {
            if (data.success) { Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, confirmButtonColor: '#198754' }).then(() => location.reload()); }
            else { Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, confirmButtonColor: '#dc3545' }); }
        }).catch(error => { Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: error.message, confirmButtonColor: '#dc3545' }); });
    }

    function bukaModalDetail(nama, jabatan, nip, riwayatJson) {
        document.getElementById('detail-title-nama').innerText = "Jurnal Absensi: " + nama;
        document.getElementById('detail-info-nama').innerText = nama;
        document.getElementById('detail-info-meta').innerText = "Jabatan: " + jabatan + " | NIP: " + (nip && nip !== 'null' ? nip : '-');
        
        const riwayat = JSON.parse(riwayatJson);
        const tbody = document.getElementById('detail-table-body');
        tbody.innerHTML = '';

        if(riwayat.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada riwayat presensi di bulan ini.</td></tr>`;
        } else {
            riwayat.forEach(item => {
                let colorMasuk = (item.status_masuk === 'Tepat Waktu') ? 'bg-success' : (item.status_masuk !== '-' ? 'bg-warning text-dark' : 'bg-secondary');
                let colorPulang = (item.status_pulang === 'Tepat Waktu') ? 'bg-success' : (item.status_pulang !== '-' ? 'bg-danger' : 'bg-secondary');

                let linkMap = (item.lat !== '-' && item.lng !== '-') 
                    ? `<a href="https://maps.google.com/?q=${item.lat},${item.lng}" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 11px;"><i class="bi bi-geo-alt"></i> Cek</a>`
                    : '<span class="text-muted">-</span>';

                // Tampilan Jam (jika ada isinya tambahkan tulisan WIB untuk frontend)
                let jamMasukTampil = item.jam_masuk ? item.jam_masuk + ' WIB' : '-';
                let jamPulangTampil = item.jam_pulang ? item.jam_pulang + ' WIB' : '-';

                // Tombol Edit yang memanggil fungsi JS baru
                let btnEdit = `<button type="button" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 11px;" onclick="bukaModalEditAbsen('${item.id}', '${item.hari}', '${item.jam_masuk}', '${item.status_masuk}', '${item.jam_pulang}', '${item.status_pulang}')" title="Koreksi Absen"><i class="bi bi-pencil"></i></button>`;

                tbody.innerHTML += `
                    <tr>
                        <td><strong>${item.hari}</strong></td>
                        <td class="text-center">${jamMasukTampil}</td>
                        <td class="text-center"><span class="badge ${colorMasuk}">${item.status_masuk}</span></td>
                        <td class="text-center">${jamPulangTampil}</td>
                        <td class="text-center"><span class="badge ${colorPulang}">${item.status_pulang}</span></td>
                        <td class="text-center">${linkMap}</td>
                        <td class="text-center">${btnEdit}</td>
                    </tr>
                `;
            });
        }

        var myModal = new bootstrap.Modal(document.getElementById('modalDetailPegawai'));
        myModal.show();
    }

    // ====================================================================
    // FUNGSI UNTUK MEMBUKA POP-UP EDIT ABSEN MANUAL OLEH ADMIN
    // ====================================================================
    function bukaModalEditAbsen(id, hari, jamMasuk, statusMasuk, jamPulang, statusPulang) {
        // Arahkan form ke rute update absen
        document.getElementById('form-edit-absen').action = '/admin/absen/' + id + '/update';
        
        // Tampilkan hari/tanggal di modal
        document.getElementById('edit-absen-tanggal').innerText = hari;

        // Isi form dengan data saat ini (format jam HH:MM:SS)
        document.getElementById('edit-absen-jam-masuk').value = (jamMasuk && jamMasuk !== '-') ? jamMasuk : '';
        document.getElementById('edit-absen-jam-pulang').value = (jamPulang && jamPulang !== '-') ? jamPulang : '';
        
        document.getElementById('edit-absen-status-masuk').value = statusMasuk;
        document.getElementById('edit-absen-status-pulang').value = statusPulang;

        // Munculkan Modal Edit (ditumpuk di atas modal Log Absen)
        var modalEdit = new bootstrap.Modal(document.getElementById('modalEditAbsenHarian'));
        modalEdit.show();
    }

    function bukaModalTolakIzin(id) {
        document.getElementById('form-tolak-izin').action = '/admin/izin/' + id + '/tolak';
        var myModal = new bootstrap.Modal(document.getElementById('modalTolakIzin'));
        myModal.show();
    }

    function bukaModalEdit(id, nama, nip, jabatan) {
        document.getElementById('edit-nama').value = nama;
        document.getElementById('edit-nip').value = (nip && nip !== 'null') ? nip : '';
        document.getElementById('edit-jabatan').value = jabatan;
        document.getElementById('form-edit-pegawai').action = '/admin/pegawai/' + id + '/update';
        var myModal = new bootstrap.Modal(document.getElementById('modalEditAkun'));
        myModal.show();
    }

    function konfirmasiHapus(id, nama) {
        document.getElementById('hapus-nama-target').innerText = nama;
        document.getElementById('form-hapus-pegawai').action = '/admin/pegawai/' + id + '/delete';
        var myModal = new bootstrap.Modal(document.getElementById('modalKonfirmasiHapus'));
        myModal.show();
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