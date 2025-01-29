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
    SELECT 'penarikan' AS jenis, '' AS nomor, p.nominal, p.tanggal, p.status AS keterangan 
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
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 800px;
            margin: 20px auto;
            margin-top: 80px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .filter-form {
            margin-bottom: 20px;
        }

        .filter-form input, .filter-form select {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .filter-form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .filter-form button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tbody tr:hover {
            background-color: #e9ecef;
        }

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            padding: 10px 15px;
            margin: 0 5px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a:hover {
            background-color: #0056b3;
        }

        .pagination .active {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Riwayat Transaksi</h1>

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
                            <td><?= ucfirst($trans['jenis']); ?></td>
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