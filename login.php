<?php
require_once __DIR__ . '/config.php';

$errors = [];
$old = ['email' => ''];
$justRegistered = isset($_GET['registered']) && $_GET['registered'] === '1';
$alreadyLoggedIn = current_user_id() !== null;

// Jika sudah login, langsung arahkan ke beranda
if ($alreadyLoggedIn) {
    redirect('index.php');
}

if (!$alreadyLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $old['email'] = $email;

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }
    if ($password === '') {
        $errors[] = 'Kata sandi wajib diisi.';
    }

    if (empty($errors)) {
        try {
            $pdo = db();
            $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $errors[] = 'Email atau kata sandi salah.';
            } else {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];

                if ($remember) {
                    setcookie('remember_email', $user['email'], time() + (86400 * 30), '/');
                } else {
                    setcookie('remember_email', '', time() - 3600, '/');
                }

                redirect('index.php');
            }
        } catch (Throwable $t) {
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
    <title>Masuk - KF OLX</title>
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

                   
                    <a class="text-teal-800 hover:text-teal-600 font-medium transition-colors" href="login.php">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </a>
                    <a class="text-teal-800 hover:text-teal-600 font-medium transition-colors" href="register.php">
                        <i class="fas fa-user-plus"></i> Daftar
                    </a>
                    <a class="bg-yellow-400 hover:bg-yellow-500 text-teal-800 font-bold py-2 px-4 rounded shadow transition-colors" href="post-add.php">
                        <i class="fas fa-plus"></i> Pasang Iklan
                    </a>
            
            </div>
            <button class="md:hidden text-gray-600" id="mobile-menu-button">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden px-4 pt-2 pb-4 space-y-1 bg-white shadow-lg">
             
                <a class="block text-teal-800 hover:text-teal-600 font-medium py-2" href="login.php">Masuk</a>
                <a class="block text-teal-800 hover:text-teal-600 font-medium py-2" href="register.php">Daftar</a>
                <a class="block bg-yellow-400 hover:bg-yellow-500 text-teal-800 font-bold text-center py-2 rounded" href="post-add.php">Pasang Iklan</a>
             
        </div>
  </nav>

    <header class="bg-teal-800 text-white py-8 mb-8" data-aos="fade-up" data-aos-duration="1000">
        <div class="container mx-auto px-4">
            <h1 class="text-2xl font-bold">Masuk ke KF OLX</h1>
            <p class="text-gray-300">Akses akun Anda untuk mulai jual beli dengan mudah</p>
        </div>
    </header>

    <main class="container mx-auto px-4 mb-10">
        <div class="flex justify-center">
            <div class="w-full md:w-1/2 lg:w-2/5">
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200" data-aos="fade-up" data-aos-duration="1000">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center text-teal-800 text-3xl">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Masuk</h2>
                            <p class="text-gray-500">Isi data di bawah untuk masuk ke akun Anda</p>
                        </div>
                    </div>

                    <?php if ($justRegistered && !$alreadyLoggedIn): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert" data-aos="fade-down">
                            Pendaftaran berhasil. Silakan masuk menggunakan email dan kata sandi Anda.
                        </div>
                    <?php endif; ?>

                    <?php if (!$alreadyLoggedIn && !empty($errors)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert" data-aos="fade-down">
                            <div class="font-semibold mb-1">Login gagal:</div>
                            <ul class="list-disc list-inside mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="post" novalidate class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="email" name="email" placeholder="nama@email.com" value="<?php echo htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8'); ?>" required />
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                            <input type="password" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="password" name="password" placeholder="••••••••" required />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" class="h-4 w-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500" id="rememberMe" name="remember" />
                                <label for="rememberMe" class="ml-2 block text-sm text-gray-900">Ingat saya</label>
                            </div>
                            <a href="#" class="text-sm text-teal-600 hover:text-teal-800 font-medium transition-colors">Lupa kata sandi?</a>
                        </div>
                        
                        <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-3 px-4 rounded-full shadow transition-colors duration-200">
                            Masuk
                        </button>
                    </form>

                    <div class="relative flex items-center justify-center my-6">
                        <span class="absolute px-3 bg-white text-gray-400 text-sm">atau</span>
                        <div class="flex-grow border-t border-gray-200"></div>
                    </div>

                    <div class="space-y-3">
                        <button type="button" class="w-full flex items-center justify-center gap-2 bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded-full border border-gray-200 shadow-sm hover:bg-gray-100 transition-colors duration-200">
                            <i class="fab fa-google text-red-500"></i> Masuk dengan Google
                        </button>
                        <button type="button" class="w-full flex items-center justify-center gap-2 bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded-full border border-gray-200 shadow-sm hover:bg-gray-100 transition-colors duration-200">
                            <i class="fab fa-facebook text-blue-600"></i> Masuk dengan Facebook
                        </button>
                    </div>

                    <p class="text-center mt-6 text-gray-600">
                        Belum punya akun? <a href="register.php" class="text-teal-600 hover:text-teal-800 font-medium transition-colors">Daftar sekarang</a>
                    </p>
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

        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Basic client-side validation style
        (function(){
            const form = document.querySelector('form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

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

            form.addEventListener('submit', function(e){
                let valid = true;
                
                if (!emailInput.value.trim() || !emailInput.checkValidity()) {
                    applyValidationClass(emailInput, false);
                    valid = false;
                } else {
                    applyValidationClass(emailInput, true);
                }

                if (!passwordInput.value) {
                    applyValidationClass(passwordInput, false);
                    valid = false;
                } else {
                    applyValidationClass(passwordInput, true);
                }

                if (!valid) {
                    e.preventDefault();
                }
            });
        })();
    </script>
</body>
</html>