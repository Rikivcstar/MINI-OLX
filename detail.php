<?php

// WAJIB: Mulai sesi untuk mengakses $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


include 'config.php';

$pdo = db();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<script>alert('Iklan tidak ditemukan!');window.location='index.php';</script>";
    exit;
}

try {
    // Ambil data iklan menggunakan PDO
    $sql = "SELECT ads.*, categories.name AS category_name, users.name AS user_name, users.whatsapp
            FROM ads
            JOIN categories ON ads.category_id = categories.id
            JOIN users ON ads.user_id = users.id
            WHERE ads.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad) {
        echo "<script>alert('Iklan tidak ditemukan!');window.location='index.php';</script>";
        exit;
    }

    // Ambil gambar iklan menggunakan PDO
    $img_stmt = $pdo->prepare("SELECT image_path FROM ad_images WHERE ad_id = :ad_id LIMIT 1");
    $img_stmt->bindParam(':ad_id', $id, PDO::PARAM_INT);
    $img_stmt->execute();
    $img_row = $img_stmt->fetch(PDO::FETCH_ASSOC);
    $image_path = $img_row ? 'uploads/' . $img_row['image_path'] : 'assets/images/noimage.png';
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
// Siapkan data pembeli yang aman untuk Midtrans
$buyer_name = isset($_SESSION['user_name']) && $_SESSION['user_name'] !== '' ? $_SESSION['user_name'] : 'Customer';
$buyer_email = isset($_SESSION['user_email']) ? (string)$_SESSION['user_email'] : '';
if (!filter_var($buyer_email, FILTER_VALIDATE_EMAIL)) {
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $buyer_email = 'user' . ($uid ?: 'guest') . '@example.test'; // domain khusus testing
}
$buyer_phone = isset($_SESSION['user_phone']) ? preg_replace('/[^0-9+]/', '', (string)$_SESSION['user_phone']) : '';
if ($buyer_phone === '' || strlen(preg_replace('/\D/','', $buyer_phone)) < 8) {
    $buyer_phone = '081234567890'; // fallback
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($ad['title']); ?> - KF OLX</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .ad-image {
            width: 100%;
            height: 550px;
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
            <h1 class="text-xl font-bold">Detail Iklan <span class="text-teal-400">>></span></h1>
        </div>
    </header>

    <main class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="w-full lg:w-3/4">
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6" data-aos="fade-right" data-aos-delay="200">
                    <img class="ad-image object-cover" src="<?php echo e($image_path); ?>" alt="Gambar Iklan">
                </div>

                <div class="bg-white rounded-xl shadow-md p-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo e($ad['title']); ?></h2>
                        <div class="text-3xl font-extrabold text-teal-800">Rp <?php echo number_format((float)$ad['price'], 0, ',', '.'); ?></div>
                    </div>
                    <div class="flex items-center text-gray-500 text-sm gap-4 mb-4">
                        <div class="flex items-center gap-2"><i class="fas fa-map-marker-alt"></i> <?php echo e($ad['location']); ?></div>
                        <div class="flex items-center gap-2"><i class="fas fa-tag"></i> <?php echo e($ad['category_name']); ?></div>
                    </div>
                    <hr class="border-gray-200 my-4" />
                    <div>
                        <h3 class="text-lg font-bold mb-2">Deskripsi</h3>
                        <p class="text-gray-600 leading-relaxed"><?php echo nl2br(e($ad['description'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/4 space-y-6">
                <div class="bg-white rounded-xl shadow-md p-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 bg-teal-100 rounded-full flex items-center justify-center text-teal-800 text-2xl">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="font-bold text-lg"><?php echo e($ad['user_name']); ?></div>
                            <div class="text-gray-500 text-sm">Penjual</div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] !== (int)$ad['user_id']): ?>
                            <?php $wa = !empty($ad['whatsapp']) ? '62' . ltrim(preg_replace('/[^0-9]/', '', (string)$ad['whatsapp']), '0') : null; ?>
                            <?php if ($wa): ?>
                                <a target="_blank" class="w-full block text-center bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors" href="https://wa.me/<?php echo e($wa); ?>?text=<?php echo urlencode('Halo, saya tertarik dengan iklan Anda: ' . $ad['title']); ?>">
                                    <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
                                </a>
                            <?php else: ?>
                                <button class="w-full block text-center bg-gray-300 text-gray-600 font-semibold py-3 px-4 rounded-lg cursor-not-allowed" disabled>
                                    <i class="fab fa-whatsapp"></i> WhatsApp tidak tersedia
                                </button>
                            <?php endif; ?>
                            <button class="w-full mt-3 block text-center bg-yellow-400 hover:bg-yellow-500 text-teal-800 font-semibold py-3 px-4 rounded-lg transition-colors" id="pay-button">
                                <i class="fas fa-shopping-cart"></i> Beli Sekarang
                            </button>
                        <?php else: ?>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <a href="login.php" class="w-full block text-center bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-sign-in-alt"></i> Masuk untuk membeli
                                </a>
                            <?php else: ?>
                                <button class="w-full block text-center bg-gray-300 text-gray-600 font-semibold py-3 px-4 rounded-lg cursor-not-allowed" disabled>
                                    <i class="fas fa-info-circle"></i> Anda tidak dapat membeli iklan Anda sendiri
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6" data-aos="fade-left" data-aos-delay="400">
                    <h3 class="text-lg font-bold mb-3">Tips Keamanan</h3>
                    <ul class="text-gray-500 text-sm list-disc list-inside space-y-2">
                        <li>Temui penjual di tempat umum yang ramai.</li>
                        <li>Periksa barang sebelum membeli.</li>
                        <li>Hindari pembayaran di muka tanpa jaminan.</li>
                        <li>Gunakan fitur chat untuk komunikasi awal.</li>
                    </ul>
                </div>
            </div>
        </div>
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
    <!-- Midtrans Payment Gateway -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-JSewxPz5Dun-I2pj"></script>
    <script>
        AOS.init();
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Midtrans Payment Handler
        const payBtnEl = document.getElementById('pay-button');
        if (payBtnEl) payBtnEl.addEventListener('click', function() {
            // Show loading state
            const payButton = document.getElementById('pay-button');
            const originalText = payButton.innerHTML;
            payButton.disabled = true;
            payButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            
            
            // Prepare transaction data
            const transactionDetails = {
                transaction_details: {
                    order_id: 'ORDER-' + Math.round((new Date()).getTime() / 1000) + '-' + <?php echo $id; ?>,
                    gross_amount: <?php echo (float)$ad['price']; ?>
                },
                item_details: [{
                    id: <?php echo $id; ?>,
                    price: <?php echo (float)$ad['price']; ?>,
                    quantity: 1,
                    name: '<?php echo addslashes($ad['title']); ?>'
                }],
                customer_details: {
                    first_name: '<?php echo addslashes($buyer_name); ?>',
                    email: '<?php echo addslashes($buyer_email); ?>',
                    phone: '<?php echo addslashes($buyer_phone); ?>'
                }
            };

            // Send request to your payment processing endpoint
            fetch('process-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(transactionDetails)
            })
            .then(async res => {
                let payload;
                try { payload = await res.json(); } catch(e) { payload = null; }
                if (!res.ok) {
                    const msg = payload && payload.message ? payload.message : ('HTTP ' + res.status);
                    throw new Error(msg);
                }
                return payload;
            })
            .then(response => {
                if(response.token) {
                    // Open payment popup
                    window.snap.pay(response.token, {
                        onSuccess: function(result) {
                            alert('Pembayaran berhasil!');
                            const oid = (result && result.order_id) ? result.order_id : (response.order_id || '');
                            window.location.href = 'payment-success.php' + (oid ? ('?order_id=' + encodeURIComponent(oid)) : '');
                        },
                        onPending: function(result) {
                            alert('Menunggu pembayaran Anda!');
                            const oid = (result && result.order_id) ? result.order_id : (response.order_id || '');
                            window.location.href = 'payment-pending.php' + (oid ? ('?order_id=' + encodeURIComponent(oid)) : '');
                        },
                        onError: function(result) {
                            alert('Pembayaran gagal!');
                            console.log(result);
                        },
                        onClose: function() {
                            alert('Anda menutup popup pembayaran tanpa menyelesaikan pembayaran');
                        }
                    });
                } else {
                    throw new Error('Token pembayaran tidak valid');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses pembayaran: ' + (error && error.message ? error.message : 'Tidak diketahui'));
            })
            .finally(() => {
                // Reset button state
                payButton.disabled = false;
                payButton.innerHTML = originalText;
            });
        });
    </script>
</body>

</html>