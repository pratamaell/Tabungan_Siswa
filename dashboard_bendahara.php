<?php
session_start();

// Pastikan pengguna sudah login dan memiliki role 'bendahara'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bendahara') {
    header("Location: login_register.php");
    exit();
}

// Koneksi ke database
require_once 'config/database.php';
include 'navbar_bendahara.php';

try {
    $db = (new Database())->getConnection();

    // Ambil nama bendahara untuk ditampilkan di dashboard
    $user_id = $_SESSION['user_id'];
    $queryBendahara = "SELECT name FROM users WHERE id = :user_id";
    $stmt = $db->prepare($queryBendahara);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $bendahara = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ambil total tabungan
    $queryTotal = "SELECT SUM(nominal) AS total_tabungan FROM transaksi WHERE jenis = 'setoran'";
    $stmt = $db->prepare($queryTotal);
    $stmt->execute();
    $totalTabungan = $stmt->fetch(PDO::FETCH_ASSOC)['total_tabungan'] ?? 0;

    // Ambil riwayat transaksi
    $queryTransactions = "SELECT t.id, u.name, t.nominal, t.jenis, t.created_at 
                      FROM transaksi t
                      JOIN siswa s ON t.siswa_id = s.id
                      JOIN users u ON s.user_id = u.id
                      WHERE t.jenis = 'setoran'
                      ORDER BY t.created_at DESC";

    $stmt = $db->prepare($queryTransactions);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bendahara Dashboard</title>
    <style>
        /* General Styling */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #dfefff, #dfefff);
            color: #2d3436;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 2.5em;
            color: #0984e3;
        }
        .header h2 {
            color: #636e72;
            margin-top: 10px;
            font-size: 1.2em;
        }

        /* Card Styling */
        .stats-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 40px;
        }
        .card {
            flex: 1;
            padding: 20px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h2 {
            font-size: 1.5em;
            color: #0984e3;
        }
        .card p {
            font-size: 2em;
            font-weight: bold;
            margin: 10px 0;
            color: #2d3436;
        }
        .card i {
            font-size: 3em;
            color: #0984e3;
            margin-bottom: 10px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #ffffff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #dfe6e9;
            font-size: 1em;
        }
        table th {
            background: #0984e3;
            color: #fff;
            text-transform: uppercase;
        }
        table tr:nth-child(even) {
            background: #f5f5f5;
        }
        table tr:hover {
            background: #dfe6e9;
        }

        /* Button Styling */
        .btn-logout {
            display: block;
            text-align: center;
            margin: 30px auto;
            padding: 15px 30px;
            background: #d63031;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: bold;
            transition: background 0.3s, transform 0.3s;
            width: 200px;
        }
        .btn-logout:hover {
            background: #e17055;
            transform: scale(1.05);
        }

        /* Responsive Styling */
        @media (max-width: 768px) {
            .stats-container {
                flex-direction: column;
            }
            .card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Dashboard Bendahara</h1>
            <h2>Selamat datang, <?= htmlspecialchars($bendahara['name']); ?>!</h2>
        </div>

        <!-- Statistik Card -->
        <div class="stats-container">
            <div class="card">
                <i class="fas fa-wallet"></i>
                <h2>Total Tabungan</h2>
                <p>Rp <?= number_format($totalTabungan, 0, ',', '.'); ?></p>
            </div>
        </div>

        <!-- Riwayat Transaksi -->
        <h2 style="text-align: center; margin-top: 40px; color: #0984e3;">Riwayat Setoran Siswa</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Jumlah (Rp)</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $index => $transaction): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= htmlspecialchars($transaction['name']); ?></td>
                            <td><?= number_format($transaction['nominal'], 0, ',', '.'); ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($transaction['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">Belum ada transaksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <!-- FontAwesome untuk ikon -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
