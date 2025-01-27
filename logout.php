<?php
// Mulai sesi
session_start();

// Hapus semua variabel sesi
session_unset();

// Hancurkan sesi
session_destroy();

// Alihkan pengguna kembali ke halaman login
header("Location: login_register.php");
exit();
?>
