<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Absensi - Kantor Wali Nagari Salimpek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 30px 25px;
        }
        /* Kotak input melengkung ala referensi */
        .form-control-pill {
            border-radius: 50rem;
            padding: 12px 20px;
            border: 1px solid #dcedc8;
            background-color: #fafafa;
            font-size: 14px;
        }
        .form-control-pill:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.15);
            background-color: #fff;
        }
        /* Tombol hijau lonjong */
        .btn-success-pill {
            border-radius: 50rem;
            padding: 12px 20px;
            background-color: #198754;
            border: none;
            font-weight: 600;
            font-size: 15px;
            color: #fff;
            transition: background 0.2s;
        }
        .btn-success-pill:hover {
            background-color: #146c43;
        }
        .text-nagari {
            color: #0d6efd;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card login-card">
                
                <!-- Logo & Header Instansi -->
                <div class="text-center mb-4">
                    <div class="mb-2">
                        <img src="{{ asset('images/Lambang_Kabupaten_Solok.png') }}" alt="Lambang Kabupaten Solok" style="width: 75px; height: auto;" class="img-fluid">
                    </div>
                    <h5 class="text-nagari mb-1">NAGARI SALIMPEK</h5>
                    <p class="text-muted small mb-0" style="font-size: 11px; line-height: 1.3;">
                        JALAN ALAHAN PANJANG - TALANG BABUNGO KM 3<br>
                        KODEPOS 27371<br>
                        KECAMATAN LEMBAH GUMANTI<br>
                        KABUPATEN SOLOK
                    </p>
                </div>

                @if(session()->has('loginError'))
                    <div class="alert alert-danger text-center py-2 small rounded-pill mb-3" role="alert">
                        {{ session('loginError') }}
                    </div>
                @endif

                <form action="/login" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <input type="email" name="email" id="email" class="form-control form-control-pill" placeholder="Email" required autofocus>
                    </div>

                    <div class="mb-3">
                        <input type="password" name="password" id="password" class="form-control form-control-pill" placeholder="Kata sandi" required>
                    </div>

                    <!-- Pilihan Tampilkan Kata Sandi & Lupa Sandi -->
                    <div class="d-flex justify-content-between align-items-center mb-4 px-2" style="font-size: 13px;">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="tampilPassword" onclick="togglePassword()">
                            <label class="form-check-label text-muted" for="tampilPassword" style="cursor: pointer;">Tampilkan kata sandi</label>
                        </div>
                        <div>
                            <a href="#" onclick="lupaSandiAlert(event)" class="text-decoration-none text-primary fw-semibold">Lupa kata sandi?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success-pill w-100 shadow-sm">Masuk</button>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Fungsi untuk toggle lihat/sembunyikan password
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    }

    // Fungsi interaktif untuk Lupa Kata Sandi
    function lupaSandiAlert(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Lupa Kata Sandi?',
            text: 'Silakan hubungi Administrator / Wali Nagari untuk melakukan reset kata sandi akun Anda secara langsung.',
            icon: 'info',
            confirmButtonColor: '#198754',
            confirmButtonText: 'Mengerti'
        });
    }
</script>

</body>
</html>