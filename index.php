<?php
include 'config.php';

// Session sudah dimulai di config.php jika belum aktif
$pdo = db();

try {
    // Ambil lokasi unik dari ads
    $locations = [];
    $loc_stmt = $pdo->query("SELECT DISTINCT location FROM ads WHERE location IS NOT NULL AND location != '' ORDER BY location ASC");
    $locations = $loc_stmt->fetchAll(PDO::FETCH_COLUMN);

    $where = [];
    $params = [];

    if (isset($_GET['title']) && $_GET['title'] != '') {
        $where[] = "ads.title LIKE :title";
        $params[':title'] = '%' . $_GET['title'] . '%';
    }
    if (isset($_GET['location']) && $_GET['location'] != '') {
        $where[] = "ads.location LIKE :location";
        $params[':location'] = '%' . $_GET['location'] . '%';
    }
    if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
        $where[] = "ads.category_id = :category_id";
        $params[':category_id'] = intval($_GET['category_id']);
    }

    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT ads.*, categories.name AS category_name, 
            (SELECT image_path FROM ad_images WHERE ad_id = ads.id LIMIT 1) AS image_path
            FROM ads 
            JOIN categories ON ads.category_id = categories.id
            $where_sql
            ORDER BY ads.created_at DESC
            LIMIT 12";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => &$val) {
        if ($key === ':category_id') {
            $stmt->bindParam($key, $val, PDO::PARAM_INT);
        } else {
            $stmt->bindParam($key, $val);
        }
    }
    $stmt->execute();
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil kategori untuk menu
    $cat_stmt = $pdo->query("SELECT * FROM categories");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil nama kategori jika ada filter
    $cat_name = '';
    if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
        $cat_id = intval($_GET['category_id']);
        $cat_stmt = $pdo->prepare("SELECT name FROM categories WHERE id = :id");
        $cat_stmt->bindParam(':id', $cat_id, PDO::PARAM_INT);
        $cat_stmt->execute();
        $cat_result = $cat_stmt->fetch(PDO::FETCH_ASSOC);
        $cat_name = $cat_result['name'] ?? '';
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KF OLX - Jual Beli Online Terpercaya</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #002f34;
            --secondary-color: #23e5db;
            --accent-color: #ffce32;
            --text-dark: #002f34;
            --text-light: #7c8a97;
            --border-color: #e6e8ea;
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: var(--text-dark); }
        .navbar-brand { font-weight: bold; font-size: 2rem; color: var(--primary-color) !important; }
        .navbar { background-color: white !important; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem 0; }
        .hero-section { background: linear-gradient(135deg, var(--primary-color) 0%, #004d56 100%); color: white; padding: 4rem 0; margin-bottom: 3rem; }
        .search-container { background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-top: 2rem; }
        .search-input { border: 2px solid var(--border-color); border-radius: 6px; padding: 12px 16px; font-size: 16px; }
        .search-input:focus { border-color: var(--secondary-color); box-shadow: 0 0 0 0.2rem rgba(35, 229, 219, 0.25); }
        .btn-search { background-color: var(--secondary-color); border: none; color: var(--primary-color); font-weight: 600; padding: 12px 24px; border-radius: 6px; }
        .btn-search:hover { background-color: #1bc5bb; color: var(--primary-color); }
        .btn-post-ad { background-color: var(--accent-color); border: none; color: var(--primary-color); font-weight: 600; padding: 10px 20px; border-radius: 6px; }
        .btn-post-ad:hover { background-color: #e6b82e; color: var(--primary-color); }
        .category-card { background: white; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; text-align: center; transition: all 0.3s ease; cursor: pointer; height: 100%; }
        .category-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-color: var(--secondary-color); }
        .category-icon { font-size: 2rem; color: var(--secondary-color); margin-bottom: 0.75rem; }
        .category-name { font-weight: 600; color: var(--text-dark); margin-bottom: 0.25rem; }
        .ad-card { background: white; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; transition: all 0.3s ease; cursor: pointer; height: 100%; }
        .ad-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .ad-image { width: 100%; height: 200px; object-fit: cover; background-color: #f8f9fa; }
        .ad-content { padding: 1rem; }
        .ad-title { font-weight: 600; color: var(--text-dark); margin-bottom: 0.5rem; font-size: 1rem; }
        .ad-price { font-size: 1.25rem; font-weight: bold; color: var(--primary-color); margin-bottom: 0.5rem; }
        .ad-location { color: var(--text-light); font-size: 0.875rem; }
        .section-title { font-size: 1.75rem; font-weight: bold; color: var(--text-dark); margin-bottom: 2rem; }
        .footer { background-color: var(--primary-color); color: white; padding: 3rem 0 1rem; margin-top: 4rem; }
        .footer-link { color: #b3c5c8; text-decoration: none; transition: color 0.3s ease; }
        .footer-link:hover { color: var(--secondary-color); }
    </style>
</head>

<body>
    <!-- Navigation -->
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><span class="nav-link">Halo, <?php echo e($_SESSION['user_name'] ?? 'Pengguna'); ?></span></li>
                        <li class="nav-item ms-2"><a class="btn btn-post-ad" href="post-add.php"><i class="fas fa-plus"></i> Pasang Iklan</a></li>
                        <li class="nav-item ms-2"><a class="nav-link" href="logout.php">Keluar</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Masuk</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php"><i class="fas fa-user-plus"></i> Daftar</a></li>
                        <li class="nav-item ms-2"><a class="btn btn-post-ad" href="post-add.php"><i class="fas fa-plus"></i> Pasang Iklan</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Search -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-5 fw-bold mb-3">Jual Beli Online Terpercaya di Indonesia</h1>
                    <p class="lead mb-4">Temukan ribuan produk berkualitas. Jual barang bekas atau baru dengan mudah dan aman.</p>
                </div>
                <div class="col-lg-6">
                    <div class="search-container">
                        <h3 class="text-dark mb-3">Cari Produk</h3>
                        <form action="index.php" method="GET">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="text" class="form-control search-input" name="title" placeholder="Judul barang..." value="<?php echo e($_GET['title'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select" name="category_id">
                                        <option value="">Semua Kategori</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo (isset($_GET['category_id']) && (int)$_GET['category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                                                <?php echo e($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-md-8">
                                    <select class="form-select" name="location">
                                        <option value="">Semua Lokasi</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?php echo e($loc); ?>" <?php echo (isset($_GET['location']) && $_GET['location'] === $loc) ? 'selected' : ''; ?>>
                                                <?php echo e($loc); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-search w-100">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="container mb-5">
        <h2 class="section-title text-center">Kategori Populer</h2>
        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-2 col-md-4 col-6">
                    <a href="index.php?category_id=<?php echo (int)$cat['id']; ?>" class="text-decoration-none text-reset">
                        <div class="category-card h-100">
                            <div class="category-icon"><i class="fas fa-tag"></i></div>
                            <div class="category-name"><?php echo e($cat['name']); ?></div>
                            <small class="text-muted">Jelajahi</small>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Ads Section -->
    <section class="container mb-5">
        <h2 class="section-title">Iklan Terbaru</h2>

        <?php
        $info = [];
        if (isset($_GET['title']) && $_GET['title'] != '') $info[] = 'Judul: <b>' . e($_GET['title']) . '</b>';
        if (isset($_GET['location']) && $_GET['location'] != '') $info[] = 'Lokasi: <b>' . e($_GET['location']) . '</b>';
        if (isset($_GET['category_id']) && $_GET['category_id'] != '') $info[] = 'Kategori: <b>' . e($cat_name) . '</b>';
        if ($info) echo '<p>' . implode(', ', $info) . '</p>';
        ?>

        <div class="row g-4">
            <?php foreach ($ads as $row): ?>
                <div class="col-lg-3 col-md-6">
                    <a class="text-decoration-none" href="detail.php?id=<?php echo (int)$row['id']; ?>">
                        <div class="ad-card">
                            <?php $img = $row['image_path'] ? 'uploads/' . $row['image_path'] : 'assets/images/noimage.png'; ?>
                            <img class="ad-image" src="<?php echo e($img); ?>" alt="Gambar Iklan">
                            <div class="ad-content">
                                <div class="ad-title"><?php echo e($row['title']); ?></div>
                                <div class="ad-price">Rp <?php echo number_format((float)$row['price'], 0, ',', '.'); ?></div>
                                <div class="ad-location"><i class="fas fa-map-marker-alt"></i> <?php echo e($row['location']); ?></div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

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
            <hr class="my-4" style="border-color: #4a5c6a;">
            <div class="row align-items-center">
                <div class="col-md-6"><p class="text-muted mb-0">&copy; 2025 KF OLX. Semua hak dilindungi.</p></div>
                <div class="col-md-6 text-md-end"><p class="text-muted mb-0">Made with <i class="fas fa-heart text-danger"></i> in Indonesia</p></div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>