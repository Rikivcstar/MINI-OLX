<?php
// Untuk menampilkan order_id dari query string
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pembayaran Berhasil - KF OLX</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
  <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-8 text-center" data-aos="zoom-in">
    <div class="text-green-500 text-6xl mb-4">
      <i class="fas fa-check-circle"></i>
    </div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h1>
    <p class="text-gray-600 mb-6">Terima kasih telah berbelanja di KF OLX. Pesanan Anda sedang diproses.</p>

    <div class="bg-gray-50 p-4 rounded-lg mb-6 text-left">
      <h2 class="font-semibold text-gray-700 mb-2">Detail Pesanan:</h2>
      <p class="text-sm text-gray-600">No. Pesanan: <?php echo htmlspecialchars($_GET['order_id'] ?? '-'); ?></p>
    </div>

    <div class="space-y-3">
      <a href="my-orders.php" class="block bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">Lihat Pesanan Saya</a>
      <a href="index.php" class="block text-teal-700 hover:text-teal-900 font-medium">Kembali ke Beranda</a>
    </div>
  </div>

  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>AOS.init();</script>
</body>
</html>
