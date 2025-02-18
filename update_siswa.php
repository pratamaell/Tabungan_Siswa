<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Proses update data jika form dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $saldo = $_POST['saldo'];

    if (empty($id) || empty($nama) || empty($kelas) || !is_numeric($saldo)) {
        echo "<script>alert('Harap isi semua bidang dengan benar!'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }

    // Update data siswa
    $query = "UPDATE siswa 
              JOIN users ON siswa.user_id = users.id 
              JOIN kelas ON siswa.kelas_id = kelas.id 
              SET users.name = :nama, kelas.nama_kelas = :kelas, siswa.saldo = :saldo 
              WHERE siswa.id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nama', $nama);
    $stmt->bindParam(':kelas', $kelas);
    $stmt->bindParam(':saldo', $saldo);

    if ($stmt->execute()) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location.href='manajemen_siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.'); window.location.href='manajemen_siswa.php';</script>";
    }
}
?>
