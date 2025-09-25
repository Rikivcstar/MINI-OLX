<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php';
$pdo = db();

try {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT ads.*, 
                          (SELECT image_path FROM ad_images WHERE ad_id=ads.id LIMIT 1) AS image_path, 
                          categories.name AS category_name 
                          FROM ads 
                          JOIN categories ON ads.category_id=categories.id 
                          WHERE ads.user_id=:user_id 
                          ORDER BY ads.created_at DESC");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iklan Saya - KF OLX</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .ad-thumb {
            width: 100%;
            height: 180px;
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

    <header class="bg-teal-800 text-white py-8 mb-8" data-aos="fade-up" data-aos-duration="1000">
        <div class="container mx-auto px-4">
            <h1 class="text-2xl font-bold">Iklan Saya</h1>
            <p class="text-gray-300">Kelola iklan yang Anda pasang</p>
        </div>
    </header>

    <main class="container mx-auto px-4 mb-10">
        <?php if (empty($ads)): ?>
            <div class="text-center py-10" data-aos="fade-up" data-aos-duration="1000">
                <div class="text-6xl text-gray-400 mb-4"><i class="far fa-folder-open"></i></div>
                <h2 class="text-xl font-semibold text-gray-700">Belum ada iklan</h2>
                <p class="text-gray-500 mb-6">Pasang iklan pertama Anda sekarang.</p>
                <a class="inline-block bg-yellow-400 hover:bg-yellow-500 text-teal-800 font-bold py-3 px-6 rounded-lg shadow transition-colors" href="post-add.php">
                    <i class="fas fa-plus"></i> Pasang Iklan
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($ads as $row): ?>
                    <div class="ad-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-transform hover:-translate-y-1 duration-200" data-aos="fade-up" data-aos-duration="800">
                        <?php $img = $row['image_path'] ? 'uploads/' . $row['image_path'] : 'assets/images/noimage.png'; ?>
                        <a href="detail.php?id=<?php echo (int)$row['id']; ?>" class="block">
                            <img class="ad-thumb object-cover" src="<?php echo $img; ?>" alt="Gambar Iklan">
                            <div class="p-4">
                                <h3 class="ad-title text-base font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <div class="ad-price text-xl font-bold text-teal-800 mt-1">Rp <?php echo number_format((float)$row['price'], 0, ',', '.'); ?></div>
                                <div class="ad-location text-gray-500 text-sm mt-1"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['location']); ?></div>
                            </div>
                        </a>
                        <div class="flex gap-2 p-4 pt-0">
                            <a href="edit_ad.php?id=<?php echo (int)$row['id']; ?>" class="w-full text-center bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_ad.php?id=<?php echo (int)$row['id']; ?>" class="w-full text-center bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors" onclick="return confirm('Yakin ingin menghapus iklan ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-teal-800 text-gray-300 py-12 mt-12" data-aos="fade-up">
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
            once: true,
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