<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';
include 'navbar_admin.php';

$database = new Database();
$conn = $database->getConnection();

// Ambil parameter bulan dan tahun
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Ambil total setoran
$query_setoran = "SELECT IFNULL(SUM(nominal), 0) AS total_setoran FROM transaksi WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
$stmt_setoran = $conn->prepare($query_setoran);
$stmt_setoran->bindValue(':bulan', (int)$bulan, PDO::PARAM_INT);
$stmt_setoran->bindValue(':tahun', (int)$tahun, PDO::PARAM_INT);
$stmt_setoran->execute();
$total_setoran = $stmt_setoran->fetch(PDO::FETCH_ASSOC)['total_setoran'];

// Ambil total penarikan
$query_penarikan = "SELECT IFNULL(SUM(nominal), 0) AS total_penarikan FROM penarikan WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
$stmt_penarikan = $conn->prepare($query_penarikan);
$stmt_penarikan->bindValue(':bulan', (int)$bulan, PDO::PARAM_INT);
$stmt_penarikan->bindValue(':tahun', (int)$tahun, PDO::PARAM_INT);
$stmt_penarikan->execute();
$total_penarikan = $stmt_penarikan->fetch(PDO::FETCH_ASSOC)['total_penarikan'];

// Ambil total pengeluaran
$query_pengeluaran = "SELECT IFNULL(SUM(nominal), 0) AS total_pengeluaran FROM pengeluaran WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
$stmt_pengeluaran = $conn->prepare($query_pengeluaran);
$stmt_pengeluaran->bindValue(':bulan', (int)$bulan, PDO::PARAM_INT);
$stmt_pengeluaran->bindValue(':tahun', (int)$tahun, PDO::PARAM_INT);
$stmt_pengeluaran->execute();
$total_pengeluaran = $stmt_pengeluaran->fetch(PDO::FETCH_ASSOC)['total_pengeluaran'];

// Hitung saldo akhir
$saldo_akhir = $total_setoran - $total_penarikan - $total_pengeluaran;

// Pagination untuk daftar transaksi
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil daftar transaksi
$query_transaksi = "SELECT * FROM transaksi WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun ORDER BY tanggal DESC LIMIT :limit OFFSET :offset";
$stmt_transaksi = $conn->prepare($query_transaksi);
$stmt_transaksi->bindValue(':bulan', (int)$bulan, PDO::PARAM_INT);
$stmt_transaksi->bindValue(':tahun', (int)$tahun, PDO::PARAM_INT);
$stmt_transaksi->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_transaksi->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_transaksi->execute();
$transaksi = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);

// Hitung total halaman
$query_count = "SELECT COUNT(*) AS total FROM transaksi WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
$stmt_count = $conn->prepare($query_count);
$stmt_count->bindValue(':bulan', (int)$bulan, PDO::PARAM_INT);
$stmt_count->bindValue(':tahun', (int)$tahun, PDO::PARAM_INT);
$stmt_count->execute();
$total_rows = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_rows / $limit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: auto;
        }
        h1 { color: #333; }
        .report-grid {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .report-item {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
            width: 22%;
        }
        .filter-form select, .filter-form button {
            padding: 10px;
            margin: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        .pagination {
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 12px;
            margin: 2px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .pagination a.active {
            background: #0056b3;
        }
        .print-button {
            padding: 10px 20px;
            margin-top: 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        @media print {
            .filter-form, .pagination, .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laporan Keuangan Admin</h1>
        <form class="filter-form" method="GET">
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
            <div class="report-item"><strong>Total Setoran:</strong> Rp <?= number_format($total_setoran, 2, ',', '.'); ?></div>
            <div class="report-item"><strong>Total Penarikan:</strong> Rp <?= number_format($total_penarikan, 2, ',', '.'); ?></div>
            <div class="report-item"><strong>Total Pengeluaran:</strong> Rp <?= number_format($total_pengeluaran, 2, ',', '.'); ?></div>
            <div class="report-item"><strong>Saldo Akhir:</strong> Rp <?= number_format($saldo_akhir, 2, ',', '.'); ?></div>
        </div>

        <button class="print-button" onclick="window.print()">Cetak Laporan</button>
    </div>
</body>
</html>
