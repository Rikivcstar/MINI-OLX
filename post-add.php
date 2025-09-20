<?php
require_once __DIR__ . '/config.php';

// Wajib login untuk mengakses halaman pasang iklan
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'post-add.php';
    redirect('login.php');
}

// Ambil kategori dari database
$pdo = db();
try {
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $t) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasang Iklan - KF OLX</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary-color:#002f34; --secondary-color:#23e5db; --accent-color:#ffce32; --text-dark:#002f34; --text-light:#7c8a97; --border-color:#e6e8ea; }
        body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:var(--text-dark); background:#fff; }
        .navbar-brand { font-weight:bold; font-size:2rem; color:var(--primary-color) !important; }
        .navbar { background:#fff !important; box-shadow:0 2px 4px rgba(0,0,0,.1); padding:1rem 0; }
        .page-header { background:linear-gradient(135deg, var(--primary-color) 0%, #004d56 100%); color:#fff; padding:2.5rem 0; margin-bottom:2rem; }
        .card-form { border:1px solid var(--border-color); border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,.06); }
        .form-control:focus, .form-select:focus { border-color:var(--secondary-color); box-shadow:0 0 0 .2rem rgba(35,229,219,.25); }
        .btn-primary-custom { background:var(--secondary-color); color:var(--primary-color); border:none; font-weight:700; }
        .btn-primary-custom:hover { background:#1bc5bb; color:var(--primary-color); }
        .btn-cta { background:var(--accent-color); border:none; color:var(--primary-color); font-weight:700; }
        .btn-cta:hover { background:#e6b82e; color:var(--primary-color); }
        .footer { background:var(--primary-color); color:#fff; padding:3rem 0 1rem; margin-top:4rem; }
        .footer-link { color:#b3c5c8; text-decoration:none; transition:color .3s ease; }
        .footer-link:hover { color:var(--secondary-color); }
    </style>
    <!-- Anda dapat menambahkan script validasi atau CSS tambahan jika diperlukan -->
    <script>
        // Nonaktifkan submit ganda sederhana
        document.addEventListener('DOMContentLoaded', function(){
            const form = document.getElementById('postAdForm');
            if (form) {
                form.addEventListener('submit', function(){
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerText = 'Mengunggah...';
                    }
                });
            }
        });
    </script>
    <script>
        // Batasi input harga agar tidak negatif
        function normalizePrice(el){ if (!el) return; if (el.value < 0) el.value = 0; }
    </script>
    <script>
        // Preview gambar sederhana (opsional)
        document.addEventListener('DOMContentLoaded', function(){
            const input = document.getElementById('image');
            const preview = document.getElementById('imagePreview');
            if (input && preview) {
                input.addEventListener('change', function(){
                    const file = input.files && input.files[0];
                    if (!file) { preview.src = ''; preview.classList.add('d-none'); return; }
                    const reader = new FileReader();
                    reader.onload = e => { preview.src = e.target.result; preview.classList.remove('d-none'); };
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
    <style>
        /* Small helper for image preview */
        .img-preview { max-height: 200px; border:1px dashed var(--border-color); border-radius:8px; padding:6px; background:#fafafa; }
    </style>
    <!-- End head -->
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-store"></i> KF OLX</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
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
            <h1 class="h3 m-0">Pasang Iklan Baru</h1>
            <p class="m-0 text-light">Isi detail iklan Anda dengan lengkap agar mudah ditemukan pembeli</p>
        </div>
    </header>

    <!-- Form Pasang Iklan -->
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card card-form p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-2" style="width: 40px; height: 40px; border-radius: 8px; background:#eaf6f7; display:flex; align-items:center; justify-content:center; color:var(--primary-color)">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h2 class="h5 m-0">Detail Iklan</h2>
                    </div>

                    <form id="postAdForm" action="post_process.php" method="POST" enctype="multipart/form-data" novalidate>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="title" class="form-label">Judul Iklan</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="Contoh: iPhone 12 Pro Max Mulus" required>
                                <div class="invalid-feedback">Judul iklan wajib diisi.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Kategori</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo (int)$cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Silakan pilih kategori.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="price" class="form-label">Harga (Rp)</label>
                                <input type="number" class="form-control" id="price" name="price" placeholder="0" min="0" oninput="normalizePrice(this)" required>
                                <div class="invalid-feedback">Harga wajib diisi dan tidak boleh negatif.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label">Lokasi</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="Kota/Kabupaten" required>
                                <div class="invalid-feedback">Lokasi wajib diisi.</div>
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="5" placeholder="Jelaskan kondisi, kelengkapan, alasan jual, dll." required></textarea>
                                <div class="invalid-feedback">Deskripsi wajib diisi.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="image" class="form-label">Foto Produk</label>
                                <input class="form-control" type="file" id="image" name="image" accept="image/*" required>
                                <div class="form-text">Format: JPG/PNG. Maks 5MB.</div>
                                <div class="invalid-feedback">Silakan unggah setidaknya 1 foto.</div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <img id="imagePreview" class="img-preview d-none w-100" alt="Preview Gambar" />
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary-custom">Pasang Iklan</button>
                        </div>
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
                <div class="col-md-6"><p class="text-muted mb-0">&copy; 2024 KF OLX. Semua hak dilindungi.</p></div>
                <div class="col-md-6 text-md-end"><p class="text-muted mb-0">Made with <i class="fas fa-heart text-danger"></i> in Indonesia</p></div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Basic client-side validation style
        (function(){
            const form = document.getElementById('postAdForm');
            if (!form) return;
            form.addEventListener('submit', function(e){
                let valid = true;
                ['title','category_id','price','location','description','image'].forEach(id => {
                    const el = document.getElementById(id);
                    if (!el || (el.type !== 'file' && !el.value) || (el.type === 'file' && (!el.files || el.files.length === 0))) {
                        el && el.classList.add('is-invalid');
                        valid = false;
                    } else {
                        el.classList.remove('is-invalid');
                    }
                });
                if (!valid) e.preventDefault();
            });
        })();
    </script>
</body>
</html>