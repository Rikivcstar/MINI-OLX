<?php
require_once __DIR__ . '/config.php';

$errors = [];
$old = [
    'name' => '',
    'email' => '',
    'whatsapp' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $terms = isset($_POST['terms']);

    $old['name'] = $name;
    $old['email'] = $email;
    $old['whatsapp'] = $whatsapp;
    
    // Validasi server-side
    if ($name === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }
    
    if ($whatsapp === '') {
        $errors[] = 'No. WhatsApp wajib diisi.';
    } elseif (!preg_match('/^(?:\+62|62|0)[0-9]{9,13}$/', $whatsapp)) {
        $errors[] = 'No. WhatsApp tidak valid. Gunakan format +628xxxx atau 08xxxx.';
    }
    

    if (strlen($password) < 8) {
        $errors[] = 'Kata sandi minimal 8 karakter.';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Konfirmasi kata sandi tidak cocok.';
    }
    if (!$terms) {
        $errors[] = 'Anda harus menyetujui Syarat & Ketentuan.';
    }

    if (empty($errors)) {
        try {
            $pdo = db();

            // Cek unik email
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain atau masuk.';
            } else {
                // Simpan user
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare('INSERT INTO users (name, email, whatsapp, password) VALUES (?, ?, ?, ?)');
                $ins->execute([$name, $email, $whatsapp, $hash]);

                // Redirect ke login dengan tanda berhasil
                redirect('register.php?success=1');
            }
        } catch (Throwable $t) {
            // Tampilkan pesan generik (log detailnya di production)
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
    <title>Daftar - KF OLX</title>
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
            <h1 class="text-2xl font-bold">Daftar Akun Baru</h1>
            <p class="text-gray-300">Buat akun untuk mulai jual beli dengan mudah</p>
        </div>
    </header>

    <main class="container mx-auto px-4 mb-10">
        <div class="flex justify-center">
            <div class="w-full md:w-2/3 lg:w-1/2">
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200" data-aos="fade-up" data-aos-duration="1000">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center text-teal-800 text-3xl">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Daftar</h2>
                            <p class="text-gray-500">Isi data di bawah untuk membuat akun baru</p>
                        </div>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert" data-aos="fade-down">
                            <div class="font-semibold mb-1">Pendaftaran gagal:</div>
                            <ul class="list-disc list-inside mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="post" novalidate class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="name" name="name" placeholder="Nama Anda" value="<?php echo htmlspecialchars($old['name'], ENT_QUOTES, 'UTF-8'); ?>" required />
                            </div>
                            <div class="md:col-span-2">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="email" name="email" placeholder="nama@email.com" value="<?php echo htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8'); ?>" required />
                            </div>
                            <div class="md:col-span-2">
                                <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fab fa-whatsapp"></i>
                                    </div>
                                    <input type="text" class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg shadow-sm focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="whatsapp" name="whatsapp" placeholder="contoh: 08123456789 atau +628123456789" value="<?php echo htmlspecialchars($old['whatsapp'], ENT_QUOTES, 'UTF-8'); ?>" required />
                                </div>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                                <input type="password" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="password" name="password" placeholder="Minimal 8 karakter" minlength="8" required />
                            </div>
                            <div>
                                <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
                                <input type="password" class="w-full border border-gray-200 rounded-lg shadow-sm py-3 px-4 focus:border-teal-400 focus:ring-1 focus:ring-teal-200 transition-all duration-200" id="password_confirm" name="password_confirm" placeholder="Ulangi kata sandi" required />
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500" id="terms" name="terms" required />
                            <label for="terms" class="ml-2 block text-sm text-gray-900">
                                Saya menyetujui <a href="#" class="text-teal-600 hover:text-teal-800 font-medium transition-colors">Syarat & Ketentuan</a> dan <a href="#" class="text-teal-600 hover:text-teal-800 font-medium transition-colors">Kebijakan Privasi</a>.
                            </label>
                        </div>

                        <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-3 px-4 rounded-full shadow transition-colors duration-200">
                            Daftar
                        </button>
                    </form>
                    
                    <div class="relative flex items-center justify-center my-6">
                        <span class="absolute px-3 bg-white text-gray-400 text-sm">atau</span>
                        <div class="flex-grow border-t border-gray-200"></div>
                    </div>

                    <div class="space-y-3">
                        <button type="button" class="w-full flex items-center justify-center gap-2 bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded-full border border-gray-200 shadow-sm hover:bg-gray-100 transition-colors duration-200">
                            <i class="fab fa-google text-red-500"></i> Daftar dengan Google
                        </button>
                        <button type="button" class="w-full flex items-center justify-center gap-2 bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded-full border border-gray-200 shadow-sm hover:bg-gray-100 transition-colors duration-200">
                            <i class="fab fa-facebook text-blue-600"></i> Daftar dengan Facebook
                        </button>
                    </div>

                    <p class="text-center mt-6 text-gray-600">
                        Sudah punya akun? <a href="login.php" class="text-teal-600 hover:text-teal-800 font-medium transition-colors">Masuk</a>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    // Cek jika ada parameter success di URL
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

        if (success === '1') {
            Swal.fire({
                    title: 'Berhasil Daftar!',
                    text: 'Akun kamu berhasil dibuat. Silakan login untuk melanjutkan.',
                    icon: 'success',
                    confirmButtonText: 'Ke Halaman Login',
                    confirmButtonColor: '#0d9488'
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
            const pwdInput = document.getElementById('password');
            const pwdConfirmInput = document.getElementById('password_confirm');
            const termsCheckbox = document.getElementById('terms');
            
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

            whatsappInput.addEventListener('input', function() {
                const regex = /^(?:\+62|62|0)[0-9]{9,13}$/;
                applyValidationClass(this, regex.test(this.value.trim()));
            });

            const validatePwdMatch = () => {
                const isValid = pwdInput.value === pwdConfirmInput.value;
                applyValidationClass(pwdConfirmInput, isValid);
                return isValid;
            };

            pwdInput.addEventListener('input', validatePwdMatch);
            pwdConfirmInput.addEventListener('input', validatePwdMatch);

            form.addEventListener('submit', function(e){
                let valid = true;
                
                // Name validation
                if (nameInput.value.trim() === '') {
                    applyValidationClass(nameInput, false);
                    valid = false;
                } else {
                    applyValidationClass(nameInput, true);
                }

                // Email validation
                if (!emailInput.value.trim() || !emailInput.checkValidity()) {
                    applyValidationClass(emailInput, false);
                    valid = false;
                } else {
                    applyValidationClass(emailInput, true);
                }

                // WhatsApp validation
                const whatsappRegex = /^(?:\+62|62|0)[0-9]{9,13}$/;
                if (whatsappInput.value.trim() === '' || !whatsappRegex.test(whatsappInput.value.trim())) {
                    applyValidationClass(whatsappInput, false);
                    valid = false;
                } else {
                    applyValidationClass(whatsappInput, true);
                }

                // Password validation
                if (pwdInput.value.length < 8) {
                    applyValidationClass(pwdInput, false);
                    valid = false;
                } else {
                    applyValidationClass(pwdInput, true);
                }

                // Password match validation
                if (!validatePwdMatch()) {
                    valid = false;
                }

                // Terms and conditions validation
                if (!termsCheckbox.checked) {
                    termsCheckbox.classList.add('border-red-500');
                    valid = false;
                } else {
                    termsCheckbox.classList.remove('border-red-500');
                }

                if (!valid) {
                    e.preventDefault();
                }
            });
        })();
    </script>
</body>
</html>