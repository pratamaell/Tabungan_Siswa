<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';
include 'navbar_admin.php';

$database = new Database();
$db = $database->getConnection();

// Hitung jumlah siswa
$query_siswa = "SELECT COUNT(*) as total_siswa FROM users WHERE role = 'siswa'";
$stmt_siswa = $db->prepare($query_siswa);
$stmt_siswa->execute();
$total_siswa = $stmt_siswa->fetch(PDO::FETCH_ASSOC);

// Hitung jumlah kelas
$query_kelas = "SELECT COUNT(*) as total_kelas FROM kelas";
$stmt_kelas = $db->prepare($query_kelas);
$stmt_kelas->execute();
$total_kelas = $stmt_kelas->fetch(PDO::FETCH_ASSOC);

// Hitung total saldo semua siswa
$query_saldo = "SELECT SUM(nominal) as total_saldo FROM transaksi";
$stmt_saldo = $db->prepare($query_saldo);
$stmt_saldo->execute();
$total_saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

// Ambil data tabungan per bulan
$query_tabungan_per_bulan = "
    SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bulan, SUM(nominal) AS total_tabungan 
    FROM transaksi 
    GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
    ORDER BY DATE_FORMAT(tanggal, '%Y-%m')";
$stmt_tabungan_per_bulan = $db->prepare($query_tabungan_per_bulan);
$stmt_tabungan_per_bulan->execute();
$tabungan_per_bulan = $stmt_tabungan_per_bulan->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #dff9fb, #c7ecee);
            color: #333;
        }
        .home-section {
            position: relative;
            background: #E4E9F7;
            min-height: 100vh;
            top: 0;
            left: 78px;
            width: calc(100% - 78px);
            transition: all 0.5s ease;
            z-index: 2;
            padding: 20px;
        }
        .sidebar.open ~ .home-section {
            left: 250px;
            width: calc(100% - 250px);
        }
        .header {
            background: #74b9ff;
            padding: 20px;
            text-align: center;
            color: #fff;
            font-size: 24px;
            font-weight: bold;
            border-radius: 8px;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            flex: 1;
            min-width: 280px;
            background: linear-gradient(to bottom right, #ffffff, #dff9fb);
            color: #2d3436;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h3 {
            margin-bottom: 10px;
            font-size: 20px;
            color: #0984e3;
        }
        .card p {
            margin: 0;
            font-size: 16px;
        }
        .info-card {
            text-align: center;
            background: linear-gradient(to bottom right, #ffffff, #dff9fb);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            color: #333;
        }
        .info-card h3 {
            font-size: 18px;
            color: #74b9ff;
        }

        .chart-container {
            margin-top: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        @media (max-width: 768px) {
            .home-section {
                left: 0;
                width: 100%;
            }
            .card-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="home-section">
        <div class="header">Admin Dashboard</div>
        <div class="card-container">
            <div class="card">
                <h3>Statistik Siswa</h3>
                <p>Jumlah Siswa: <?php echo $total_siswa['total_siswa']; ?></p>
            </div>
            <div class="card">
                <h3>Statistik Kelas</h3>
                <p>Jumlah Kelas: <?php echo $total_kelas['total_kelas']; ?></p>
            </div>
            <div class="card">
                <h3>Keuangan</h3>
                <p>Total Saldo Keseluruhan: Rp <?php echo number_format($total_saldo['total_saldo'], 0, ',', '.'); ?></p>
            </div>
        </div>
        <br>
        <div class="info-card">
            <h3>Informasi Tambahan</h3>
            <p>Pastikan semua data siswa dan kelas telah diperbarui.</p>
        </div>
        <div class="chart-container">
            <canvas id="tabunganChart"></canvas>
        </div>
    </div>
    <script>
        // Data untuk grafik
        const labels = <?php echo json_encode(array_column($tabungan_per_bulan, 'bulan')); ?>;
        const data = {
            labels: labels,
            datasets: [{
                label: 'Total Tabungan',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                data: <?php echo json_encode(array_column($tabungan_per_bulan, 'total_tabungan')); ?>,
                fill: false,
            }]
        };

        // Konfigurasi untuk grafik
        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Total Tabungan (Rp)'
                        }
                    }
                }
            }
        };

        // Inisialisasi grafik
        const tabunganChart = new Chart(
            document.getElementById('tabunganChart'),
            config
        );
    </script>
</body>
</html>