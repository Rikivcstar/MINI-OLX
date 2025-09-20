<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'post-add.php';
    header('Location: login.php');
    exit();
}

// Panggil koneksi PDO
$pdo = db();

$user_id = $_SESSION['user_id'];
$title = $_POST['title'] ?? '';
$category_id = $_POST['category_id'] ?? '';
$price = $_POST['price'] ?? 0;
$location = $_POST['location'] ?? '';
$description = $_POST['description'] ?? '';


try {
    // Mulai transaksi
    $pdo->beginTransaction();

    // Simpan data iklan
    $stmt = $pdo->prepare("
        INSERT INTO ads (user_id, category_id, title, description, price, location) 
        VALUES (:user_id, :category_id, :title, :description, :price, :location)
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':category_id' => $category_id,
        ':title' => $title,
        ':description' => $description,
        ':price' => $price,
        ':location' => $location
    ]);
    $ad_id = $pdo->lastInsertId();

    // Upload gambar
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $upload_path = __DIR__ . '/uploads/' . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            // Simpan nama file ke tabel ad_images
            $stmt = $pdo->prepare("
                INSERT INTO ad_images (ad_id, image_path) 
                VALUES (:ad_id, :image_path)
            ");
            $stmt->execute([
                ':ad_id' => $ad_id,
                ':image_path' => $filename
            ]);
        }
    }

    // Commit transaksi
    $pdo->commit();
    echo "<script>alert('Iklan berhasil dipasang!');window.location='index.php';</script>";
} catch (PDOException $e) {
    // Rollback jika ada error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<script>alert('Gagal memasang iklan: " . $e->getMessage() . "');window.location='post-add.php';</script>";
}
