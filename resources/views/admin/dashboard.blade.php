<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Wali Nagari Salimpek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ==========================================
           CSS MOBILE-FIRST (TAMPILAN HP)
           ========================================== */
        body { padding-bottom: 80px; padding-top: 0; }
        .sidebar-left { display: none !important; } 
        
        /* Banner Solid Warna Tema Admin */
        .admin-banner-solid {
            background-color: #198754 !important;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            padding: 25px 20px;
        }

        /* Gaya Rekap Satu Baris Horizontal */
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

        /* Gaya Hotbar Bawah */
        .nav-bottom { position: fixed; bottom: 0; left: 0; right: 0; height: 65px; background: white; box-shadow: 0 -2px 15px rgba(0,0,0,0.1); z-index: 1000; }
        .nav-item-box { flex: 1; text-align: center; color: #6c757d; cursor: pointer; padding: 10px 0; }
        .nav-item-box.active { color: #198754; font-weight: bold; }

        .page-content { display: none; }
        .page-content.active { display: block; }

        /* ==========================================
           CSS DESKTOP (TAMPILAN LAPTOP/PC)
           ========================================== */
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

<!-- ==========================================
     SIDEBAR KIRI (HANYA MUNCUL DI PC)
     ========================================== -->
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

<!-- ==========================================
     MENU 1: UTAMA (DASHBOARD & TABEL REKAP PEGAWAI)
     ========================================== -->
<div id="page-utama" class="page-content active container py-4">
    <!-- Header Admin -->
    <div class="mb-4 text-center text-md-start">
        <h4 class="fw-bold mb-0 text-dark">Dashboard Wali Nagari</h4>
        <p class="text-muted small mb-0">Nagari Salimpek, Kecamatan Lembah Gumanti</p>
    </div>

    <!-- Container Rekap Presensi Hari Ini (Gaya Satu Baris) -->
    <div class="card rekap-card shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-box me-3">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0" style="font-size: 16px;">Rekap Presensi Hari Ini</h5>
                    <span class="small opacity-75">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>
            <div class="pt-1">
                <span class="small fw-semibold opacity-90">{{ $totalPegawai }} Total Pegawai</span>
            </div>
        </div>

        <div class="row text-center g-0">
            <div class="col-4">
                <div class="badge-num bg-success">{{ $hadir }}</div>
                <span class="label-status">Hadir</span>
            </div>
            <div class="col-4">
                <div class="badge-num bg-warning text-dark">{{ $terlambat }}</div>
                <span class="label-status">Terlambat</span>
            </div>
            <div class="col-4">
                <div class="badge-num bg-danger">{{ $belumAbsen }}</div>
                <span class="label-status">Belum Absen</span>
            </div>
        </div>
    </div>

    <!-- Tabel Monitoring Akumulasi Bulanan Seluruh Pegawai -->
    <div class="card border-0 shadow-sm p-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-3 gap-2">
            <h5 class="fw-bold mb-0">Rekapitulasi Kinerja Pegawai</h5>
            
            <!-- Pilih Bulan -->
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
                        <th>Jabatan / Status</th>
                        <th>Kapan Terdaftar</th>
                        <th class="text-center text-success">Hadir</th>
                        <th class="text-center text-warning">Telat</th>
                        <th class="text-center text-danger">Alpa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftarPegawai as $pegawai)
                        <tr>
                            <td>
                                <strong>{{ $pegawai->name }}</strong><br>
                                <span class="text-muted small">
                                    NIP: {{ $pegawai->nip ?? '-' }}<br>
                                    {{ $pegawai->email }}
                                </span>
                            </td>
                            <td>
                                {{ $pegawai->jabatan ?? 'Belum Diatur' }}<br>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    {{ $pegawai->status_kerja ?? 'Aktif' }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($pegawai->created_at)->translatedFormat('d F Y') }}</td>
                            <td class="text-center fw-bold text-success">{{ $pegawai->count_hadir }}</td>
                            <td class="text-center fw-bold text-warning">{{ $pegawai->count_telat }}</td>
                            <td class="text-center fw-bold text-danger">{{ $pegawai->count_alpa }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data akun pegawai terdaftar.</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     MENU 2: PROFIL & PUSAT KELOLA AKUN
     ========================================== -->
<div id="page-profil" class="page-content container py-4">
    <!-- Header Kelola Akun -->
    <div class="admin-banner-solid text-center text-md-start mb-4 mx-[-12px] mx-md-0">
        <h4 class="fw-bold mb-0 text-dark">Manajemen Profil</h4>
        <p class="text-dark small mb-0 opacity-75">Pusat kendali dan administrasi aplikasi</p>
    </div>

    <div class="row g-4 justify-content-center justify-content-md-start">
        <!-- Info Akun Admin -->
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

        <!-- Tombol Menu Akses Admin -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold text-muted mb-3 px-2">Pusat Pengaturan & Akses</h6>
                
                <div class="list-group list-group-flush">
                    <!-- Tombol Kelola Akun Pegawai (Membuka Modal Popup) -->
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 rounded mb-2 bg-light" data-bs-toggle="modal" data-bs-target="#modalKelolaPegawai">
                        <div>
                            <i class="bi bi-people-fill text-primary me-3 fs-5"></i>
                            <span class="fw-semibold">Kelola Akun Pegawai</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </button>

                    <!-- Tombol Cek Lokasi Kantor -->
                    <a href="https://www.google.com/maps/search/?api=1&query={{ env('KANTOR_LATITUDE') }},{{ env('KANTOR_LONGITUDE') }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 rounded mb-2 bg-light text-decoration-none">
                        <div>
                            <i class="bi bi-geo-alt-fill text-danger me-3 fs-5"></i>
                            <span class="fw-semibold text-dark">Lokasi Koordinat Kantor</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>

                    <!-- Tombol Keluar Aplikasi -->
                    <form action="{{ route('logout') }}" method="POST" class="w-100 mt-2">
                        @csrf
                        <button type="submit" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 rounded text-danger fw-bold bg-danger-subtle">
                            <div>
                                <i class="bi bi-box-arrow-left me-3 fs-5"></i>
                                <span>Keluar Aplikasi</span>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     POPUP MODAL: KELOLA AKUN PEGAWAI (DENGAN FUNGSI EDIT & HAPUS)
     ========================================== -->
<div class="modal fade" id="modalKelolaPegawai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-sliders me-2"></i>Kelola Akun Staf Pegawai</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-submit="modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Tombol Tambah Pegawai Baru -->
                <button class="btn btn-success btn-sm mb-3 fw-semibold shadow-sm" data-bs-toggle="collapse" data-bs-target="#collapseTambahPegawai">
                    <i class="bi bi-person-plus-fill me-1"></i> Daftarkan Pegawai Baru
                </button>

                <!-- Form Collapse Tambah Akun -->
                <div class="collapse mb-3" id="collapseTambahPegawai">
                    <div class="card p-3 border border-success-subtle bg-light shadow-sm">
                        <form action="#" method="POST">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">Nama Lengkap</label>
                                    <input type="text" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">NIP</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="Isi '-' jika tidak ada">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">Email (Akun)</label>
                                    <input type="email" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">Password</label>
                                    <input type="password" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">Jabatan</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="Contoh: Kaur Keuangan" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold mb-1">Status Kerja</label>
                                    <select class="form-select form-select-sm" required>
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

                <!-- List Daftar Manajemen Aksi -->
                <h6 class="fw-bold text-muted mb-2 small mt-2">Daftar Akun Terdaftar</h6>
                <div class="list-group gap-2">
                    <!-- Item Contoh Pegawai -->
                    <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-dark">Budi Setiawan</strong>
                            <div class="text-muted" style="font-size: 11px;">Staf Administrasi | budi@salimpek.go.id</div>
                        </div>
                        <div class="d-flex gap-1">
                            <!-- Tombol Trigger Edit Form Modal Popup -->
                            <button class="btn btn-outline-primary btn-sm px-2 py-1" style="font-size: 11px;" onclick="bukaModalEdit('1', 'Budi Setiawan', '13029388484920', 'Kaur Keuangan')">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <!-- Tombol Trigger Hapus Konfirmasi Popup -->
                            <button class="btn btn-outline-danger btn-sm px-2 py-1" style="font-size: 11px;" onclick="konfirmasiHapus('1', 'Budi Setiawan')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     POPUP POPUP REAL: MODAL EDIT DATA AKUN
     ========================================== -->
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
                        <input type="text" id="edit-nama" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">NIP</label>
                        <input type="text" id="edit-nip" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Jabatan</label>
                        <input type="text" id="edit-jabatan" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small text-muted" style="font-size: 11px;">*Kosongkan password jika tidak ingin diganti</label>
                        <input type="password" class="form-control form-control-sm" placeholder="Password Baru">
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

<!-- ==========================================
     POPUP POPUP REAL: MODAL KONFIRMASI HAPUS PERINGATAN
     ========================================== -->
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

<!-- ==========================================
     HOTBAR NAVIGATION (HANYA DI MOBILE)
     ========================================== -->
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
<script>
    // Penanganan Buka Modal Popup Edit
    function bukaModalEdit(id, nama, nip, jabatan) {
        document.getElementById('edit-nama').value = nama;
        document.getElementById('edit-nip').value = nip;
        document.getElementById('edit-jabatan').value = jabatan;
        
        // Atur action form update secara dinamis
        document.getElementById('form-edit-pegawai').action = '/admin/pegawai/' + id + '/update';
        
        // Panggil modal Bootstrap secara manual (bukan local alert browser)
        var myModal = new bootstrap.Modal(document.getElementById('modalEditAkun'));
        myModal.show();
    }

    // Penanganan Buka Modal Popup Hapus Peringatan Real
    function konfirmasiHapus(id, nama) {
        document.getElementById('hapus-nama-target').innerText = nama;
        
        // Atur action form destroy secara dinamis
        document.getElementById('form-hapus-pegawai').action = '/admin/pegawai/' + id + '/delete';
        
        var myModal = new bootstrap.Modal(document.getElementById('modalKonfirmasiHapus'));
        myModal.show();
    }

    // Jalur Pindah Tab Menu SPA Tanpa Reload
    function switchPage(pageId, element) {
        document.querySelectorAll('.page-content').forEach(page => page.classList.remove('active'));
        document.querySelectorAll('.nav-item-box, .sidebar-item').forEach(item => item.classList.remove('active'));
        
        document.getElementById('page-' + pageId).classList.add('active');
        
        const targetClass = element.classList.contains('sidebar-item') ? '.sidebar-item' : '.nav-item-box';
        const items = Array.from(element.parentNode.querySelectorAll(targetClass));
        const index = items.indexOf(element);

        if(index !== -1) {
            document.querySelectorAll('.sidebar-item')[index].classList.add('active');
            document.querySelectorAll('.nav-item-box')[index].classList.add('active');
        }
    }
</script>
</body>
</html>