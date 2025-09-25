<?php
require_once __DIR__ . '/config.php';

// Require login
$userId = current_user_id();
if ($userId === null) {
    redirect('login.php');
}

$pdo = db();
$errors = [];
$success = '';

// Load current user
$stmt = $pdo->prepare('SELECT id, name, email, COALESCE(whatsapp, "") AS whatsapp, created_at FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    // Invalid session
    session_destroy();
    redirect('login.php');
}

// Defaults for form fields
$name = $user['name'];
$email = $user['email'];
$whatsapp = $user['whatsapp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $new_password_confirm = $_POST['new_password_confirm'] ?? '';

    // Validations
    if ($name === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }
    if ($whatsapp !== '' && !preg_match('/^(?:\+62|62|0)[0-9]{9,13}$/', $whatsapp)) {
        $errors[] = 'No. WhatsApp tidak valid. Gunakan format +628xxxx atau 08xxxx.';
    }

    // Email uniqueness (exclude current)
    if (empty($errors)) {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $st->execute([$email, $userId]);
        if ($st->fetch()) {
            $errors[] = 'Email sudah digunakan oleh pengguna lain.';
        }
    }

    $changePassword = false;
    if ($current_password !== '' || $new_password !== '' || $new_password_confirm !== '') {
        $changePassword = true;
        // Need all three fields
        if ($current_password === '' || $new_password === '' || $new_password_confirm === '') {
            $errors[] = 'Untuk mengganti kata sandi, isi semua kolom kata sandi.';
        }
        if (strlen($new_password) < 8) {
            $errors[] = 'Kata sandi baru minimal 8 karakter.';
        }
        if ($new_password !== $new_password_confirm) {
            $errors[] = 'Konfirmasi kata sandi baru tidak cocok.';
        }
        // Verify current password
        if (empty($errors)) {
            $pwq = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
            $pwq->execute([$userId]);
            $pwRow = $pwq->fetch(PDO::FETCH_ASSOC);
            if (!$pwRow || !password_verify($current_password, $pwRow['password'])) {
                $errors[] = 'Kata sandi saat ini salah.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update basic info
            $up = $pdo->prepare('UPDATE users SET name = ?, email = ?, whatsapp = ? WHERE id = ?');
            $up->execute([$name, $email, $whatsapp, $userId]);

            // Update password if requested
            if ($changePassword) {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $upp = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $upp->execute([$hash, $userId]);
            }

            $pdo->commit();

            // Refresh session display name
            $_SESSION['user_name'] = $name;

            $success = 'Profil berhasil diperbarui' . ($changePassword ? ' dan kata sandi diganti.' : '.');
        } catch (Throwable $t) {
            $pdo->rollBack();
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
    <title>Profil Saya - KF OLX</title>
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
            <h1 class="text-2xl font-bold">Profil Saya</h1>
            <p class="text-gray-300">Kelola informasi akun Anda</p>
        </div>
    </header>

    <main class="container mx-auto px-4 mb-10">
        <div class="flex justify-center">
            <div class="w-full md:w-2/3 lg:w-1/2">
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert" data-aos="fade-down">
                        <div class="font-semibold mb-1">Perbarui profil gagal:</div>
                        <ul class="list-disc list-inside mb-0">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success !== ''): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert" data-aos="fade-down">
                        <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200" data-aos="fade-up" data-aos-duration="1000">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center text-teal-800 text-3xl">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="font-bold text-lg"><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="text-gray-500 text-sm">Bergabung sejak <?php echo htmlspecialchars(date('d M Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>

                    <form action="profile.php" method="post" novalidate class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="name" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required />
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required />
                            </div>
                            <div>
                                <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fab fa-whatsapp"></i>
                                    </div>
                                    <input type="text" class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg shadow-sm focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="whatsapp" name="whatsapp" placeholder="08xxxxxxxxxx atau +628xxxxxxxx" value="<?php echo htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <h2 class="text-base font-semibold text-gray-900 mb-4">Ganti Kata Sandi (opsional)</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Saat Ini</label>
                                    <input type="password" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="current_password" name="current_password" placeholder="••••••••" />
                                </div>
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                                    <input type="password" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="new_password" name="new_password" placeholder="Minimal 8 karakter" />
                                </div>
                                <div>
                                    <label for="new_password_confirm" class="block text-sm font-medium text-gray-700 mb-1">Ulangi Kata Sandi Baru</label>
                                    <input type="password" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="new_password_confirm" name="new_password_confirm" placeholder="Ulangi kata sandi" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                            <a href="index.php" class="bg-white border border-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-full shadow hover:bg-gray-50 transition-colors duration-200">Batal</a>
                            <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 px-6 rounded-full shadow transition-colors duration-200">Simpan Perubahan</button>
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

        // Client-side validation helpers (visual only)
        (function(){
            const form = document.querySelector('form');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const whatsappInput = document.getElementById('whatsapp');
            const newPassword = document.getElementById('new_password');
            const newPasswordConfirm = document.getElementById('new_password_confirm');

            function applyValidationClass(el, isValid) {
                if (el) {
                    el.classList.toggle('border-red-500', !isValid);
                    el.classList.toggle('focus:border-red-500', !isValid);
                    el.classList.toggle('focus:ring-red-500', !isValid);
                    el.classList.toggle('border-gray-200', isValid);
                    el.classList.toggle('focus:border-teal-400', isValid);
                    el.classList.toggle('focus:ring-teal-200', isValid);
                }
            }

            function validateWhatsapp(){
                const v = whatsappInput.value.trim();
                if (!v) { applyValidationClass(whatsappInput, true); return true; }
                const ok = /^(?:\+62|62|0)[0-9]{9,13}$/.test(v);
                applyValidationClass(whatsappInput, ok);
                return ok;
            }
            whatsappInput.addEventListener('input', validateWhatsapp);

            function validatePwdMatch(){
                const isChanging = newPassword.value.trim() !== '' || newPasswordConfirm.value.trim() !== '';
                let ok = true;
                if (isChanging) {
                    const pwdLengthOk = newPassword.value.length >= 8;
                    const pwdMatch = newPassword.value === newPasswordConfirm.value;
                    ok = pwdLengthOk && pwdMatch;
                }
                applyValidationClass(newPassword, ok);
                applyValidationClass(newPasswordConfirm, ok);
                return ok;
            }
            newPassword.addEventListener('input', validatePwdMatch);
            newPasswordConfirm.addEventListener('input', validatePwdMatch);

            form.addEventListener('submit', function(e){
                let valid = true;
                if (!nameInput.value.trim()) { applyValidationClass(nameInput, false); valid = false; } else { applyValidationClass(nameInput, true); }
                if (!emailInput.value.trim() || !emailInput.checkValidity()) { applyValidationClass(emailInput, false); valid = false; } else { applyValidationClass(emailInput, true); }
                if (!validateWhatsapp()) valid = false;
                if (!validatePwdMatch()) valid = false;
                if (!valid) e.preventDefault();
            });
        })();
    </script>
</body>
</html>