<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Hitung jumlah siswa
$query_siswa = "SELECT COUNT(*) as total_siswa FROM users WHERE role = 'siswa'";
$stmt_siswa = $db->prepare($query_siswa);
$stmt_siswa->execute();
$total_siswa = $stmt_siswa->fetch(PDO::FETCH_ASSOC);

// Hitung jumlah kelas
$query_kelas = "SELECT COUNT(*) as total_kelas FROM classes";
$stmt_kelas = $db->prepare($query_kelas);
$stmt_kelas->execute();
$total_kelas = $stmt_kelas->fetch(PDO::FETCH_ASSOC);

// Hitung total saldo semua siswa
$query_saldo = "SELECT SUM(amount) as total_saldo FROM transactions";
$stmt_saldo = $db->prepare($query_saldo);
$stmt_saldo->execute();
$total_saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0984e3, #74b9ff);
            color: #fff;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #2d3436;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px 0;
        }
        .sidebar h2 {
            color: #00cec9;
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 15px;
            text-align: center;
        }
        .sidebar ul li a {
            color: #dfe6e9;
            text-decoration: none;
            font-size: 18px;
            display: block;
        }
        .sidebar ul li a:hover {
            background: #00cec9;
            color: #2d3436;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .header {
            background: #00cec9;
            padding: 20px;
            text-align: center;
            color: #2d3436;
            font-size: 24px;
            font-weight: bold;
        }
        .card {
            background: #0984e3;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .card h3 {
            margin-bottom: 10px;
            font-size: 20px;
        }
        .card p {
            margin: 0;
            font-size: 16px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #fff;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background: #74b9ff;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .sidebar ul li {
                text-align: left;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="#">Kelola Siswa</a></li>
            <li><a href="#">Kelola Kelas</a></li>
            <li><a href="#">Laporan Keuangan</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">Admin Dashboard</div>
        <div class="card">
            <h3>Statistik Sekolah</h3>
            <p>Jumlah Siswa: <?php echo $total_siswa['total_siswa']; ?></p>
            <p>Jumlah Kelas: <?php echo $total_kelas['total_kelas']; ?></p>
            <p>Total Saldo Keseluruhan: Rp <?php echo number_format($total_saldo['total_saldo'], 0, ',', '.'); ?></p>
        </div>
        <div class="card">
            <h3>Informasi Tambahan</h3>
            <p>Pastikan semua data siswa dan kelas telah diperbarui.</p>
        </div>
    </div>
</body>
</html>
