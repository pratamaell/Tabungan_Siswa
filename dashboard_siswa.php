<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Ambil data siswa yang login
$user_id = $_SESSION['user_id'];
$query = "SELECT name FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil total saldo siswa
$query_saldo = "SELECT SUM(amount) as total_saldo FROM transactions WHERE user_id = :user_id";
$stmt_saldo = $db->prepare($query_saldo);
$stmt_saldo->bindParam(':user_id', $user_id);
$stmt_saldo->execute();
$saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

// Ambil riwayat transaksi siswa
$query_transaksi = "SELECT * FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
$stmt_transaksi = $db->prepare($query_transaksi);
$stmt_transaksi->bindParam(':user_id', $user_id);
$stmt_transaksi->execute();
$transaksi = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
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
            color: #fdcb6e;
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
            background: #fdcb6e;
            color: #2d3436;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .header {
            background: #fdcb6e;
            padding: 20px;
            text-align: center;
            color: #2d3436;
            font-size: 24px;
            font-weight: bold;
        }
        .card {
            background: #6c5ce7;
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
            background: #a29bfe;
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
        <h2>Siswa Panel</h2>
        <ul>
            <li><a href="#">Lihat Saldo</a></li>
            <li><a href="#">Riwayat Transaksi</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">Siswa Dashboard</div>
        <div class="card">
            <h3>Selamat Datang, <?php echo $user['name']; ?>!</h3>
            <p>Berikut adalah informasi tabungan Anda:</p>
        </div>
        <div class="card">
            <h3>Total Saldo</h3>
            <p>Rp <?php echo number_format($saldo['total_saldo'], 0, ',', '.'); ?></p>
        </div>
        <div class="card">
            <h3>Riwayat Transaksi</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksi as $trx): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($trx['created_at'])); ?></td>
                            <td><?php echo $trx['description']; ?></td>
                            <td>Rp <?php echo number_format($trx['amount'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
