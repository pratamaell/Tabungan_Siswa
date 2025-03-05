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
    $queryTotal = "SELECT 
                    (SUM(s.saldo) - COALESCE((SELECT SUM(nominal) FROM pengeluaran), 0)) AS total_tabungan 
                FROM siswa s";
    $stmt = $db->prepare($queryTotal);
    $stmt->execute();
    $totalTabungan = $stmt->fetch(PDO::FETCH_ASSOC)['total_tabungan'] ?? 0;
    
    // Ambil jumlah siswa dengan tabungan
    $queryJumlahSiswa = "SELECT COUNT(*) AS jumlah_siswa FROM siswa WHERE saldo > 0";
    $stmt = $db->prepare($queryJumlahSiswa);
    $stmt->execute();
    $jumlahSiswa = $stmt->fetch(PDO::FETCH_ASSOC)['jumlah_siswa'] ?? 0;
    
    // Ambil total setoran bulan ini
    $querySetoranBulanIni = "SELECT SUM(nominal) AS total_setoran 
                             FROM transaksi 
                             WHERE jenis = 'setoran' 
                             AND MONTH(created_at) = MONTH(CURRENT_DATE())
                             AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    $stmt = $db->prepare($querySetoranBulanIni);
    $stmt->execute();
    $totalSetoranBulanIni = $stmt->fetch(PDO::FETCH_ASSOC)['total_setoran'] ?? 0;
    
    // Ambil total penarikan bulan ini
    $queryPenarikanBulanIni = "SELECT SUM(nominal) AS total_penarikan 
                              FROM penarikan 
                              WHERE status = 'approved' 
                              AND MONTH(created_at) = MONTH(CURRENT_DATE())
                              AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    $stmt = $db->prepare($queryPenarikanBulanIni);
    $stmt->execute();
    $totalPenarikanBulanIni = $stmt->fetch(PDO::FETCH_ASSOC)['total_penarikan'] ?? 0;

    // Ambil riwayat transaksi (setoran + penarikan)
    $queryTransactions = "
    SELECT t.id, u.name, t.nominal, 'Setoran' AS jenis, t.created_at 
    FROM transaksi t
    JOIN siswa s ON t.siswa_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE t.jenis = 'setoran'

    UNION

    SELECT p.id, u.name, p.nominal, 'Penarikan' AS jenis, p.created_at 
    FROM penarikan p
    JOIN siswa s ON p.siswa_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE p.status = 'approved' 

    ORDER BY created_at DESC";

    $stmt = $db->prepare($queryTransactions);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Add these lines after database connection
$limit = 6; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Modify the transaction query to include pagination
$queryTransactions = "
    (SELECT t.id, u.name, t.nominal, 'Setoran' AS jenis, t.created_at 
    FROM transaksi t
    JOIN siswa s ON t.siswa_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE t.jenis = 'setoran')
    
    UNION
    
    (SELECT p.id, u.name, p.nominal, 'Penarikan' AS jenis, p.created_at 
    FROM penarikan p
    JOIN siswa s ON p.siswa_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE p.status = 'approved')
    
    ORDER BY created_at DESC
    LIMIT :start, :limit";

$stmt = $db->prepare($queryTransactions);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$queryCount = "
    SELECT COUNT(*) as total FROM (
        SELECT id FROM transaksi WHERE jenis = 'setoran'
        UNION ALL
        SELECT id FROM penarikan WHERE status = 'approved'
    ) as combined_transactions";
$stmt = $db->prepare($queryCount);
$stmt->execute();
$total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bendahara Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Variables */
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --success: #0bb967;
            --warning: #f7b731;
            --danger: #fc5c65;
            --dark: #1e293b;
            --gray: #64748b;
            --light: #f8fafc;
            --white: #ffffff;
            --shadow: 0 10px 25px rgba(67, 97, 238, 0.07);
            --card-shadow: 0 15px 35px rgba(67, 97, 238, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --border-radius: 16px;
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #ebedfa 100%);
            color: var(--dark);
            font-size: 15px;
            line-height: 1.6;
        }

        /* Layout */
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

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        /* Header */
        .dashboard-header {
            background: linear-gradient(120deg, var(--primary), var(--primary-light));
            padding: 35px 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            color: var(--white);
        }

        .dashboard-header::before {
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

        .header-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .header-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header-content p {
            font-size: 1.1rem;
            font-weight: 400;
            opacity: 0.9;
        }

        /* Stats Cards */
        .stats-container {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            text-align: center;
            transition: var(--transition);
            position: relative;
            flex: 1;
            min-width: 250px;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 5px;
            width: 100%;
        }

        .stat-card:nth-child(1)::before {
            background: linear-gradient(to right, var(--success), #4cc9f0);
        }
        
        .stat-card:nth-child(2)::before {
            background: linear-gradient(to right, var(--primary), var(--accent));
        }
        
        .stat-card:nth-child(3)::before {
            background: linear-gradient(to right, var(--warning), #ff9e40);
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(67, 97, 238, 0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 15px;
            transition: var(--transition);
        }
        
        .stat-card:nth-child(1) .stat-icon {
            color: var(--success);
            background: rgba(11, 185, 103, 0.1);
        }
        
        .stat-card:nth-child(2) .stat-icon {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
        }
        
        .stat-card:nth-child(3) .stat-icon {
            color: var(--warning);
            background: rgba(247, 183, 49, 0.1);
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1);
        }

        .stat-card:nth-child(1):hover .stat-icon {
            background: rgba(11, 185, 103, 0.15);
        }
        
        .stat-card:nth-child(2):hover .stat-icon {
            background: rgba(67, 97, 238, 0.15);
        }
        
        .stat-card:nth-child(3):hover .stat-icon {
            background: rgba(247, 183, 49, 0.15);
        }

        .stat-card h2 {
            font-size: 1.25rem;
            color: var(--gray);
            margin-bottom: 10px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-card:nth-child(1) .stat-value {
            color: var(--success);
        }
        
        .stat-card:nth-child(2) .stat-value {
            color: var(--primary);
        }
        
        .stat-card:nth-child(3) .stat-value {
            color: var(--warning);
        }

        /* Transaction History */
        .transaction-section {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 30px;
            margin-bottom: 40px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(100, 116, 139, 0.1);
            padding-bottom: 15px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
            color: var(--primary);
        }

        /* Table Styling */
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transaction-table th {
            background: rgba(67, 97, 238, 0.05);
            color: var(--dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
        }

        .transaction-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(100, 116, 139, 0.1);
            color: var(--gray);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .transaction-table tr:hover td {
            background: rgba(67, 97, 238, 0.02);
            color: var(--dark);
        }

        .transaction-table tr:last-child td {
            border-bottom: none;
        }

        .amount {
            font-weight: 600;
            color: var(--primary);
        }

        .date {
            color: var(--gray);
            font-size: 0.85rem;
        }

        .empty-transactions {
            text-align: center;
            padding: 50px 0;
            color: var(--gray);
            font-style: italic;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .stats-container {
                flex-direction: column;
            }
            
            .stat-card {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .home-section {
                left: 0;
                width: 100%;
                padding: 15px;
            }

            .container {
                padding: 10px;
            }

            .dashboard-header {
                padding: 25px 20px;
            }

            .header-content h1 {
                font-size: 1.8rem;
            }

            .header-content p {
                font-size: 1rem;
            }

            .transaction-section {
                padding: 20px 15px;
            }

            .section-title {
                font-size: 1.2rem;
            }

            .transaction-table th, 
            .transaction-table td {
                padding: 12px 10px;
                font-size: 0.9rem;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dashboard-header,
        .stats-container,
        .transaction-section {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .stats-container .stat-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stats-container .stat-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stats-container .stat-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .transaction-section {
            animation-delay: 0.4s;
        }

        /* Pagination Styles */
            .pagination-container {
                display: flex;
                justify-content: center;
                margin-top: 2rem;
                gap: 0.5rem;
            }

            .pagination-button {
                padding: 0.75rem 1.25rem;
                border-radius: 12px;
                background: var(--white);
                color: var(--primary);
                border: 1px solid rgba(67, 97, 238, 0.1);
                font-weight: 500;
                transition: var(--transition);
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .pagination-button:hover {
                background: var(--primary);
                color: var(--white);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
            }

            .pagination-button.active {
                background: linear-gradient(135deg, var(--primary), var(--primary-light));
                color: var(--white);
                border: none;
            }

            .pagination-button.disabled {
                opacity: 0.5;
                cursor: not-allowed;
                pointer-events: none;
            }
    </style>
</head>
<body>
    <div class="home-section">
        <div class="container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="header-content">
                    <h1>Dashboard Bendahara</h1>
                    <p>Selamat datang, <?= htmlspecialchars($bendahara['name']); ?>! Kelola tabungan siswa dengan mudah.</p>
                </div>
            </div>

            <!-- Statistik Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2>Jumlah Siswa</h2>
                    <div class="stat-value"><?= number_format($jumlahSiswa, 0, ',', '.'); ?></div>
                    <p>Siswa dengan tabungan aktif</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h2>Total Tabungan</h2>
                    <div class="stat-value">Rp <?= number_format($totalTabungan, 0, ',', '.'); ?></div>
                    <p>Saldo keseluruhan siswa</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h2>Transaksi Bulan Ini</h2>
                    <div class="stat-value">Rp <?= number_format($totalSetoranBulanIni - $totalPenarikanBulanIni, 0, ',', '.'); ?></div>
                    <p>Selisih setoran & penarikan</p>
                </div>
            </div>

            <!-- Riwayat Transaksi -->
            <div class="transaction-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-exchange-alt"></i>
                        Riwayat Transaksi
                    </h2>
                </div>
                
                <?php if (count($transactions) > 0): ?>
                    <div class="table-responsive">
                        <table class="transaction-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Jumlah</th>
                                    <th>Jenis</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $index => $transaction): ?>
                                    <tr>
                                        <td><?= $index + 1; ?></td>
                                        <td>
                                            <div class="user-info">
                                                <?= htmlspecialchars($transaction['name']); ?>
                                            </div>
                                        </td>
                                        <td class="amount">Rp <?= number_format($transaction['nominal'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="<?= $transaction['jenis'] === 'Setoran' ? 'text-success' : 'text-warning' ?>">
                                                <?= $transaction['jenis']; ?>
                                            </span>
                                        </td>
                                        <td class="date"><?= date('d-m-Y H:i', strtotime($transaction['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-transactions">
                        <i class="fas fa-info-circle"></i> Belum ada transaksi tercatat.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>" class="pagination-button">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="pagination-button <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page+1 ?>" class="pagination-button">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Selamat datang, <?= htmlspecialchars($bendahara['name']); ?>!',
            html: `
                <div>
                    <p>Anda berhasil login sebagai Bendahara.</p>
                    <small>Username: <?= htmlspecialchars($bendahara['name']); ?><br>
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
    });
</script>
</body>
</html>