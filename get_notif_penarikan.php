<?php
include 'config/database.php'; // Sesuaikan dengan koneksi database PDO

try {
    // Query untuk menghitung jumlah penarikan dengan status 'pending'
    $query = "SELECT COUNT(*) AS jumlah FROM penarikan WHERE status = 'pending'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    // Ambil hasil query
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['jumlah']; // Output jumlah notifikasi
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
