<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $database = new Database();
    $db = $database->getConnection();

    // Hapus data siswa berdasarkan ID
    $query = "DELETE FROM siswa WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo "<script>alert('Siswa berhasil dihapus!'); window.location.href='manajemen_siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus siswa.'); window.location.href='manajemen_siswa.php';</script>";
    }
} else {
    echo "<script>alert('ID tidak valid.'); window.location.href='manajemen_siswa.php';</script>";
}
?>
