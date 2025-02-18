<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bendahara') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';
include 'navbar_bendahara.php';

$database = new Database();
$conn = $database->getConnection();

// Ambil parameter bulan dan tahun
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Ambil total setoran
$query_setoran = "SELECT SUM(nominal) AS total_setoran FROM transaksi WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
$stmt_setoran = $conn->prepare($query_setoran);
$stmt_setoran->bindParam(':bulan', $bulan);
$stmt_setoran->bindParam(':tahun', $tahun);
$stmt_setoran->execute();
$total_setoran = $stmt_setoran->fetch(PDO::FETCH_ASSOC)['total_setoran'];

// Ambil total penarikan
$query_penarikan = "SELECT SUM(nominal) AS total_penarikan FROM penarikan WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
$stmt_penarikan = $conn->prepare($query_penarikan);
$stmt_penarikan->bindParam(':bulan', $bulan);
$stmt_penarikan->bindParam(':tahun', $tahun);
$stmt_penarikan->execute();
$total_penarikan = $stmt_penarikan->fetch(PDO::FETCH_ASSOC)['total_penarikan'];

// Ambil total pengeluaran
$query_pengeluaran = "SELECT SUM(nominal) AS total_pengeluaran FROM pengeluaran WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
$stmt_pengeluaran = $conn->prepare($query_pengeluaran);
$stmt_pengeluaran->bindParam(':bulan', $bulan);
$stmt_pengeluaran->bindParam(':tahun', $tahun);
$stmt_pengeluaran->execute();
$total_pengeluaran = $stmt_pengeluaran->fetch(PDO::FETCH_ASSOC)['total_pengeluaran'];

// Hitung saldo akhir
$saldo_akhir = $total_setoran - $total_penarikan - $total_pengeluaran;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Bendahara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .report-item {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }

        .report-item strong {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .filter-form {
            margin-bottom: 20px;
        }

        .filter-form select {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .filter-form button {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .filter-form button:hover {
            background: #0056b3;
        }

        .print-button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laporan Keuangan Bendahara</h1>
        <form class="filter-form" method="GET" action="">
            <select name="bulan">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i; ?>" <?= $i == $bulan ? 'selected' : ''; ?>><?= date('F', mktime(0, 0, 0, $i, 10)); ?></option>
                <?php endfor; ?>
            </select>
            <select name="tahun">
                <?php for ($i = date('Y'); $i >= 2000; $i--): ?>
                    <option value="<?= $i; ?>" <?= $i == $tahun ? 'selected' : ''; ?>><?= $i; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit">Tampilkan</button>
        </form>
        <div class="report-grid">
            <div class="report-item">
                <strong>Total Setoran:</strong>
                Rp <?= number_format($total_setoran, 2, ',', '.'); ?>
            </div>
            <div class="report-item">
                <strong>Total Penarikan:</strong>
                Rp <?= number_format($total_penarikan, 2, ',', '.'); ?>
            </div>
            <div class="report-item">
                <strong>Total Pengeluaran:</strong>
                Rp <?= number_format($total_pengeluaran, 2, ',', '.'); ?>
            </div>
            <div class="report-item">
                <strong>Saldo Akhir:</strong>
                Rp <?= number_format($saldo_akhir, 2, ',', '.'); ?>
            </div>
        </div>
        <button class="print-button" onclick="window.print()">Cetak Laporan</button>
    </div>
</body>
</html>