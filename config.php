<?php
// config.php - Konfigurasi koneksi database menggunakan PDO untuk KF OLX
// Sesuaikan kredensial di bawah ini dengan environment lokal Anda

// Mulai session untuk kebutuhan login/sesi user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi database
$DB_CONFIG = [
    'host' => '127.0.0.1',   // atau 'localhost'
    'port' => '3306',        // port MySQL default
    'dbname' => 'olx_v2',    // ganti dengan nama database Anda
    'username' => 'root',    // default XAMPP biasanya 'root'
    'password' => '',        // default XAMPP biasanya kosong
    'charset' => 'utf8mb4',
];

/**
 * Mengembalikan instance PDO singleton
 * - Error mode: exceptions
 * - Default fetch mode: associative array
 * - Emulate prepares: off (gunakan prepared statement native)
 */
function db(): PDO {
    static $pdo = null;
    global $DB_CONFIG;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $DB_CONFIG['host'],
        $DB_CONFIG['port'],
        $DB_CONFIG['dbname'],
        $DB_CONFIG['charset']
    );

    try {
        $pdo = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Untuk production, sebaiknya log error dan tampilkan pesan generik
        http_response_code(500);
        die('Gagal terhubung ke database. Periksa konfigurasi Anda.');
    }
}

/**
 * Helper untuk output aman ke HTML
 */
function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper redirect sederhana
 */
function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

/**
 * Contoh pengecekan login (template)
 * - Jika sudah ada sistem login, set $_SESSION['user_id'] saat sukses login
 */
function current_user_id(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

// Validasi struktur tabel (opsional, untuk pengembangan)
// Anda dapat menonaktifkan blok di bawah ini di production
/*
try {
    $pdo = db();
    // Cek salah satu tabel sebagai indikator (misal: users)
    $pdo->query('SELECT 1 FROM users LIMIT 1');
} catch (Throwable $t) {
    // Komentar baris di bawah ini jika tidak ingin melihat error saat tabel belum dibuat
    // die('Tabel database belum siap: ' . e($t->getMessage()));
}
*/
