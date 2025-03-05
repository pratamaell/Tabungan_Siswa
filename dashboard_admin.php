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

$query_saldo = "SELECT 
                    (SUM(s.saldo) - COALESCE((SELECT SUM(nominal) FROM pengeluaran), 0)) AS total_saldo 
                FROM siswa s";
$stmt_saldo = $db->prepare($query_saldo);
$stmt_saldo->execute();
$total_saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC);


// Ambil data setoran & penarikan per bulan
$query_tabungan_per_bulan = "
    SELECT 
        DATE_FORMAT(tanggal, '%Y-%m') AS bulan,
        SUM(CASE WHEN jenis = 'setoran' THEN nominal ELSE 0 END) AS total_tabungan,
        SUM(CASE WHEN jenis = 'penarikan' THEN nominal ELSE 0 END) AS total_penarikan
    FROM transaksi
    GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
    ORDER BY DATE_FORMAT(tanggal, '%Y-%m')";
$stmt_tabungan_per_bulan = $db->prepare($query_tabungan_per_bulan);
$stmt_tabungan_per_bulan->execute();
$tabungan_per_bulan = $stmt_tabungan_per_bulan->fetchAll(PDO::FETCH_ASSOC);

// Replace the existing username assignment (around line 45) with:
$query_admin = "SELECT name FROM users WHERE id = ? AND role = 'admin'";
$stmt_admin = $db->prepare($query_admin);
$stmt_admin->execute([$_SESSION['user_id']]);
$admin_data = $stmt_admin->fetch(PDO::FETCH_ASSOC);
$username = $admin_data['name'] ?? 'Admin';



$labels = array_column($tabungan_per_bulan, 'bulan');
$totalTabungan = array_column($tabungan_per_bulan, 'total_tabungan');
$totalPenarikan = array_column($tabungan_per_bulan, 'total_penarikan');

// Jika data kosong, berikan nilai default
if (empty($labels)) {
    $labels = ['Tidak Ada Data'];
    $totalTabungan = [0];
    $totalPenarikan = [0];
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Admin Dashboard</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #4cc9f0;
            --accent-color: #3a0ca3;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --transition: all 0.3s ease;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --card-shadow: 0 8px 20px rgba(67, 97, 238, 0.15);
            --border-radius: 16px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #ebedfa 100%);
            color: var(--dark-color);
            font-size: 15px;
            line-height: 1.6;
        }
        
        .home-section {
            position: relative;
            min-height: 100vh;
            top: 0;
            left: 78px;
            width: calc(100% - 78px);
            transition: var(--transition);
            z-index: 2;
            padding: 25px;
        }
        
        .sidebar.open ~ .home-section {
            left: 250px;
            width: calc(100% - 250px);
        }
        
        .header {
            background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
            padding: 25px;
            text-align: center;
            color: white;
            font-size: 28px;
            font-weight: 600;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(rgba(255, 255, 255, 0.1), transparent);
            opacity: 0.6;
            pointer-events: none;
        }
        
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            color: var(--dark-color);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            z-index: -1;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 30px rgba(67, 97, 238, 0.2);
        }
        
        .card-icon {
            font-size: 42px;
            margin-bottom: 15px;
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.08);
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .card h3 {
            margin-bottom: 15px;
            font-size: 22px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .card p {
            margin: 0;
            font-size: 16px;
            color: #555;
        }
        
        .card .value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 10px 0;
        }
        
        .info-card {
            text-align: center;
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            transition: var(--transition);
            border-left: 5px solid var(--warning-color);
        }
        
        .info-card:hover {
            transform: scale(1.02);
        }
        
        .info-card h3 {
            font-size: 20px;
            color: var(--warning-color);
            margin-bottom: 15px;
        }
        
        .info-card p {
            color: #555;
        }
        
        .chart-container {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            position: relative;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .chart-actions select {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-family: 'Poppins', sans-serif;
            outline: none;
        }
        
        @media (max-width: 768px) {
            .home-section {
                left: 0;
                width: 100%;
                padding: 15px;
            }
            
            .card-container {
                grid-template-columns: 1fr;
            }
            
            .header {
                font-size: 22px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="home-section">
        <div class="header">
            Dashboard Admin
        </div>
        
        <div class="card-container">
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3>Statistik Siswa</h3>
                <div class="value"><?php echo $total_siswa['total_siswa']; ?></div>
                <p>Total siswa yang terdaftar</p>
            </div>
            
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-school"></i>
                </div>
                <h3>Statistik Kelas</h3>
                <div class="value"><?php echo $total_kelas['total_kelas']; ?></div>
                <p>Total kelas yang tersedia</p>
            </div>
            
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3>Keuangan</h3>
                <div class="value">Rp <?php echo number_format($total_saldo['total_saldo'], 0, ',', '.'); ?></div>
                <p>Total saldo keseluruhan</p>
            </div>
        </div>
        
        <div class="info-card">
            <h3><i class="fas fa-info-circle"></i> Informasi Penting</h3>
            <p>Pastikan semua data siswa dan kelas telah diperbarui. Pembaruan terakhir mempengaruhi laporan keuangan.</p>
        </div>
        
        <div class="chart-container">
            <div class="chart-header">
                <div class="chart-title">Grafik Transaksi Bulanan</div>
                <div class="chart-actions">
                    <select id="chartType" onchange="updateChartType()">
                        <option value="line">Line Chart</option>
                        <option value="bar">Bar Chart</option>
                    </select>
                </div>
            </div>
            <canvas id="tabunganChart"></canvas>
        </div>
    </div>
    
    <script>
    // Data untuk grafik
    const labels = <?php echo json_encode(array_column($tabungan_per_bulan, 'bulan')); ?>;
    const totalTabungan = <?php echo json_encode(array_column($tabungan_per_bulan, 'total_tabungan')); ?>;
    const totalPenarikan = <?php echo json_encode(array_column($tabungan_per_bulan, 'total_penarikan')); ?>;

    let tabunganChart;

    function initChart(type = 'line') {
        const data = {
            labels: labels,
            datasets: [
                {
                    label: 'Total Setoran',
                    backgroundColor: 'rgba(67, 97, 238, 0.2)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    data: totalTabungan,
                    fill: type === 'line' ? true : false,
                    tension: 0.4
                },
                {
                    label: 'Total Penarikan',
                    backgroundColor: 'rgba(76, 201, 240, 0.2)',
                    borderColor: 'rgba(76, 201, 240, 1)',
                    data: totalPenarikan,
                    fill: type === 'line' ? true : false,
                    tension: 0.4
                }
            ]
        };

        // Konfigurasi untuk grafik
        const config = {
            type: type,
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#212529',
                        bodyColor: '#212529',
                        bodyFont: {
                            family: "'Poppins', sans-serif"
                        },
                        titleFont: {
                            family: "'Poppins', sans-serif",
                            weight: 'bold'
                        },
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 15,
                        caretSize: 7,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan',
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Total Nominal (Rp)',
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumSignificantDigits: 3 }).format(value);
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        };

        // Inisialisasi grafik
        if (tabunganChart) {
            tabunganChart.destroy();
        }
        
        tabunganChart = new Chart(
            document.getElementById('tabunganChart'),
            config
        );
    }

    function updateChartType() {
        const chartType = document.getElementById('chartType').value;
        initChart(chartType);
    }

    // Inisialisasi grafik saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        initChart('line');
    });

    // SweetAlert welcome message
Swal.fire({
    position: 'top-end',
    icon: 'success',
    title: 'Selamat datang, <?php echo $username; ?>!',
    html: `
        <div>
            <p>Anda berhasil login sebagai admin.</p>
            <small>Username: <?php echo $username; ?><br>
            Waktu Login: ${new Date().toLocaleString('id-ID', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            })}</small>
        </div>
    `,
    showConfirmButton: false,
    timer: 10000,
    toast: true,
    background: '#f0f0f0',
    iconColor: '#4361ee',
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});
</script>

</body>
</html>