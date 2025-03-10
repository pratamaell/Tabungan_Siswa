<?php
include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

session_start();
if(isset($_GET['action'])){
    $action = $_GET['action'];
}else{
    $action = "";
}

if($action == "delete"){
    try {
        $db->beginTransaction(); // Mulai transaksi agar lebih aman

        $id = $_GET['id'];

        // Hapus data transaksi
        $queryTransaksi = $db->prepare("DELETE FROM transaksi WHERE siswa_id IN (SELECT id FROM siswa WHERE user_id = :id)");
        $queryTransaksi->bindValue(':id', $id, PDO::PARAM_INT);
        $queryTransaksi->execute();

        // Hapus data penarikan
        $queryPenarikan = $db->prepare("DELETE FROM penarikan WHERE siswa_id IN (SELECT id FROM siswa WHERE user_id = :id)");
        $queryPenarikan->bindValue(':id', $id, PDO::PARAM_INT);
        $queryPenarikan->execute();

        // Hapus data siswa
        $querySiswa = $db->prepare("DELETE FROM siswa WHERE user_id = :id");
        $querySiswa->bindValue(':id', $id, PDO::PARAM_INT);
        $querySiswa->execute();

        // Hapus akun user
        $queryUser = $db->prepare("DELETE FROM users WHERE id = :id");
        $queryUser->bindValue(':id', $id, PDO::PARAM_INT);
        $queryUser->execute();

        $db->commit(); // Simpan semua perubahan jika berhasil

        $_SESSION['sukses'] = "Akun berhasil dihapus";
        header('Location: manajemen_akun_pengaturan.php');
        exit();
    } catch (Exception $e) {
        $db->rollBack(); // Batalkan perubahan jika terjadi error
         $_SESSION['error'] = "Akun gagal dihapus: " . $e->getMessage();
        header('Location: manajemen_akun_pengaturan.php');
        exit();
    }
}
?>
