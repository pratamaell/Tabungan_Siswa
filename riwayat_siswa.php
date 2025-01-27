<?php
session_start();
include 'config/database.php';
include 'navbar_siswa.php';

// Inisialisasi koneksi menggunakan PDO
$database = new Database();
$conn = $database->getConnection();

// Ambil data riwayat transaksi siswa
$siswa_id = $_SESSION['user_id'];
// Query untuk mendapatkan transaksi siswa
$query_transaksi = "
    SELECT 'setoran' AS jenis, t.nomor, t.nominal, t.tanggal, t.keterangan 
    FROM transaksi t
    JOIN siswa s ON t.siswa_id = s.id
    WHERE s.user_id = :user_id AND t.jenis = 'setoran'
    UNION ALL
    SELECT 'penarikan' AS jenis, '' AS nomor, p.nominal, p.tanggal, p.status AS keterangan 
    FROM penarikan p
    JOIN siswa s ON p.siswa_id = s.id
    WHERE s.user_id = :user_id
    ORDER BY tanggal DESC;
";
$stmt_transaksi = $conn->prepare($query_transaksi);
$stmt_transaksi->execute(['user_id' => $siswa_id]);
$transaksi = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);
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
            margin-top: 100px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Riwayat Transaksi</h1>
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
    </div>
</body>
</html>