<?php
session_start();

// Jika belum logout, lakukan penghapusan session dulu
if (!isset($_GET['success'])) {

    // Hapus semua variabel session
    $_SESSION = array();

    // Jika menggunakan cookie session, hapus juga cookienya
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Hancurkan session di sisi server
    session_destroy();
    session_unset();

    // Redirect ke halaman yang sama untuk memunculkan alert
    header("Location: logout.php?success=1");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logout</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
const urlParams = new URLSearchParams(window.location.search);
const success = urlParams.get('success');

if (success === '1') {
  Swal.fire({
    title: "Apa Kamu Yakin?",
    text: "Kamu akan logout dari sistem!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Lanjutkan?"
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Berhasil!",
        text: "Kamu berhasil Logout.",
        icon: "success"
      }).then(() => {
        window.location.href = "index.php"; // Redirect setelah alert sukses
      });
    } else {
      window.location.href = "index.php"; // Kalau dibatalkan, tetap kembali ke index
    }
  });
}
</script>

</body>
</html>
\