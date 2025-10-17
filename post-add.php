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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .is-invalid {
            border-color: #dc3545;
        }
        .is-invalid + .invalid-feedback {
            display: block;
        }
        .img-preview { 
            max-height: 200px;
            border: 1px dashed #e6e8ea;
            border-radius: 8px;
            padding: 6px;
            background: #fafafa;
            display: none;
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

    <header class="bg-teal-800 text-white py-12 mb-8" data-aos="fade-up" data-aos-duration="1000">
        <div class="container mx-auto px-4">
            <h1 class="text-2xl font-bold">Pasang Iklan Baru</h1>
            <p class="text-gray-300">Isi detail iklan Anda dengan lengkap agar mudah ditemukan pembeli</p>
        </div>
    </header>

    <main class="container mx-auto px-4">
        <div class="flex justify-center">
            <div class="w-full lg:w-3/4">
                <div class="bg-white rounded-xl shadow-md p-6 lg:p-8" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-lg bg-teal-100 flex items-center justify-center text-teal-800 text-xl mr-3">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h2 class="text-xl font-bold">Detail Iklan</h2>
                    </div>

                    <form id="postAdForm" action="post_process.php" method="POST" enctype="multipart/form-data" novalidate>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-full" data-aos="fade-right">
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Iklan</label>
                                <input type="text" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400" id="title" name="title" placeholder="Contoh: iPhone 12 Pro Max Mulus" required>
                                <div class="invalid-feedback text-red-500 text-sm mt-1">Judul iklan wajib diisi.</div>
                            </div>

                            <div data-aos="fade-right" data-aos-delay="100">
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                <select class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400" id="category_id" name="category_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo (int)$cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback text-red-500 text-sm mt-1">Silakan pilih kategori.</div>
                            </div>

                            <div data-aos="fade-left" data-aos-delay="100">
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                                <input type="number" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400" id="price" name="price" placeholder="0" min="0" oninput="normalizePrice(this)" required>
                                <div class="invalid-feedback text-red-500 text-sm mt-1">Harga wajib diisi dan tidak boleh negatif.</div>
                            </div>

                            <div data-aos="fade-right" data-aos-delay="200">
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                                <input type="text" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400" id="location" name="location" placeholder="Kota/Kabupaten" required>
                                <div class="invalid-feedback text-red-500 text-sm mt-1">Lokasi wajib diisi.</div>
                            </div>

                            <div class="col-span-full" data-aos="fade-up" data-aos-delay="300">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                <textarea class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400" id="description" name="description" rows="5" placeholder="Jelaskan kondisi, kelengkapan, alasan jual, dll." required></textarea>
                                <div class="invalid-feedback text-red-500 text-sm mt-1">Deskripsi wajib diisi.</div>
                            </div>

                            <div data-aos="fade-right" data-aos-delay="400">
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
                                <input class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400" type="file" id="image" name="image" accept="image/*" required>
                                <p class="text-xs text-gray-500 mt-1">Format: JPG/PNG. Maks 5MB.</p>
                                <div class="invalid-feedback text-red-500 text-sm mt-1">Silakan unggah setidaknya 1 foto.</div>
                            </div>
                            <div data-aos="fade-left" data-aos-delay="400">
                                <img id="imagePreview" class="img-preview w-full mt-2" alt="Preview Gambar" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-6" data-aos="fade-up" data-aos-delay="500">
                            <a href="index.php" class="btn bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors">Batal</a>
                            <button type="submit" class="btn bg-teal-400 hover:bg-teal-500 text-teal-800 font-bold py-3 px-6 rounded-lg transition-colors">Pasang Iklan</button>
                        </div>
                    </form>
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
                <p class="mb-2 md:mb-0">&copy; 2024 KF OLX. Semua hak dilindungi.</p>
                <p>Made with <i class="fas fa-heart text-red-500"></i> in Indonesia</p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // Cek jika ada parameter success di URL
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

        if (success === '1') {
            Swal.fire({
                    title: 'Berhasil Daftar!',
                    text: 'Iklan  berhasil di Tambahkan. Silakan login untuk melanjutkan.',
                    icon: 'success',
                    confirmButtonText: 'Ke Halaman Login',
                    confirmButtonColor: '#67706fff'
                }).then(() => {
                    // Setelah klik OK, baru diarahkan ke halaman login
                    window.location.href = 'login.php';
                    // Hapus parameter success agar tidak muncul lagi saat reload
                    window.history.replaceState({}, document.title, "register.php");

                });

        }
    </script>


    <script>
        AOS.init({
            once: true,
        });

        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
        
        // Disable double submit & basic client-side validation
        document.addEventListener('DOMContentLoaded', function(){
            const form = document.getElementById('postAdForm');
            if (!form) return;

            form.addEventListener('submit', function(e){
                let valid = true;
                const fields = ['title', 'category_id', 'price', 'location', 'description', 'image'];

                fields.forEach(id => {
                    const el = document.getElementById(id);
                    if (!el) return;

                    let hasValue = true;
                    if (el.type === 'file') {
                        if (!el.files || el.files.length === 0) {
                            hasValue = false;
                        }
                    } else if (!el.value.trim()) {
                        hasValue = false;
                    }
                    
                    if (!hasValue) {
                        el.classList.add('is-invalid');
                        valid = false;
                    } else {
                        el.classList.remove('is-invalid');
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    return;
                }

                // Prevent double submission
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Mengunggah...';
                }
            });

            // Normalize price input
            window.normalizePrice = function(el) {
                if (el.value < 0) {
                    el.value = 0;
                }
            };
            
            // Image preview
            const input = document.getElementById('image');
            const preview = document.getElementById('imagePreview');
            if (input && preview) {
                input.addEventListener('change', function(){
                    const file = input.files && input.files[0];
                    if (!file) {
                        preview.src = '';
                        preview.style.display = 'none';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
</body>
</html>