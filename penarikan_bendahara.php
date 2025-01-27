<?php
include 'config/database.php';
include 'navbar_bendahara.php';

// Mengambil data penarikan dari database
$database = new Database();
$conn = $database->getConnection();

$query = "SELECT penarikan.*, siswa.saldo, users.name 
          FROM penarikan
          INNER JOIN siswa ON penarikan.siswa_id = siswa.id
          INNER JOIN users ON siswa.user_id = users.id
          WHERE penarikan.status = 'pending'";

$stmt = $conn->prepare($query);
$stmt->execute();
$penarikans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses persetujuan atau penolakan penarikan
if (isset($_POST['action'])) {
    $penarikan_id = $_POST['penarikan_id'];
    $status = $_POST['action']; // 'approved' or 'rejected'

    // Memeriksa status penarikan
    if ($status == 'approved') {
        // Ambil data penarikan dan saldo siswa
        $query_penarikan = "SELECT * FROM penarikan WHERE id = :penarikan_id";
        $stmt_penarikan = $conn->prepare($query_penarikan);
        $stmt_penarikan->bindParam(':penarikan_id', $penarikan_id);
        $stmt_penarikan->execute();
        $penarikan = $stmt_penarikan->fetch(PDO::FETCH_ASSOC);

        $siswa_id = $penarikan['siswa_id'];
        $nominal = $penarikan['nominal'];

        // Periksa apakah saldo siswa mencukupi
        $query_saldo = "SELECT saldo FROM siswa WHERE id = :siswa_id";
        $stmt_saldo = $conn->prepare($query_saldo);
        $stmt_saldo->bindParam(':siswa_id', $siswa_id);
        $stmt_saldo->execute();
        $siswa = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

        if ($siswa['saldo'] >= $nominal) {
            // Update saldo siswa
            $new_saldo = $siswa['saldo'] - $nominal;
            $query_update_saldo = "UPDATE siswa SET saldo = :new_saldo WHERE id = :siswa_id";
            $stmt_update_saldo = $conn->prepare($query_update_saldo);
            $stmt_update_saldo->bindParam(':new_saldo', $new_saldo);
            $stmt_update_saldo->bindParam(':siswa_id', $siswa_id);
            $stmt_update_saldo->execute();

            // Update status penarikan menjadi 'approved'
            $query_update_status = "UPDATE penarikan SET status = 'approved' WHERE id = :penarikan_id";
            $stmt_update_status = $conn->prepare($query_update_status);
            $stmt_update_status->bindParam(':penarikan_id', $penarikan_id);
            $stmt_update_status->execute();

            echo "<script>alert('Penarikan berhasil diproses');</script>";
        } else {
            echo "<script>alert('Saldo siswa tidak mencukupi untuk penarikan ini');</script>";
        }
    } else if ($status == 'rejected') {
        // Update status penarikan menjadi 'rejected'
        $query_update_status = "UPDATE penarikan SET status = 'rejected' WHERE id = :penarikan_id";
        $stmt_update_status = $conn->prepare($query_update_status);
        $stmt_update_status->bindParam(':penarikan_id', $penarikan_id);
        $stmt_update_status->execute();

        echo "<script>alert('Penarikan ditolak');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penarikan Tabungan Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #dfefff, #dfefff);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 90%;
            max-width: 1200px;
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
            font-size: 16px;
            background-color: #f9f9f9;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px 16px;
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

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 4px;
            transition: background-color 0.3s;
        }

        .approve-btn {
            background-color: #28a745;
            color: white;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: #dc3545;
            color: white;
        }

        .reject-btn:hover {
            background-color: #c82333;
        }

        .empty-message {
            text-align: center;
            color: #666;
            font-size: 18px;
            padding: 20px 0;
        }

        @media (max-width: 768px) {
            th, td {
                font-size: 14px;
                padding: 8px 12px;
            }

            .action-btn {
                font-size: 12px;
                padding: 6px 12px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Penarikan Tabungan Siswa</h1>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Nominal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($penarikans): ?>
                    <?php foreach ($penarikans as $index => $penarikan): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= htmlspecialchars($penarikan['name']); ?></td>
                            <td>Rp <?= number_format($penarikan['nominal'], 2, ',', '.'); ?></td>
                            <td><?= ucfirst($penarikan['status']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="penarikan_id" value="<?= $penarikan['id']; ?>">
                                    <button type="submit" name="action" value="approved" class="action-btn approve-btn">Setujui</button>
                                    <button type="submit" name="action" value="rejected" class="action-btn reject-btn">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-message">Tidak ada permintaan penarikan saat ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
