<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail Iklan - KF OLX</title>
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

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            background-color: #fff;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 2rem;
            color: var(--primary-color) !important;
        }

        .navbar {
            background-color: white !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #004d56 100%);
            color: #fff;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .breadcrumb a {
            color: #cfe9eb;
            text-decoration: none;
        }

        .breadcrumb .active {
            color: #fff;
        }

        .card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
        }

        .ad-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .price {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--primary-color);
        }

        .meta {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .thumb {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
        }

        .main-image {
            width: 100%;
            height: 420px;
            object-fit: cover;
            background-color: #f8f9fa;
            border-radius: 10px;
        }

        .badge-category {
            background: var(--secondary-color);
            color: var(--primary-color);
            font-weight: 700;
        }

        .btn-cta {
            background-color: var(--accent-color);
            border: none;
            color: var(--primary-color);
            font-weight: 700;
        }

        .btn-cta:hover {
            background-color: #e6b82e;
            color: var(--primary-color);
        }

        .section-title {
            font-weight: 800;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .seller-card .avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #eaf6f7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .safe-tips li {
            margin-bottom: 0.5rem;
        }

        .ad-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .ad-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .ad-thumb {
            width: 100%;
            height: 160px;
            background: #f7f7f7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .footer-link {
            color: #b3c5c8;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: var(--secondary-color);
        }
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
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Masuk</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php"><i class="fas fa-user-plus"></i> Daftar</a></li>
                    <li class="nav-item ms-2"><a class="btn btn-cta" href="post-add.php"><i class="fas fa-plus"></i> Pasang Iklan</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="page-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="categories.php">Kategori</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Iklan</li>
                </ol>
            </nav>
            <h1 class="h3 m-0">Detail Iklan</h1>
        </div>
    </header>

    <main class="container">
        <div class="row g-4">
            <!-- Left: Gallery + Description -->
            <div class="col-lg-8">
                <div class="card p-3 mb-4">
                    <div class="row g-3 align-items-start">
                        <div class="col-12">
                            <img src="https://via.placeholder.com/960x540?text=Foto+Utama" alt="Foto Utama" class="main-image" id="mainImage" />
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <img class="thumb" src="https://via.placeholder.com/160x90?text=Foto+1" alt="Foto 1" data-src="https://via.placeholder.com/960x540?text=Foto+1" />
                                <img class="thumb" src="https://via.placeholder.com/160x90?text=Foto+2" alt="Foto 2" data-src="https://via.placeholder.com/960x540?text=Foto+2" />
                                <img class="thumb" src="https://via.placeholder.com/160x90?text=Foto+3" alt="Foto 3" data-src="https://via.placeholder.com/960x540?text=Foto+3" />
                                <img class="thumb" src="https://via.placeholder.com/160x90?text=Foto+4" alt="Foto 4" data-src="https://via.placeholder.com/960x540?text=Foto+4" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-3 mb-4">
                    <span class="badge badge-category mb-2">Mobil</span>
                    <h2 class="ad-title">Toyota Avanza 2020 Tangan Pertama Mulus</h2>
                    <div class="d-flex flex-wrap gap-3 meta mb-3">
                        <span><i class="fas fa-map-marker-alt"></i> Jakarta Selatan</span>
                        <span><i class="fas fa-clock"></i> Diposting 2 hari yang lalu</span>
                        <span><i class="fas fa-eye"></i> 1.234 kali dilihat</span>
                        <span><i class="fas fa-hashtag"></i> ID Iklan: 12345</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                        <div class="price">Rp 180.000.000</div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary"><i class="far fa-heart"></i> Simpan</button>
                            <button class="btn btn-outline-secondary"><i class="fas fa-share-alt"></i> Bagikan</button>
                            <button class="btn btn-outline-secondary"><i class="fas fa-flag"></i> Laporkan</button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="section-title">Detail</div>
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Kategori</span><span>Mobil</span></li>
                                    <li class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Tahun</span><span>2020</span></li>
                                    <li class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Transmisi</span><span>Automatic</span></li>
                                    <li class="d-flex justify-content-between py-2"><span class="text-muted">Kondisi</span><span>Bekas</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="section-title">Lokasi</div>
                                <p class="mb-1">Kebayoran Baru, Jakarta Selatan</p>
                                <p class="text-muted mb-0">DKI Jakarta, Indonesia</p>
                                <div class="ratio ratio-16x9 mt-2">
                                    <iframe src="https://maps.google.com/maps?q=jakarta&t=&z=13&ie=UTF8&iwloc=&output=embed" style="border:0;border-radius:8px;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" />

                    <div class="section-title">Deskripsi</div>
                    <p class="mb-0">
                        Dijual mobil Toyota Avanza 2020, kondisi sangat terawat, servis rutin, tangan pertama.
                        Interior dan eksterior mulus, siap pakai. Nego halus di tempat.
                    </p>
                </div>
            </div>

            <!-- Right: Seller + Actions -->
            <div class="col-lg-4">
                <div class="card p-3 mb-4 seller-card">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar"><i class="fas fa-user"></i></div>
                        <div>
                            <div class="fw-bold">Budi Santoso</div>
                            <div class="text-muted small">Bergabung sejak Jan 2023</div>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-cta"><i class="fas fa-phone"></i> Tampilkan Nomor</a>
                        <a href="#" class="btn btn-outline-secondary"><i class="fas fa-comment-dots"></i> Chat Penjual</a>
                    </div>
                </div>

                <div class="card p-3 mb-4">
                    <div class="section-title">Tips Keamanan</div>
                    <ul class="safe-tips text-muted mb-0">
                        <li>Temui penjual di tempat umum yang ramai.</li>
                        <li>Periksa barang sebelum membeli.</li>
                        <li>Hindari pembayaran di muka atau transfer tanpa jaminan.</li>
                        <li>Gunakan fitur chat untuk komunikasi awal.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Similar Ads -->
        <section class="mt-4">
            <h3 class="section-title">Iklan Serupa</h3>
            <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <div class="ad-card">
                        <div class="ad-thumb">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                        <div class="p-2">
                            <div class="fw-bold">Avanza 2019</div>
                            <div class="text-success fw-bold">Rp 165.000.000</div>
                            <div class="text-muted small"><i class="fas fa-map-marker-alt"></i> Depok</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="ad-card">
                        <div class="ad-thumb">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                        <div class="p-2">
                            <div class="fw-bold">Xenia 2020</div>
                            <div class="text-success fw-bold">Rp 175.000.000</div>
                            <div class="text-muted small"><i class="fas fa-map-marker-alt"></i> Bekasi</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="ad-card">
                        <div class="ad-thumb">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                        <div class="p-2">
                            <div class="fw-bold">Avanza 2021</div>
                            <div class="text-success fw-bold">Rp 195.000.000</div>
                            <div class="text-muted small"><i class="fas fa-map-marker-alt"></i> Tangerang</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="ad-card">
                        <div class="ad-thumb">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                        <div class="p-2">
                            <div class="fw-bold">Brio 2020</div>
                            <div class="text-success fw-bold">Rp 165.000.000</div>
                            <div class="text-muted small"><i class="fas fa-map-marker-alt"></i> Jakarta Timur</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
        // Gallery switcher
        document.querySelectorAll('.thumb').forEach(function (img) {
            img.addEventListener('click', function () {
                const target = this.getAttribute('data-src');
                const main = document.getElementById('mainImage');
                if (target && main) main.src = target;
            });
        });

        // Click on similar ad to go to detail (placeholder)
        document.querySelectorAll('.ad-card').forEach(function (card) {
            card.addEventListener('click', function () {
                window.location.href = 'detail.php?id=2';
            });
        });
    </script>
</body>
</html>
