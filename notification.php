<?php
// Jangan gunakan session_start() di file notifikasi.
// File ini dipanggil oleh Midtrans server, bukan browser pengguna.

require 'vendor/autoload.php';
require 'config.php'; // Pastikan db() ada disini

// 1. Konfigurasi Midtrans (harus sama dengan process-payment.php)
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$serverKey = 'SB-Mid-server-CoUUtJVoEcB0FTa8jWXtxkjo'; 

// 2. Ambil notifikasi dari Midtrans
$notif = new \Midtrans\Notification();

// Data penting dari notifikasi
$transaction_status = $notif->transaction_status;
$payment_type = $notif->payment_type;
$order_id = $notif->order_id;
$fraud_status = $notif->fraud_status;

// Log untuk debugging (opsional, tapi sangat disarankan)
error_log("Midtrans Notification: " . $order_id . " | Status: " . $transaction_status);

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        // Jika order tidak ditemukan, Midtrans tidak perlu mencoba lagi.
        http_response_code(404);
        die("Order ID not found.");
    }

    $new_status = 'pending';
    
    if ($transaction_status == 'capture') {
        if ($payment_type == 'credit_card') {
            if($fraud_status == 'accept') {
                $new_status = 'success'; // Kartu kredit berhasil (Accepted)
            }
        }
    } else if ($transaction_status == 'settlement') {
        $new_status = 'success'; // Pembayaran berhasil untuk non-kartu kredit (Transfer, VA, dll.)
        
    } else if ($transaction_status == 'pending') {
        $new_status = 'pending'; // Menunggu pembayaran
        
    } else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
        $new_status = 'failed'; // Transaksi gagal/expired/dibatalkan
    }

    // 3. Perbarui Status Transaksi di Database
    if ($new_status !== $transaction['status']) {
        $update_stmt = $pdo->prepare("UPDATE transactions SET status = ?, updated_at = NOW() WHERE order_id = ?");
        $update_stmt->execute([$new_status, $order_id]);
        
        // **LOGIKA TAMBAHAN PENTING:** // Jika status menjadi 'success', Anda mungkin perlu mengaktifkan iklan (jika ini untuk promosi)
        // atau menandai barang terjual di tabel 'ads'.
        if ($new_status === 'success') {
             // Contoh: UPDATE ads SET is_sold = 1 WHERE id = :ad_id
             // Anda dapat mengambil ad_id dari $transaction['ad_id']
        }
    }
    
    // 4. Beri tahu Midtrans bahwa notifikasi sudah diterima (PENTING)
    http_response_code(200);
    echo "OK";

} catch (Exception $e) {
    // Jika ada error pada kode Anda, berikan HTTP 500 agar Midtrans mencoba mengirim ulang
    http_response_code(500); 
    error_log("Notification Error: " . $e->getMessage());
    echo "Error processing notification.";
}
?>