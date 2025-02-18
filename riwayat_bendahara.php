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

// Ambil data transaksi dan penarikan
$query = "
    SELECT t.nomor, t.tanggal, t.nominal, t.keterangan, u.name AS nama_siswa, 'Setoran' AS jenis
    FROM transaksi t
    INNER JOIN siswa s ON t.siswa_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    UNION ALL
    SELECT p.nomor, p.tanggal, p.nominal, p.status AS keterangan, u.name AS nama_siswa, 'Penarikan' AS jenis
    FROM penarikan p
    INNER JOIN siswa s ON p.siswa_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    ORDER BY tanggal DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Riwayat Transaksi Bendahara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1000px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background: #007bff;
            color: #fff;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .actions button {
            padding: 5px 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .actions button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Riwayat Transaksi Bendahara</h1>
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
                        <td><?= htmlspecialchars($row['tanggal'] ?? ''); ?></td>
                        <td>Rp <?= number_format($row['nominal'] ?? 0, 2, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($row['keterangan'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($row['jenis'] ?? ''); ?></td>
                        <td class="actions">
                            <button onclick="printReceipt('<?= htmlspecialchars($row['nomor']); ?>')">
                                <i class="fa-solid fa-print"></i> Print
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function printReceipt(nomor) {
            window.open('generate_receipt.php?nomor=' + encodeURIComponent(nomor), '_blank');
        }
    </script>
</body>
</html>