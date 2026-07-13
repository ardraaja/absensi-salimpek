<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Absensi Wali Nagari Salimpek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="text-center mb-3 fw-bold">Absensi Digital</h3>
                    <p class="text-muted text-center mb-4">Kantor Wali Nagari Salimpek</p>

                    @if(session()->has('loginError'))
                        <div class="alert alert-danger text-center py-2" role="alert">
                            {{ session('loginError') }}
                        </div>
                    @endif

                    <form action="/login" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="contoh@salimpek.go.id" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Masuk</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>