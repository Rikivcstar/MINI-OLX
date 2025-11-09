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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .ad-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans text-gray-800">

    <nav class="bg-white shadow-md py-4" data-aos="fade-down" data-aos-duration="1000">
        <div class="container mx-auto flex items-center justify-between px-4">
            <a class="text-3xl font-bold text-teal-800 flex items-center gap-2" href="index.php">
                <i class="fas fa-store"></i> KF OLX
            </a>
            <div class="hidden md:flex items-center space-x-6">
                <a class="text-teal-800 hover:text-teal-600 font-medium transition-colors" href="index.php">
                    <i class="fas fa-house"></i> Beranda
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="text-teal-800 hover:text-teal-600 font-medium transition-colors" href="my_ads.php">
                        <i class="fas fa-eye"></i> Iklan Saya
                    </a>
                    <a class="text-teal-800 hover:text-teal-600 font-medium transition-colors" href="post-add.php">
                        <i class="fas fa-plus"></i> Pasang Iklan
                    </a>
                    <a class="text-teal-800 hover:text-teal-600 font-medium transition-colors" href="profile.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <span class="text-gray-600 font-medium">Halo <?php echo e($_SESSION['user_name'] ?? 'Pengguna'); ?> 🥷</span>
                    <a class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded transition-colors" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a class="text-teal-800 hover:text-teal-600 font-medium transition-colors" href="login.php">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </a>
                    <a class="text-teal-800 hover:text-teal-600 font-medium transition-colors" href="register.php">
                        <i class="fas fa-user-plus"></i> Daftar
                    </a>
                    <a class="bg-yellow-400 hover:bg-yellow-500 text-teal-800 font-bold py-2 px-4 rounded shadow transition-colors" href="post-add.php">
                        <i class="fas fa-plus"></i> Pasang Iklan
                    </a>
                <?php endif; ?>
            </div>
            <button class="md:hidden text-gray-600" id="mobile-menu-button">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden px-4 pt-2 pb-4 space-y-1 bg-white shadow-lg">
             <a class="block text-teal-800 hover:text-teal-600 font-medium py-2" href="index.php">Beranda</a>
             <?php if (isset($_SESSION['user_id'])): ?>
                <a class="block text-teal-800 hover:text-teal-600 font-medium py-2" href="my_ads.php">Iklan Saya</a>
                <a class="block text-teal-800 hover:text-teal-600 font-medium py-2" href="post-add.php">Pasang Iklan</a>
                <a class="block text-teal-800 hover:text-teal-600 font-medium py-2" href="profile.php">Profile</a>
                <span class="block text-gray-600 font-medium py-2">Halo <?php echo e($_SESSION['user_name'] ?? 'Pengguna'); ?> 🥷</span>
                <a class="block bg-red-500 hover:bg-red-600 text-white font-semibold text-center py-2 rounded" href="logout.php">Logout</a>
             <?php else: ?>
                <a class="block text-teal-800 hover:text-teal-600 font-medium py-2" href="login.php">Masuk</a>
                <a class="block text-teal-800 hover:text-teal-600 font-medium py-2" href="register.php">Daftar</a>
                <a class="block bg-yellow-400 hover:bg-yellow-500 text-teal-800 font-bold text-center py-2 rounded" href="post-add.php">Pasang Iklan</a>
             <?php endif; ?>
        </div>
    </nav>
    
    <section style="background-image: url(assets/banner.png);" class=" text-white py-16 mb-12" data-aos="fade-up" data-aos-duration="1000">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/2" data-aos="fade-right" data-aos-delay="300">
                    <h1 class="text-4xl lg:text-5xl font-extrabold mb-4">Jual Beli Online Terpercaya di Indonesia</h1>
                    <p class="text-lg mb-6 text-gray-200">Temukan ribuan produk berkualitas. Jual barang bekas atau baru dengan mudah dan aman.</p>
                </div>
                <div class="w-full lg:w-1/2 mt-8 lg:mt-0" data-aos="fade-left" data-aos-delay="300">
                    <div class="bg-white bg-opacity-10 rounded-xl p-8 shadow-xl">
                        <h3 class="text-white text-2xl font-bold mb-4">Cari Produk</h3>
                        <form action="index.php" method="GET">
                            <div class="space-y-4">
                                <input type="text" class="w-full p-3 rounded-lg border-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-400 text-gray-800" name="title" placeholder="Judul barang..." value="<?php echo e($_GET['title'] ?? ''); ?>">
                                <select class="w-full p-3 rounded-lg border-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-400 text-gray-800" name="category_id">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo (int)$cat['id']; ?>" <?php echo (isset($_GET['category_id']) && (int)$_GET['category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo e($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="w-full p-3 rounded-lg border-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-400 text-gray-800" name="location">
                                    <option value="">Semua Lokasi</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?php echo e($loc); ?>" <?php echo (isset($_GET['location']) && $_GET['location'] === $loc) ? 'selected' : ''; ?>>
                                            <?php echo e($loc); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="w-full bg-teal-400 hover:bg-teal-500 text-teal-800 font-bold py-3 px-4 rounded-lg shadow transition-colors">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container mx-auto px-4 mb-12" data-aos="fade-up">
        <h2 class="text-3xl font-bold text-center mb-8" data-aos="fade-up">Kategori Populer</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <?php foreach ($categories as $cat): ?>
                <a href="index.php?category_id=<?php echo (int)$cat['id']; ?>" class="block text-center no-underline" data-aos="zoom-in-up" data-aos-delay="100">
                    <div class="bg-white rounded-lg p-6 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:border-teal-400 border border-transparent">
                        <div class="text-4xl text-teal-500 mb-2">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="font-semibold text-gray-800 mb-1"><?php echo e($cat['name']); ?></div>
                        <small class="text-gray-500">Jelajahi</small>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="container mx-auto px-4 mb-12" data-aos="fade-up" data-aos-delay="200">
        <h2 class="text-3xl font-bold mb-6 text-center " data-aos="fade-up">Iklan Terbaru</h2>
        <?php
        $info = [];
        if (isset($_GET['title']) && $_GET['title'] != '') $info[] = 'Judul: <b>' . e($_GET['title']) . '</b>';
        if (isset($_GET['location']) && $_GET['location'] != '') $info[] = 'Lokasi: <b>' . e($_GET['location']) . '</b>';
        if (isset($_GET['category_id']) && $_GET['category_id'] != '') $info[] = 'Kategori: <b>' . e($cat_name) . '</b>';
        if ($info) echo '<p class="text-gray-600 mb-4" data-aos="fade-right">' . implode(', ', $info) . '</p>';
        ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 my-20">
            <?php foreach ($ads as $row): ?>
                <a class="block no-underline" href="detail.php?id=<?php echo (int)$row['id']; ?>" data-aos="fade-up">
                    <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                        <?php $img = $row['image_path'] ? 'uploads/' . $row['image_path'] : 'assets/images/noimage.png'; ?>
                        <img class="ad-image bg-gray-200" src="<?php echo e($img); ?>" alt="Gambar Iklan" style="height: 300px; background-size: cover; background-repeat: no-repeat;">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1"><?php echo e($row['title']); ?></h3>
                            <div class="text-xl font-bold text-teal-800 mb-2">Rp <?php echo number_format((float)$row['price'], 0, ',', '.'); ?></div>
                            <div class="text-sm text-gray-500"><i class="fas fa-map-marker-alt"></i> <?php echo e($row['location']); ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <footer class="bg-teal-800 text-gray-300 py-12" data-aos="fade-up">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
                <div>
                    <h5 class="text-xl font-bold text-white mb-4"><i class="fas fa-store"></i> KF OLX</h5>
                    <p class="text-sm">Platform jual beli online terpercaya di Indonesia. Jual dan beli dengan mudah, aman, dan terpercaya.</p>
                </div>
                <div>
                    <h6 class="text-lg font-bold text-white mb-4">Kategori</h6>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Mobil</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Motor</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Handphone</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Elektronik</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Properti</a></li>
                    </ul>
                </div>
                <div>
                    <h6 class="text-lg font-bold text-white mb-4">Bantuan</h6>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Cara Jual</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Cara Beli</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Tips Aman</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">FAQ</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Kontak</a></li>
                    </ul>
                </div>
                <div>
                    <h6 class="text-lg font-bold text-white mb-4">Perusahaan</h6>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Karir</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Blog</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Press</a></li>
                    </ul>
                </div>
                <div>
                    <h6 class="text-lg font-bold text-white mb-4">Legal</h6>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Panduan Komunitas</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-8 border-gray-700">
            <div class="flex flex-col md:flex-row items-center justify-between text-sm text-gray-500">
                <p class="mb-2 md:mb-0">&copy; 2025 KF OLX. Semua hak dilindungi.</p>
                <p>Made with <i class="fas fa-heart text-red-500"></i> in Indonesia</p>
            </div>
        </div>
    </footer>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true, // Animasi hanya berjalan sekali saat pertama kali elemen terlihat
        });

        // Toggle mobile menu
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>

</html>