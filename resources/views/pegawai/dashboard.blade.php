<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pegawai - Wali Nagari Salimpek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ==========================================
           CSS MOBILE-FIRST (TAMPILAN HP)
           ========================================== */
        body { padding-bottom: 80px; padding-top: 0; }
        .sidebar-left { display: none !important; } 
        
        /* Efek Gradasi Hijau Pudar Lengkung Atas */
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
        
        /* Kontainer Status Putih Solid Bersih Tanpa Transparan di HP */
        .header-gradient-mobile .alert-secondary {
            background: #ffffff !important;
            border: none !important;
            color: #212529 !important; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05) !important;
        }

        /* Warna teks status di dalam box putih (Tanpa Opacity/Transparan) */
        .text-success-light { color: #198754 !important; font-weight: 700 !important; }
        .header-gradient-mobile .text-warning { color: #b8860b !important; font-weight: 700 !important; } 

        /* ==========================================
           STYLE REKAP KARTU HORIZONTAL SATU BARIS
           ========================================== */
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
            padding: 4px 16px;
            border-radius: 50rem;
            display: inline-block;
            font-size: 14px;
            min-width: 48px;
        }
        .label-status {
            font-size: 11px;
            opacity: 0.85;
            display: block;
            margin-top: 4px;
        }

        /* Gaya Hotbar Bawah Modern dengan Tombol Tengah Menonjol */
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
            box-shadow: 0 3px 10px rgba(25, 135, 84, 0.4);
            font-size: 11px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
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

        /* ==========================================
           CSS DESKTOP (TAMPILAN LAPTOP/PC)
           ========================================== */
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

<!-- ==========================================
     SIDEBAR KIRI (HANYA MUNCUL DI PC)
     ========================================== -->
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

<!-- ==========================================
     MENU 1: UTAMA (REKAP & DETAIL HARI KINERJA)
     ========================================== -->
<div id="page-utama" class="page-content active container py-4">
    <!-- Header Pegawai -->
    <div class="header-gradient-mobile text-center text-md-start mb-4 mx-[-12px] mx-md-0">
        <h4 class="fw-bold mb-0">{{ Auth::user()->name }} ({{ Auth::user()->status_kerja }})</h4>
        <p class="mb-2 opacity-90">{{ Auth::user()->jabatan }}</p>
        
        <!-- Jam & Tanggal Realtime Terintegrasi -->
        <div class="mb-2 small opacity-90">
            <i class="bi bi-calendar3 me-1"></i> <span id="realtime-date">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
            <span class="mx-1">|</span>
            <i class="bi bi-clock me-1"></i> <span id="realtime-clock">00:00:00</span> WIB
        </div>
        
        <!-- Info Status Dinamis (Berubah Otomatis Sesuai Database) -->
        <div class="alert alert-secondary py-2 px-3 d-inline-block small mb-0 shadow-sm">
            <i class="bi bi-info-circle-fill me-1"></i> Status Hari Ini: 
            <span id="status-lokasi" class="{{ $absenHariIni ? 'text-success-light fw-bold' : 'text-warning fw-bold' }}">
                {{ $statusHariIni }}
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Tombol Absen Versi Desktop (Otomatis Lock jika sudah absen) -->
        <div class="col-md-4 text-center desktop-absen-card">
            <div class="card border-0 shadow-sm p-4 d-flex flex-column align-items-center justify-content-center h-100">
                <h5 class="fw-semibold mb-3">Ambil Absensi Hari Ini</h5>
                <button type="button" class="btn {{ ($absenHariIni || !$isJamKerja) ? 'btn-secondary' : 'btn-success' }} btn-lg rounded-circle shadow my-2 btn-absen-trigger" style="width: 130px; height: 130px;" {{ ($absenHariIni || !$isJamKerja) ? 'disabled' : '' }}>
                    <span class="fw-bold">HADIR</span>
                </button>
            </div>
        </div>

        <!-- Container Rekap Bulanan Satu Baris -->
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

                <div class="row text-center g-0">
                    <div class="col-4">
                        <div class="badge-num">{{ $totalHadir }}</div>
                        <span class="label-status">Hadir</span>
                    </div>
                    <div class="col-4">
                        <div class="badge-num">{{ $totalTerlambat }}</div>
                        <span class="label-status">Terlambat</span>
                    </div>
                    <div class="col-4">
                        <div class="badge-num">{{ $tanpaKeterangan }}</div>
                        <span class="label-status">Tanpa Keterangan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bulan & Tabel Detail Riwayat -->
    <div class="card border-0 shadow-sm p-3 mt-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-3 gap-2">
            <h5 class="fw-bold mb-0">Detail Presensi Harian</h5>
            <form action="/pegawai/dashboard" method="GET" id="form-bulan" class="input-group" style="max-width: 250px;">
                <span class="input-group-text bg-white small" style="font-size: 13px;">Bulan</span>
                <input type="month" name="bulan" class="form-control form-control-sm" value="{{ $bulanDipilih }}" onchange="document.getElementById('form-bulan').submit();">
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                <thead class="table-light">
                    <tr>
                        <th>Hari / Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatAbsen as $absen)
                        <tr>
                            <td><strong>{{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('l, d F Y') }}</strong></td>
                            <td>{{ date('H:i', strtotime($absen->jam_masuk)) }} WIB</td>
                            <td>
                                @if($absen->status == 'Tepat Waktu' || $absen->status == 'Hadir')
                                    <span class="badge bg-success">Tepat Waktu</span>
                                @else
                                    <span class="badge bg-warning text-dark">Terlambat</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Belum ada riwayat presensi di bulan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     MENU 2: PROFIL, INFO AKUN & PENGATURAN
     ========================================== -->
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
                    <!-- FIX: Tombol Lokasi Kantor Sekarang Aktif Mengarah ke Google Maps Sesuai Koordinat .env -->
                    <a href="https://www.google.com/maps/search/?api=1&query={{ env('KANTOR_LATITUDE') }},{{ env('KANTOR_LONGITUDE') }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 rounded mb-2 bg-light text-decoration-none">
                        <div>
                            <i class="bi bi-geo-alt-fill text-danger me-3 fs-5"></i>
                            <span class="fw-semibold text-dark">Lokasi Kantor Wali Nagari</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>

                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 rounded mb-2 bg-light">
                        <div>
                            <i class="bi bi-shield-lock-fill text-warning me-3 fs-5"></i>
                            <span class="fw-semibold">Pengaturan Akun (Reset PW)</span>
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

<!-- ==========================================
     HOTBAR NAVIGATION (HANYA DI MOBILE - DENGAN HADIR DI TENGAH)
     ========================================== -->
<div class="nav-bottom d-flex align-items-center border-top">
    <div class="nav-item-box active" onclick="switchPage('utama', this)">
        <i class="bi bi-clock-history fs-4"></i><br>
        <span style="font-size: 11px;">Presensi</span>
    </div>
    
    <div class="center-absen-wrapper">
        <button type="button" class="btn {{ ($absenHariIni || !$isJamKerja) ? 'btn-secondary' : 'btn-success' }} btn-hadir-mobile btn-absen-trigger" {{ ($absenHariIni || !$isJamKerja) ? 'disabled' : '' }}>
            HADIR
        </button>
    </div>
    
    <div class="nav-item-box" onclick="switchPage('profil', this)">
        <i class="bi bi-person-bounding-box fs-4"></i><br>
        <span style="font-size: 11px;">Profil</span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const statusLokasi = document.getElementById('status-lokasi');

    // Jam Realtime
    function updateClock() {
        const now = new Date();
        
        const opsiTanggal = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        const tanggalIndo = now.toLocaleDateString('id-ID', opsiTanggal);
        
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        // Cek keberadaan elemen agar tidak error saat resize / ganti tab menu
        if (document.getElementById('realtime-date')) {
            document.getElementById('realtime-date').innerText = tanggalIndo;
        }
        if (document.getElementById('realtime-clock')) {
            document.getElementById('realtime-clock').innerText = `${hours}:${minutes}:${seconds}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
    
    // Logika Kirim Absen via AJAX
    document.querySelectorAll('.btn-absen-trigger').forEach(btn => {
        btn.addEventListener('click', function() {
            statusLokasi.className = "fw-semibold text-warning";
            statusLokasi.innerText = "Mencari GPS...";
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    let lat = position.coords.latitude;
                    let lng = position.coords.longitude;
                    
                    statusLokasi.className = "fw-semibold text-info";
                    statusLokasi.innerText = "Mengirim presensi...";
                    
                    fetch('/pegawai/absen', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ latitude: lat, longitude: lng })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            statusLokasi.className = "text-success-light fw-bold";
                            statusLokasi.innerText = "Sudah Absen (Berhasil)";
                            
                            document.querySelectorAll('.btn-absen-trigger').forEach(b => {
                                b.disabled = true;
                                b.classList.replace('btn-success', 'btn-secondary');
                            });
                            
                            setTimeout(() => { location.reload(); }, 1200);
                        } else {
                            statusLokasi.className = "fw-semibold text-danger";
                            statusLokasi.innerText = data.message;
                        }
                    })
                    .catch(error => {
                        statusLokasi.className = "fw-semibold text-danger";
                        statusLokasi.innerText = "Gagal mengirim data.";
                    });
                    
                }, function(error) {
                    statusLokasi.className = "fw-semibold text-danger";
                    statusLokasi.innerText = "GPS error / Izin ditolak!";
                }, { enableHighAccuracy: true });
            } else {
                statusLokasi.className = "fw-semibold text-danger";
                statusLokasi.innerText = "Browser tidak mendukung GPS.";
            }
        });
    });

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