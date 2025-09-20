<?php
require_once __DIR__ . '/config.php';

$errors = [];
$old = [
    'name' => '',
    'email' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $terms = isset($_POST['terms']);

    $old['name'] = $name;
    $old['email'] = $email;

    // Validasi server-side
    if ($name === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Kata sandi minimal 8 karakter.';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Konfirmasi kata sandi tidak cocok.';
    }
    if (!$terms) {
        $errors[] = 'Anda harus menyetujui Syarat & Ketentuan.';
    }

    if (empty($errors)) {
        try {
            $pdo = db();

            // Cek unik email
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain atau masuk.';
            } else {
                // Simpan user
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
                $ins->execute([$name, $email, $hash]);

                // Redirect ke login dengan tanda berhasil
                redirect('login.php?registered=1');
            }
        } catch (Throwable $t) {
            // Tampilkan pesan generik (log detailnya di production)
            $errors[] = 'Terjadi kesalahan pada server. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar - KF OLX</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #002f34;
            --secondary-color: #23e5db;
            --accent-color: #ffce32;
            --text-dark: #002f34;
            --text-light: #7c8a97;
            --border-color: #e6e8ea;
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: var(--text-dark); background-color: #fff; }

        .navbar-brand { font-weight: bold; font-size: 2rem; color: var(--primary-color) !important; }
        .navbar { background-color: white !important; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 1rem 0; }

        .page-header { background: linear-gradient(135deg, var(--primary-color) 0%, #004d56 100%); color: #fff; padding: 2.5rem 0; margin-bottom: 2rem; }

        .auth-card { border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }

        .form-control:focus, .form-check-input:focus, .form-select:focus { border-color: var(--secondary-color); box-shadow: 0 0 0 0.2rem rgba(35, 229, 219, 0.25); }

        .btn-primary-custom { background-color: var(--secondary-color); color: var(--primary-color); border: none; font-weight: 700; }
        .btn-primary-custom:hover { background-color: #1bc5bb; color: var(--primary-color); }

        .btn-cta { background-color: var(--accent-color); border: none; color: var(--primary-color); font-weight: 700; }
        .btn-cta:hover { background-color: #e6b82e; color: var(--primary-color); }

        .footer { background-color: var(--primary-color); color: white; padding: 3rem 0 1rem; margin-top: 4rem; }
        .footer-link { color: #b3c5c8; text-decoration: none; transition: color 0.3s ease; }
        .footer-link:hover { color: var(--secondary-color); }

        .divider-text { position: relative; text-align: center; margin: 1.5rem 0; color: var(--text-light); font-size: 0.9rem; }
        .divider-text::before, .divider-text::after { content: ""; position: absolute; top: 50%; width: 40%; height: 1px; background: var(--border-color); }
        .divider-text::before { left: 0; }
        .divider-text::after { right: 0; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store"></i> KF OLX
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories.php">Kategori</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">Tentang</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="btn btn-cta" href="post-add.php"><i class="fas fa-plus"></i> Pasang Iklan</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="page-header">
        <div class="container">
            <h1 class="h3 m-0">Daftar Akun Baru</h1>
            <p class="m-0 text-light">Buat akun untuk mulai jual beli dengan mudah</p>
        </div>
    </header>

    <!-- Register Form -->
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card auth-card p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-2" style="width: 40px; height: 40px; border-radius: 8px; background:#eaf6f7; display:flex; align-items:center; justify-content:center; color:var(--primary-color)">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2 class="h5 m-0">Daftar</h2>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <div class="fw-semibold mb-1">Pendaftaran gagal:</div>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err): ?>
                                    <li><?php echo e($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="post" novalidate>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Nama Anda" value="<?php echo e($old['name']); ?>" required>
                                <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" value="<?php echo e($old['email']); ?>" required>
                                <div class="invalid-feedback">Silakan masukkan email yang valid.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Kata Sandi</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 8 karakter" minlength="8" required>
                                <div class="invalid-feedback">Kata sandi minimal 8 karakter.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirm" class="form-label">Konfirmasi Kata Sandi</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Ulangi kata sandi" required>
                                <div class="invalid-feedback">Konfirmasi kata sandi tidak cocok.</div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        Saya menyetujui <a href="#" class="text-decoration-none" style="color:var(--secondary-color)">Syarat & Ketentuan</a> dan <a href="#" class="text-decoration-none" style="color:var(--secondary-color)">Kebijakan Privasi</a>.
                                    </label>
                                    <div class="invalid-feedback">Anda harus menyetujui syarat & ketentuan.</div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 mt-3">Daftar</button>

                        <div class="divider-text">atau</div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary"><i class="fab fa-google me-2"></i> Daftar dengan Google</button>
                            <button type="button" class="btn btn-outline-secondary"><i class="fab fa-facebook me-2"></i> Daftar dengan Facebook</button>
                        </div>

                        <p class="text-center mt-3 mb-0">Sudah punya akun? <a href="login.php" class="text-decoration-none" style="color:var(--secondary-color)">Masuk</a></p>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3"><i class="fas fa-store"></i> KF OLX</h5>
                    <p class="text-muted">Platform jual beli online terpercaya di Indonesia. Jual dan beli dengan mudah, aman, dan terpercaya.</p>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Kategori</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">Mobil</a></li>
                        <li><a href="#" class="footer-link">Motor</a></li>
                        <li><a href="#" class="footer-link">Handphone</a></li>
                        <li><a href="#" class="footer-link">Elektronik</a></li>
                        <li><a href="#" class="footer-link">Properti</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Bantuan</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">Cara Jual</a></li>
                        <li><a href="#" class="footer-link">Cara Beli</a></li>
                        <li><a href="#" class="footer-link">Tips Aman</a></li>
                        <li><a href="#" class="footer-link">FAQ</a></li>
                        <li><a href="#" class="footer-link">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Perusahaan</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">Tentang Kami</a></li>
                        <li><a href="#" class="footer-link">Karir</a></li>
                        <li><a href="#" class="footer-link">Blog</a></li>
                        <li><a href="#" class="footer-link">Press</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="footer-link">Kebijakan Privasi</a></li>
                        <li><a href="#" class="footer-link">Panduan Komunitas</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color:#4a5c6a" />
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2024 KF OLX. Semua hak dilindungi.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">Made with <i class="fas fa-heart text-danger"></i> in Indonesia</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Basic client-side validation & password match check
        (function(){
            const form = document.querySelector('form');
            const email = document.getElementById('email');
            const name = document.getElementById('name');
            const pwd = document.getElementById('password');
            const pwd2 = document.getElementById('password_confirm');
            const terms = document.getElementById('terms');

            function validatePwdMatch(){
                if (pwd.value && pwd2.value && pwd.value !== pwd2.value) {
                    pwd2.classList.add('is-invalid');
                    return false;
                } else {
                    pwd2.classList.remove('is-invalid');
                    return true;
                }
            }

            pwd2.addEventListener('input', validatePwdMatch);
            pwd.addEventListener('input', validatePwdMatch);

            form.addEventListener('submit', function(e){
                let valid = true;
                if (!name.value) { name.classList.add('is-invalid'); valid = false; } else { name.classList.remove('is-invalid'); }
                if (!email.value || !email.checkValidity()) { email.classList.add('is-invalid'); valid = false; } else { email.classList.remove('is-invalid'); }
                if (!pwd.value || pwd.value.length < 8) { pwd.classList.add('is-invalid'); valid = false; } else { pwd.classList.remove('is-invalid'); }
                if (!validatePwdMatch()) valid = false;
                if (!terms.checked) { terms.classList.add('is-invalid'); valid = false; } else { terms.classList.remove('is-invalid'); }
                if (!valid) e.preventDefault();
            });
        })();
    </script>
</body>
</html>
