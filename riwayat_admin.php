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

// Search filters
// Search filters
$search_name = isset($_GET['search_name']) ? $_GET['search_name'] : '';
$transaction_type = isset($_GET['transaction_type']) ? $_GET['transaction_type'] : '';
$selected_month = isset($_GET['month']) ? $_GET['month'] : ''; // Add this line
// Pagination settings
$limit = 10; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Build the filter conditions
$where_conditions = [];
$params = [];

if (!empty($search_name)) {
    $where_conditions[] = "u.name LIKE :search_name";
    $params[':search_name'] = "%$search_name%";
}

if (!empty($selected_month)) {
    $where_conditions[] = "DATE_FORMAT(tanggal, '%Y-%m') = :selected_month";
    $params[':selected_month'] = $selected_month;
}

// Transaction type filter conditions
$type_filter_sql = "";
if ($transaction_type === 'Setoran') {
    $type_filter_sql = "HAVING jenis = 'Setoran'";
} elseif ($transaction_type === 'Penarikan') {
    $type_filter_sql = "HAVING jenis = 'Penarikan'";
}

// Create WHERE clause for the main query
$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Modify the main query to include filters
$query = "
    SELECT SQL_CALC_FOUND_ROWS
        t.nomor, 
        t.tanggal, 
        t.nominal, 
        t.keterangan, 
        u.name AS nama_siswa, 
        'Setoran' AS jenis,
        k.nama_kelas
    FROM transaksi t
    INNER JOIN siswa s ON t.siswa_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    LEFT JOIN kelas k ON s.kelas_id = k.id
    $where_clause
    UNION ALL
    SELECT 
        p.nomor, 
        p.tanggal, 
        p.nominal, 
        p.status AS keterangan, 
        u.name AS nama_siswa, 
        'Penarikan' AS jenis,
        k.nama_kelas
    FROM penarikan p
    INNER JOIN siswa s ON p.siswa_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    LEFT JOIN kelas k ON s.kelas_id = k.id
    $where_clause
    $type_filter_sql
    ORDER BY tanggal DESC
    LIMIT :start, :limit";

$stmt = $conn->prepare($query);

// Bind parameters for the search filters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}

$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total records for pagination (with filters applied)
$stmt = $conn->query("SELECT FOUND_ROWS() as total");
$total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $limit);

// Buat nomor transaksi otomatis
$date = date("Ymd"); // Format YYYYMMDD
$query_last = "SELECT MAX(id) AS last_id FROM penarikan";
$stmt_last = $conn->prepare($query_last);
$stmt_last->execute();
$result_last = $stmt_last->fetch(PDO::FETCH_ASSOC);
$last_id = $result_last['last_id'] ?? 0;
$new_id = $last_id + 1;

// Format nomor transaksi
$nomor_transaksi = "PEN-$date-" . str_pad($new_id, 3, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi ADMIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #ff0a54;
            --light: #f8f9fa;
            --dark: #212529;
            --gradient: linear-gradient(135deg, #4361ee, #4cc9f0);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #f4f7fe;
            min-height: 100vh;
            padding: 2rem;
            margin:0;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            padding: 2rem;
        }

        .home-section {
            position: relative;
            min-height: 100vh;
            width: calc(100% - 78px);
            left: 78px;
            transition: all 0.3s ease;
        }

        h1 {
            color: var(--dark);
            font-size: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        th {
            background: var(--gradient);
            color: white;
            font-weight: 500;
            padding: 1rem;
            text-align: left;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-size: 0.9rem;
            color: var(--dark);
        }

        tr:hover {
            background: rgba(67, 97, 238, 0.05);
            transition: all 0.3s ease;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-print {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }

        .nominal {
            font-weight: 600;
            color: var(--primary);
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            text-align: center;
            min-width: 100px;
        }

        .badge-setoran {
            background: rgba(76, 201, 240, 0.15);
            color: #0bb377;
            border: 1px solid rgba(11, 179, 119, 0.3);
        }

        .badge-penarikan {
            background: rgba(247, 37, 133, 0.15);
            color: #f72585;
            border: 1px solid rgba(247, 37, 133, 0.3);
        }

        /* Hover effect untuk badge */
        .badge-setoran:hover {
            background: rgba(76, 201, 240, 0.25);
        }

        .badge-penarikan:hover {
            background: rgba(247, 37, 133, 0.25);
        }

        /* Filter form */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn-search {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }

        .btn-reset {
            background: white;
            color: var(--dark);
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-reset:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .page-btn {
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            background: white;
            color: var(--primary);
            border: 1px solid rgba(67, 97, 238, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .page-btn:hover {
            background: var(--gradient);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }

        .page-btn.active {
            background: var(--gradient);
            color: white;
            border: none;
        }

        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* No records message */
        .no-records {
            text-align: center;
            padding: 2rem;
            color: var(--dark);
            font-weight: 500;
            background: rgba(67, 97, 238, 0.05);
            border-radius: 10px;
            margin: 1rem 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 0.75rem;
            }

            .btn-print {
                padding: 0.4rem 0.8rem;
            }

            .filter-form {
                flex-direction: column;
                gap: 1rem;
            }

            .form-group {
                width: 100%;
            }
        }

        /* Custom Scrollbar */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="home-section">
        <div class="container">
            <h1>Riwayat Transaksi [Admin]</h1>
            
            <!-- Filter Form -->
            <form class="filter-form" method="GET" action="">
                <div class="form-group">
                    <label for="search_name" class="form-label">Cari Nama Siswa</label>
                    <input type="text" id="search_name" name="search_name" class="form-control" 
                           placeholder="Masukkan nama siswa" value="<?= htmlspecialchars($search_name) ?>">
                </div>
                
                <div class="form-group">
                    <label for="transaction_type" class="form-label">Jenis Transaksi</label>
                    <select id="transaction_type" name="transaction_type" class="form-control">
                        <option value="" <?= $transaction_type === '' ? 'selected' : '' ?>>Semua</option>
                        <option value="Setoran" <?= $transaction_type === 'Setoran' ? 'selected' : '' ?>>Setoran</option>
                        <option value="Penarikan" <?= $transaction_type === 'Penarikan' ? 'selected' : '' ?>>Penarikan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="month" class="form-label">Pilih Bulan</label>
                    <input type="month" id="month" name="month" class="form-control" 
                        value="<?= htmlspecialchars($selected_month) ?>">
                </div>
                            
                <div class="form-group" style="display: flex; align-items: flex-end; gap: 1rem;">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="riwayat_admin.php" class="btn-reset">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
            
            <div class="table-container">
                <?php if (count($riwayat) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nomor Transaksi</th>
                            <th>Nama Siswa</th>
                            <th>Tanggal</th>
                            <th>Nominal</th>
                            <th>Deskripsi</th>
                            <th>Jenis</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nomor'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['nama_siswa'] ?? ''); ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                <td class="nominal">Rp <?= number_format($row['nominal'] ?? 0, 2, ',', '.'); ?></td>
                                <td><?= htmlspecialchars($row['keterangan'] ?? ''); ?></td>
                                <td>
                                    <span class="badge <?= ($row['jenis'] == 'Setoran') ? 'badge-setoran' : 'badge-penarikan' ?>">
                                        <?= htmlspecialchars($row['jenis']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="btn-print" onclick="printReceipt('<?= htmlspecialchars($row['nomor']); ?>')">
                                        <i class="fa-solid fa-print"></i>
                                        Print
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-records">
                    <i class="fas fa-info-circle"></i> Tidak ada data transaksi yang ditemukan.
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination with filter parameters preserved -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page-1 ?>&search_name=<?= urlencode($search_name) ?>&transaction_type=<?= urlencode($transaction_type) ?>" class="page-btn">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>

                <?php
                // Calculate range of pages to show
                $range = 2;
                $start_page = max(1, $page - $range);
                $end_page = min($total_pages, $page + $range);

                if ($start_page > 1): ?>
                    <a href="?page=1&search_name=<?= urlencode($search_name) ?>&transaction_type=<?= urlencode($transaction_type) ?>" class="page-btn">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="page-btn disabled">...</span>
                    <?php endif; ?>
                <?php endif;

                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=<?= $i ?>&search_name=<?= urlencode($search_name) ?>&transaction_type=<?= urlencode($transaction_type) ?>" 
                       class="page-btn <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor;

                if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="page-btn disabled">...</span>
                    <?php endif; ?>
                    <a href="?page=<?= $total_pages ?>&search_name=<?= urlencode($search_name) ?>&transaction_type=<?= urlencode($transaction_type) ?>" class="page-btn"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?>&search_name=<?= urlencode($search_name) ?>&transaction_type=<?= urlencode($transaction_type) ?>" class="page-btn">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function printReceipt(nomor) {
            window.open('generate_receipt.php?nomor=' + encodeURIComponent(nomor), '_blank');
        }
    </script>
</body>
</html>