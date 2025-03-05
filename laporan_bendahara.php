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
$result_setoran = $stmt_setoran->fetch(PDO::FETCH_ASSOC);
$total_setoran = $result_setoran['total_setoran'] ?? 0;

// Ambil total penarikan
$query_penarikan = "SELECT SUM(nominal) AS total_penarikan FROM penarikan WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
$stmt_penarikan = $conn->prepare($query_penarikan);
$stmt_penarikan->bindParam(':bulan', $bulan);
$stmt_penarikan->bindParam(':tahun', $tahun);
$stmt_penarikan->execute();
$result_penarikan = $stmt_penarikan->fetch(PDO::FETCH_ASSOC);
$total_penarikan = $result_penarikan['total_penarikan'] ?? 0;

// Ambil total pengeluaran
$query_pengeluaran = "SELECT SUM(nominal) AS total_pengeluaran FROM pengeluaran WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
$stmt_pengeluaran = $conn->prepare($query_pengeluaran);
$stmt_pengeluaran->bindParam(':bulan', $bulan);
$stmt_pengeluaran->bindParam(':tahun', $tahun);
$stmt_pengeluaran->execute();
$result_pengeluaran = $stmt_pengeluaran->fetch(PDO::FETCH_ASSOC);
$total_pengeluaran = $result_pengeluaran['total_pengeluaran'] ?? 0;

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4CAF50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --border-radius: 16px;
            --border-radius-sm: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f6f9fc 0%, #eef1f5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: 900px;
            overflow: hidden;
            padding: 0;
            position: relative;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                45deg,
                rgba(255, 255, 255, 0.05),
                rgba(255, 255, 255, 0.05) 10px,
                transparent 10px,
                transparent 20px
            );
        }

        h1 {
            font-weight: 600;
            margin: 0;
            font-size: 28px;
            position: relative;
            z-index: 1;
        }

        .content {
            padding: 30px;
        }

        .filter-form {
            background: rgba(255, 255, 255, 0.7);
            padding: 20px;
            border-radius: var(--border-radius-sm);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .select-wrapper {
            position: relative;
            min-width: 150px;
        }

        .select-wrapper::after {
            content: '\f078';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            pointer-events: none;
        }

        .filter-form select {
            appearance: none;
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(67, 97, 238, 0.3);
            border-radius: var(--border-radius-sm);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #333;
            transition: var(--transition);
            cursor: pointer;
        }

        .filter-form select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .filter-form button {
            padding: 12px 25px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-form button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .report-item {
            background: white;
            padding: 25px 20px;
            border-radius: var(--border-radius-sm);
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .report-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .report-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .report-item:nth-child(1)::before {
            background: linear-gradient(to right, #4CAF50, #8BC34A);
        }

        .report-item:nth-child(2)::before {
            background: linear-gradient(to right, #FFC107, #FF9800);
        }

        .report-item:nth-child(3)::before {
            background: linear-gradient(to right, #F44336, #E91E63);
        }

        .report-item:nth-child(4)::before {
            background: linear-gradient(to right, #2196F3, #03A9F4);
        }

        .report-item .icon {
            font-size: 32px;
            margin-bottom: 15px;
            background: rgba(67, 97, 238, 0.1);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
        }

        .report-item:nth-child(1) .icon {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }

        .report-item:nth-child(2) .icon {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        .report-item:nth-child(3) .icon {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .report-item:nth-child(4) .icon {
            background: rgba(33, 150, 243, 0.1);
            color: #2196F3;
        }

        .report-item strong {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        .report-item .amount {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }

        .print-button {
            display: block;
            width: 200px;
            margin: 30px auto 0;
            padding: 15px 30px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .print-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            z-index: -1;
        }

        .print-button:hover::before {
            width: 100%;
        }

        .print-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }

        .print-button i {
            margin-right: 8px;
        }

        /* Untuk print */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                max-width: 100%;
            }
            
            .filter-form, .print-button {
                display: none;
            }
            
            .header {
                color: black;
                background: white;
            }
            
            .header::before {
                display: none;
            }
            
            .report-item {
                box-shadow: none;
                border: 1px solid #eee;
            }
            
            .report-item::before {
                display: none;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .report-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-form {
                flex-direction: column;
            }
            
            .select-wrapper {
                width: 100%;
            }
            
            .filter-form button {
                width: 100%;
                justify-content: center;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .report-item {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .report-item:nth-child(2) {
            animation-delay: 0.1s;
        }

        .report-item:nth-child(3) {
            animation-delay: 0.2s;
        }

        .report-item:nth-child(4) {
            animation-delay: 0.3s;
        }
        .home-section {
        position: relative;
        min-height: 100vh;
        width: calc(80% - 78px);
        left: 60px;
        transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="home-section">
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Laporan Keuangan Bendahara</h1>
        </div>
        <div class="content">
            <form class="filter-form" method="GET" action="">
                <div class="select-wrapper">
                    <select name="bulan">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i; ?>" <?= $i == $bulan ? 'selected' : ''; ?>><?= date('F', mktime(0, 0, 0, $i, 10)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="select-wrapper">
                    <select name="tahun">
                        <?php for ($i = date('Y'); $i >= 2000; $i--): ?>
                            <option value="<?= $i; ?>" <?= $i == $tahun ? 'selected' : ''; ?>><?= $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit"><i class="fas fa-filter"></i> Tampilkan</button>
            </form>
            
            <div class="report-grid">
                <div class="report-item">
                    <div class="icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <strong>Total Setoran</strong>
                    <div class="amount">Rp <?= number_format($total_setoran, 0, ',', '.'); ?></div>
                </div>
                <div class="report-item">
                    <div class="icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <strong>Total Penarikan</strong>
                    <div class="amount">Rp <?= number_format($total_penarikan, 0, ',', '.'); ?></div>
                </div>
                <div class="report-item">
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <strong>Total Pengeluaran</strong>
                    <div class="amount">Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?></div>
                </div>
                <div class="report-item">
                    <div class="icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <strong>Saldo Akhir</strong>
                    <div class="amount">Rp <?= number_format($saldo_akhir, 0, ',', '.'); ?></div>
                </div>
            </div>
            
            <button class="print-button" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>
    </div>
    </div>
</body>
</html>