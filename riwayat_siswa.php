<?php
session_start();
include 'config/database.php';
include 'navbar_siswa.php';

// Inisialisasi koneksi menggunakan PDO
$database = new Database();
$conn = $database->getConnection();

// Ambil data riwayat transaksi siswa
$siswa_id = $_SESSION['user_id'];

// Pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$filter_tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
$filter_tanggal_selesai = isset($_GET['tanggal_selesai']) ? $_GET['tanggal_selesai'] : '';

// Query untuk mendapatkan transaksi siswa dengan filter dan pagination
$query_transaksi = "
    SELECT 'setoran' AS jenis, t.nomor, t.nominal, t.tanggal, t.keterangan 
    FROM transaksi t
    JOIN siswa s ON t.siswa_id = s.id
    WHERE s.user_id = :user_id AND t.jenis = 'setoran'
    " . ($filter_jenis ? "AND t.jenis = :jenis " : "") . "
    " . ($filter_tanggal_mulai ? "AND t.tanggal >= :tanggal_mulai " : "") . "
    " . ($filter_tanggal_selesai ? "AND t.tanggal <= :tanggal_selesai " : "") . "
    
    UNION ALL
    
    SELECT 'penarikan' AS jenis, p.nomor, p.nominal, p.tanggal, p.status AS keterangan 
    FROM penarikan p
    JOIN siswa s ON p.siswa_id = s.id
    WHERE s.user_id = :user_id
    " . ($filter_jenis ? "AND 'penarikan' = :jenis " : "") . "
    " . ($filter_tanggal_mulai ? "AND p.tanggal >= :tanggal_mulai " : "") . "
    " . ($filter_tanggal_selesai ? "AND p.tanggal <= :tanggal_selesai " : "") . "
    
    ORDER BY tanggal DESC
    LIMIT :limit OFFSET :offset;
";

$stmt_transaksi = $conn->prepare($query_transaksi);
$stmt_transaksi->bindParam(':user_id', $siswa_id);
if ($filter_jenis) $stmt_transaksi->bindParam(':jenis', $filter_jenis);
if ($filter_tanggal_mulai) $stmt_transaksi->bindParam(':tanggal_mulai', $filter_tanggal_mulai);
if ($filter_tanggal_selesai) $stmt_transaksi->bindParam(':tanggal_selesai', $filter_tanggal_selesai);
$stmt_transaksi->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt_transaksi->bindParam(':offset', $offset, PDO::PARAM_INT);

// Debugging: Periksa apakah query mengembalikan hasil
try {
    $stmt_transaksi->execute();
} catch (PDOException $e) {
    echo "Error executing query: " . $e->getMessage();
    exit();
}

$transaksi = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);

// Hitung total data untuk pagination
$query_count = "
    SELECT COUNT(*) as total 
    FROM (
        SELECT t.id 
        FROM transaksi t
        JOIN siswa s ON t.siswa_id = s.id
        WHERE s.user_id = :user_id AND t.jenis = 'setoran'
        " . ($filter_jenis ? "AND t.jenis = :jenis " : "") . "
        " . ($filter_tanggal_mulai ? "AND t.tanggal >= :tanggal_mulai " : "") . "
        " . ($filter_tanggal_selesai ? "AND t.tanggal <= :tanggal_selesai " : "") . "
        UNION ALL
        SELECT p.id 
        FROM penarikan p
        JOIN siswa s ON p.siswa_id = s.id
        WHERE s.user_id = :user_id
        " . ($filter_jenis ? "AND 'penarikan' = :jenis " : "") . "
        " . ($filter_tanggal_mulai ? "AND p.tanggal >= :tanggal_mulai " : "") . "
        " . ($filter_tanggal_selesai ? "AND p.tanggal <= :tanggal_selesai " : "") . "
    ) as total_data;
";
$stmt_count = $conn->prepare($query_count);
$stmt_count->bindParam(':user_id', $siswa_id);
if ($filter_jenis) $stmt_count->bindParam(':jenis', $filter_jenis);
if ($filter_tanggal_mulai) $stmt_count->bindParam(':tanggal_mulai', $filter_tanggal_mulai);
if ($filter_tanggal_selesai) $stmt_count->bindParam(':tanggal_selesai', $filter_tanggal_selesai);

// Debugging: Periksa apakah query mengembalikan hasil
try {
    $stmt_count->execute();
} catch (PDOException $e) {
    echo "Error executing count query: " . $e->getMessage();
    exit();
}

$total_data = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_data / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --accent: #4cc9f0;
        --background: #f8fafc;
        --card-bg: rgba(255, 255, 255, 0.95);
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --border: rgba(148, 163, 184, 0.1);
        --gradient: linear-gradient(135deg, #4361ee, #3f37c9);
    }

    body {
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: var(--background);
        background-image: 
            radial-gradient(at 40% 20%, rgba(67, 97, 238, 0.1) 0px, transparent 50%),
            radial-gradient(at 80% 0%, rgba(76, 201, 240, 0.1) 0px, transparent 50%),
            radial-gradient(at 0% 50%, rgba(67, 97, 238, 0.1) 0px, transparent 50%);
    }

    .container {
        width: 90%;
        max-width: 900px;
        margin: 2rem auto;
        margin-left: 300px;
        padding: 2rem;
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
        border: 1px solid var(--border);
        animation: fadeIn 0.5s ease-out;
    }

    h1 {
        color: var(--text-primary);
        font-size: 2rem;
        text-align: center;
        margin-bottom: 2rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .filter-form {
        display: flex;
        gap: 1rem;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: rgba(67, 97, 238, 0.05);
        border-radius: 16px;
        border: 1px solid var(--border);
    }

    .filter-form input,
    .filter-form select {
        padding: 0.75rem 1rem;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.9);
        color: var(--text-primary);
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .filter-form input:focus,
    .filter-form select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    .filter-form button {
        padding: 0.75rem 1.5rem;
        background: var(--gradient);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-form button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 1rem;
    }

    th {
        background: rgba(67, 97, 238, 0.05);
        color: var(--primary);
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        border-bottom: 2px solid rgba(67, 97, 238, 0.1);
    }

    td {
        padding: 1rem;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border);
    }

    tbody tr {
        transition: all 0.3s ease;
    }

    tbody tr:hover {
        background: rgba(67, 97, 238, 0.02);
        transform: translateX(5px);
    }

    .pagination {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        margin-top: 2rem;
    }

    .pagination a {
        padding: 0.5rem 1rem;
        background: white;
        color: var(--text-primary);
        text-decoration: none;
        border-radius: 8px;
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .pagination a:hover {
        background: var(--gradient);
        color: white;
        transform: translateY(-2px);
    }

    .pagination .active {
        background: var(--gradient);
        color: white;
    }

    @keyframes fadeIn {
        from { 
            opacity: 0;
            transform: translateY(20px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .container {
            margin: 1rem;
            padding: 1rem;
            width: 95%;
        }

        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }

        table {
            display: block;
            overflow-x: auto;
        }

        th, td {
            white-space: nowrap;
        }
    }

    /* Status badges for transaction types */
    .status-setoran,
    .status-penarikan {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-setoran {
        background: rgba(46, 204, 113, 0.1);
        color: #2ecc71;
    }

    .status-penarikan {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }
</style>
</head>
<body>
    <div class="container">
        <h1>Riwayat Transaksi</h1>
           <center>
        <form class="filter-form" method="GET" action="riwayat_siswa.php">
            <input type="date" name="tanggal_mulai" value="<?= htmlspecialchars($filter_tanggal_mulai); ?>">
            <input type="date" name="tanggal_selesai" value="<?= htmlspecialchars($filter_tanggal_selesai); ?>">
            <select name="jenis">
                <option value="">Semua Jenis</option>
                <option value="setoran" <?= $filter_jenis == 'setoran' ? 'selected' : ''; ?>>Setoran</option>
                <option value="penarikan" <?= $filter_jenis == 'penarikan' ? 'selected' : ''; ?>>Penarikan</option>
            </select>
            <button type="submit">Filter</button>
        </form>
        </center>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nomor Transaksi</th>
                    <th>Nominal</th>
                    <th>Jenis</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($transaksi): ?>
                    <?php foreach ($transaksi as $trans): ?>
                        <tr>
                            <td><?= htmlspecialchars($trans['tanggal']); ?></td>
                            <td><?= htmlspecialchars($trans['nomor']); ?></td>
                            <td>Rp <?= number_format($trans['nominal'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="status-<?= strtolower($trans['jenis']); ?>">
                                    <?= ucfirst($trans['jenis']); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($trans['keterangan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Tidak ada transaksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i; ?>&tanggal_mulai=<?= htmlspecialchars($filter_tanggal_mulai); ?>&tanggal_selesai=<?= htmlspecialchars($filter_tanggal_selesai); ?>&jenis=<?= htmlspecialchars($filter_jenis); ?>" class="<?= $i == $page ? 'active' : ''; ?>"><?= $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>

</body>
</html>