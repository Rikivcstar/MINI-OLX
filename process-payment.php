<?php
require 'vendor/autoload.php';
require 'config.php';

// Set your Merchant Server Key from Midtrans Dashboard
\Midtrans\Config::$serverKey = 'SB-Mid-server-CoUUtJVoEcB0FTa8jWXtxkjo';
// Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
\Midtrans\Config::$isProduction = false;
// Set sanitization on (default)
\Midtrans\Config::$isSanitized = true;
// Set 3DS transaction for credit card to true
\Midtrans\Config::$is3ds = true;

header('Content-Type: application/json');

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Silakan login terlebih dahulu');
    }

    // Validate required fields
    if (empty($data['transaction_details']['order_id']) || 
        empty($data['transaction_details']['gross_amount']) || 
        empty($data['item_details']) || 
        empty($data['customer_details'])) {
        throw new Exception('Data transaksi tidak lengkap');
    }

    // Ensure payload has valid shapes for Midtrans
    if (!isset($data['customer_details']) || !is_array($data['customer_details'])) {
        $data['customer_details'] = [];
    }
    // Sanitize email
    $cd = &$data['customer_details'];
    $buyerName = isset($_SESSION['user_name']) && $_SESSION['user_name'] !== '' ? $_SESSION['user_name'] : 'Customer';
    $buyerEmail = isset($cd['email']) ? (string)$cd['email'] : '';
    if (!filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
        $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $buyerEmail = 'user' . ($uid ?: 'guest') . '@example.test';
    }
    $buyerPhone = isset($cd['phone']) ? preg_replace('/[^0-9+]/', '', (string)$cd['phone']) : '';
    if ($buyerPhone === '' || strlen(preg_replace('/\D/','', $buyerPhone)) < 8) {
        $buyerPhone = '081234567890';
    }
    $cd['first_name'] = $buyerName;
    $cd['email'] = $buyerEmail;
    $cd['phone'] = $buyerPhone;

    // Normalize required numeric types
    $data['transaction_details']['gross_amount'] = (int) $data['transaction_details']['gross_amount'];
    // Snap requires unique order_id length <= 50
    $data['transaction_details']['order_id'] = substr((string)$data['transaction_details']['order_id'], 0, 50);

    // Save transaction to database
    $pdo = db();
    $stmt = $pdo->prepare("INSERT INTO transactions 
        (order_id, user_id, ad_id, amount, status, created_at) 
        VALUES (?, ?, ?, ?, 'pending', NOW())");
    
    $adId = $data['item_details'][0]['id'];
    $stmt->execute([
        $data['transaction_details']['order_id'],
        $_SESSION['user_id'],
        $adId,
        $data['transaction_details']['gross_amount']
    ]);

    // Get Snap Token from Midtrans
    $snapToken = \Midtrans\Snap::getSnapToken($data);

    // Return the token and other details
    echo json_encode([
        'status' => 'success',
        'token' => $snapToken,
        'order_id' => $data['transaction_details']['order_id']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
