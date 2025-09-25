<?php
include 'config.php';

// Wajib login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$pdo = db();

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);

try {
    // Ambil data iklan menggunakan PDO
    $stmt = $pdo->prepare("SELECT * FROM ads WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad) {
        echo "<script>alert('Iklan tidak ditemukan atau Anda tidak berhak mengedit.');window.location='my_ads.php';</script>";
        exit;
    }

    // Proses update jika form disubmit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $location = $_POST['location'];
        $description = $_POST['description'];

        // Mulai transaksi
        $pdo->beginTransaction();

        try {
            // Update data iklan
            $update_stmt = $pdo->prepare("UPDATE ads SET title = :title, category_id = :category_id, 
                                            price = :price, location = :location, description = :description 
                                            WHERE id = :id AND user_id = :user_id");
            
            $update_stmt->bindParam(':title', $title);
            $update_stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $update_stmt->bindParam(':price', $price);
            $update_stmt->bindParam(':location', $location);
            $update_stmt->bindParam(':description', $description);
            $update_stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $update_stmt->execute();

            // Jika ada upload gambar baru
            if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid().'.'.$ext;
                $upload_path = 'uploads/'.$filename;
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Hapus gambar lama (opsional)
                    $img_stmt = $pdo->prepare("SELECT image_path FROM ad_images WHERE ad_id = :ad_id LIMIT 1");
                    $img_stmt->bindParam(':ad_id', $id, PDO::PARAM_INT);
                    $img_stmt->execute();
                    $old_img = $img_stmt->fetch(PDO::FETCH_COLUMN);
                    
                    if($old_img && file_exists('uploads/'.$old_img)) {
                        unlink('uploads/'.$old_img);
                    }
                    
                    // Update gambar
                    $update_img_stmt = $pdo->prepare("UPDATE ad_images SET image_path = :image_path WHERE ad_id = :ad_id");
                    $update_img_stmt->bindParam(':image_path', $filename);
                    $update_img_stmt->bindParam(':ad_id', $id, PDO::PARAM_INT);
                    $update_img_stmt->execute();
                }
            }

            // Commit transaksi
            $pdo->commit();
            echo "<script>alert('Iklan berhasil diupdate!');window.location='my_ads.php';</script>";
            exit;

        } catch(PDOException $e) {
            // Rollback jika terjadi error
            $pdo->rollBack();
            echo "<script>alert('Gagal mengupdate iklan: ".addslashes($e->getMessage())."');</script>";
        }
    }

    // Ambil kategori
    $cats_stmt = $pdo->query("SELECT * FROM categories");
    $categories = $cats_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "<script>alert('Terjadi kesalahan: ".addslashes($e->getMessage())."');</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Iklan - KF OLX</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
            <h1 class="text-2xl font-bold">Edit Iklan</h1>
            <p class="text-gray-300">Perbarui detail iklan Anda agar lebih menarik</p>
        </div>
    </header>

    <main class="container mx-auto px-4 mb-10">
        <div class="flex justify-center">
            <div class="w-full md:w-2/3 lg:w-1/2">
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200" data-aos="fade-up" data-aos-duration="1000">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center text-teal-800 text-xl">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h2 class="text-xl font-semibold">Form Edit</h2>
                    </div>

                    <?php
                    // Ambil gambar saat ini untuk preview
                    $current_image = 'assets/images/noimage.png';
                    try {
                        $imgq = $pdo->prepare('SELECT image_path FROM ad_images WHERE ad_id = :ad_id LIMIT 1');
                        $imgq->bindParam(':ad_id', $id, PDO::PARAM_INT);
                        $imgq->execute();
                        $imgRow = $imgq->fetch(PDO::FETCH_ASSOC);
                        if ($imgRow && !empty($imgRow['image_path'])) {
                            $current_image = 'uploads/' . $imgRow['image_path'];
                        }
                    } catch (Throwable $t) { /* ignore preview errors */ }
                    ?>

                    <form action="" method="POST" enctype="multipart/form-data" id="editAdForm" novalidate class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-full">
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Iklan</label>
                                <input type="text" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="title" name="title" value="<?php echo e($ad['title']); ?>" required>
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                <div class="relative">
                                    <select class="block w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 appearance-none pr-8 transition-all duration-200" id="category_id" name="category_id" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo ((int)$ad['category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                        <i class="fas fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">Rp</div>
                                    <input type="number" class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg shadow-sm focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="price" name="price" value="<?php echo e($ad['price']); ?>" min="0" required>
                                </div>
                            </div>

                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <input type="text" class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg shadow-sm focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="location" name="location" value="<?php echo e($ad['location']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-full">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="description" name="description" rows="5" required><?php echo e($ad['description']); ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Ganti Foto Produk (opsional)</label>
                                <input class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 transition-colors duration-200" type="file" id="image" name="image" accept="image/*">
                                <p class="mt-1 text-xs text-gray-500">Format: JPG/PNG. Maks 5MB.</p>
                            </div>
                            <div class="flex justify-center">
                                <img id="imagePreview" class="w-full max-h-48 object-contain rounded-lg shadow-sm border border-dashed border-gray-200 p-2" src="<?php echo e($current_image); ?>" alt="Preview Gambar" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                            <a href="my_ads.php" class="bg-white border border-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-full shadow hover:bg-gray-50 transition-colors duration-200">Batal</a>
                            <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 px-6 rounded-full shadow transition-colors duration-200">Update Iklan</button>
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

        // Preview gambar saat memilih file baru
        (function(){
            const input = document.getElementById('image');
            const preview = document.getElementById('imagePreview');
            if (input && preview) {
                input.addEventListener('change', function(){
                    const file = input.files && input.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = e => { preview.src = e.target.result; };
                    reader.readAsDataURL(file);
                });
            }
        })();

        // Simple client-side validation
        (function(){
            const form = document.getElementById('editAdForm');
            if (!form) return;
            form.addEventListener('submit', function(e){
                let valid = true;
                ['title','category_id','price','location','description'].forEach(id => {
                    const el = document.getElementById(id);
                    if (!el || !el.value) { 
                        el && el.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500'); 
                        el && el.classList.remove('border-gray-200', 'focus:border-teal-400', 'focus:ring-teal-200'); 
                        valid = false; 
                    } else { 
                        el.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500'); 
                        el.classList.add('border-gray-200', 'focus:border-teal-400', 'focus:ring-teal-200'); 
                    }
                });
                if (!valid) {
                    e.preventDefault();
                    window.scrollTo({top: 0, behavior: 'smooth'}); // Scroll to top on validation fail
                }
            });
        })();
    </script>
</body>
</html>