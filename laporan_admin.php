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
$query_pengeluaran = "SELECT SUM(nominal) AS total_pengeluaran FROM pengeluaran WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
$stmt_pengeluaran = $conn->prepare($query_pengeluaran);
$stmt_pengeluaran->bindParam(':bulan', $bulan);
$stmt_pengeluaran->bindParam(':tahun', $tahun);
$stmt_pengeluaran->execute();
$result_pengeluaran = $stmt_pengeluaran->fetch(PDO::FETCH_ASSOC);
$total_pengeluaran = $result_pengeluaran['total_pengeluaran'] ?? 0;

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

// Nama bulan dalam Bahasa Indonesia
$nama_bulan = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --danger-color: #e63946;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
            color: var(--dark-color);
        }
        
        .dashboard-container {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
            margin-left: 100px;
            transition: margin-left 0.5s;
        }
        
        .page-header {
            margin-bottom: 30px;
            position: relative;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .page-subtitle {
            font-size: 16px;
            color: var(--gray-color);
            margin-bottom: 25px;
        }
        
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 25px;
            margin-bottom: 30px;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .filter-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .form-group {
            position: relative;
        }
        
        .form-group label {
            position: absolute;
            top: -10px;
            left: 10px;
            background: white;
            padding: 0 5px;
            font-size: 12px;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .filter-form select, 
        .filter-form button {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            background: white;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: var(--transition);
        }
        
        .filter-form select {
            min-width: 140px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }
        
        .filter-form select:hover,
        .filter-form button:hover {
            border-color: var(--primary-color);
        }
        
        .filter-form select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .filter-form button {
            background: var(--primary-color);
            color: white;
            cursor: pointer;
            font-weight: 500;
            padding: 12px 20px;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-form button:hover {
            background: var(--secondary-color);
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 25px;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            min-height: 140px;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 8px;
            height: 100%;
            border-radius: 4px 0 0 4px;
        }
        
        .stat-card.deposit::before {
            background: var(--primary-color);
        }
        
        .stat-card.withdrawal::before {
            background: var(--warning-color);
        }
        
        .stat-card.expense::before {
            background: var(--danger-color);
        }
        
        .stat-card.balance::before {
            background: var(--success-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .stat-title {
            font-size: 14px;
            color: var(--gray-color);
            margin-bottom: 8px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-left: 15px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            padding-left: 15px;
        }
        
        .deposit .stat-value {
            color: var(--primary-color);
        }
        
        .withdrawal .stat-value {
            color: var(--warning-color);
        }
        
        .expense .stat-value {
            color: var(--danger-color);
        }
        
        .balance .stat-value {
            color: var(--success-color);
        }
        
        .stat-icon {
            position: absolute;
            right: 20px;
            bottom: 20px;
            font-size: 48px;
            opacity: 0.1;
            color: var(--dark-color);
        }
        
        .actions-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .button {
            padding: 12px 24px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            text-decoration: none;
            font-size: 14px;
        }
        
        .button-primary {
            background: var(--primary-color);
            color: white;
            border: none;
        }
        
        .button-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .button-outline {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .button-outline:hover {
            background: rgba(67, 97, 238, 0.1);
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        thead th {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
            font-size: 14px;
        }
        
        tbody tr {
            background: white;
            border-bottom: 1px solid #eee;
            transition: var(--transition);
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        tbody tr:last-child {
            border-bottom: none;
        }
        
        tbody td {
            padding: 15px;
            font-size: 14px;
            color: var(--dark-color);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .pagination a {
            padding: 8px 16px;
            margin: 0 5px;
            background: white;
            color: var(--dark-color);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .pagination a:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
        }
        
        .pagination a.active {
            background: var(--primary-color);
            color: white;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background: rgba(76, 201, 240, 0.2);
            color: var(--success-color);
        }
        
        .badge-danger {
            background: rgba(247, 37, 133, 0.2);
            color: var(--warning-color);
        }
        
        /* Custom print styles */
        /* Add this CSS in the existing <style> tag */

@media print {
    body {
        background: white;
        padding: 20px;
        font-size: 12px;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .dashboard-container {
        padding: 0;
        margin-left: 0 !important;
        max-width: 100%;
    }
    
    .home-section {
        width: 100% !important;
        left: 0 !important;
    }
    
    /* Hide unnecessary elements */
    .filter-section,
    .button-outline,
    .pagination,
    .sidebar,
    nav {
        display: none !important;
    }
    
    /* Header styling */
    .page-header {
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #4361ee;
        padding-bottom: 15px;
    }
    
    .page-title {
        color: #4361ee !important;
        font-size: 24px;
        margin-bottom: 5px;
    }
    
    .page-subtitle {
        font-size: 14px;
        color: #666;
    }
    
    /* Stats cards styling */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin: 20px 0;
        page-break-inside: avoid;
    }
    
    .stat-card {
        border: 1px solid #ddd;
        padding: 15px;
        background-color: #f8f9fa !important;
        min-height: auto;
        page-break-inside: avoid;
    }
    
    .stat-card::before {
        display: none;
    }
    
    .stat-title {
        color: #666 !important;
        font-size: 12px;
        margin-bottom: 5px;
    }
    
    .stat-value {
        font-size: 18px;
        font-weight: bold;
    }
    
    /* Table styling */
    table {
        width: 100%;
        margin-top: 20px;
        border: 1px solid #ddd;
    }
    
    thead th {
        background-color: #4361ee !important;
        color: white !important;
        padding: 10px;
        font-size: 12px;
    }
    
    tbody td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
        font-size: 11px;
    }
    
    /* Footer styling */
    .print-footer {
        display: block !important;
        margin-top: 30px;
        text-align: right;
        font-size: 11px;
        page-break-inside: avoid;
    }
    
    /* Add page numbering */
    @page {
        margin: 1cm;
    }
    
    .page-break {
        page-break-before: always;
    }
}
        
        /* Responsive styling */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 20px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .filter-section {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-form {
                margin-bottom: 15px;
                width: 100%;
            }
            
            .form-group {
                flex-grow: 1;
            }
            
            .form-group:first-child {
                min-width: 100%;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

            .home-section {
            position: relative;
            min-height: 100vh;
            width: calc(100% - 58px);
            left: 58px;
            transition: all 0.3s ease;
            }
    </style>
</head>
<body>
    <div class="home-section">
    <div class="dashboard-container">
        <div class="page-header">
            <h1 class="page-title">Laporan Keuangan</h1>
            <p class="page-subtitle">Periode <?= $nama_bulan[(int)$bulan] ?> <?= $tahun ?></p>
        </div>
        
        <div class="card">
            <div class="filter-section">
                <form class="filter-form" method="GET">
                    <div class="form-group">
                        <label for="bulan">Bulan</label>
                        <select name="bulan" id="bulan">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i; ?>" <?= $i == $bulan ? 'selected' : ''; ?>><?= $nama_bulan[$i]; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tahun">Tahun</label>
                        <select name="tahun" id="tahun">
                            <?php for ($i = date('Y'); $i >= 2000; $i--): ?>
                                <option value="<?= $i; ?>" <?= $i == $tahun ? 'selected' : ''; ?>><?= $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit"><i class="fas fa-filter"></i> Tampilkan</button>
                </form>
                
                <button class="button button-outline" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>
            
            <div class="stats-container">
                <div class="stat-card deposit">
                    <div class="stat-title">Total Setoran</div>
                    <div class="stat-value">Rp <?= number_format($total_setoran, 0, ',', '.'); ?></div>
                    <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
                </div>
                
                <div class="stat-card withdrawal">
                    <div class="stat-title">Total Penarikan</div>
                    <div class="stat-value">Rp <?= number_format($total_penarikan, 0, ',', '.'); ?></div>
                    <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
                </div>
                <br>
                <div class="stat-card expense">
                    <div class="stat-title">Total Pengeluaran</div>
                    <div class="stat-value">Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?></div>
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                </div>
                
                <div class="stat-card balance">
                    <div class="stat-title">Saldo Akhir</div>
                    <div class="stat-value">Rp <?= number_format($saldo_akhir, 0, ',', '.'); ?></div>
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                </div>
            </div>
            
            <?php if (count($transaksi) > 0): ?>
                <div class="section-title">Daftar Transaksi</div>
                
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = ($page - 1) * $limit + 1;
                        foreach ($transaksi as $row): 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                            <td>Rp <?= number_format($row['nominal'], 0, ',', '.'); ?></td>
                            <td><?= $row['keterangan'] ?? '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&page=<?= $page - 1 ?>">
                                <i class="fas fa-angle-left"></i> Prev
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&page=<?= $i ?>" 
                               class="<?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&page=<?= $page + 1 ?>">
                                Next <i class="fas fa-angle-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 30px;">
                    <i class="fas fa-info-circle" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                    <p>Tidak ada transaksi untuk periode ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Add this before closing card div -->
<div class="print-footer" style="display: none;">
    <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
    <p>Oleh: <?= $_SESSION['username'] ?? 'Admin' ?></p>
</div>
    </div>
</body>
</html>