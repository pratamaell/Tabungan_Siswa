<?php
ob_start();
include 'config/database.php';
include 'navbar_bendahara.php';

// Mengambil data penarikan dari database
$database = new Database();
$conn = $database->getConnection();

$query = "SELECT penarikan.*, penarikan.created_at, siswa.saldo, users.name 
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

        // Update saldo siswa
        $query_update_saldo = "UPDATE siswa SET saldo = saldo - :nominal WHERE id = :siswa_id";
        $stmt_update_saldo = $conn->prepare($query_update_saldo);
        $stmt_update_saldo->bindParam(':nominal', $penarikan['nominal']);
        $stmt_update_saldo->bindParam(':siswa_id', $penarikan['siswa_id']);
        $stmt_update_saldo->execute();
    }

    // Update status penarikan
    $query_update_status = "UPDATE penarikan SET status = :status WHERE id = :penarikan_id";
    $stmt_update_status = $conn->prepare($query_update_status);
    $stmt_update_status->bindParam(':status', $status);
    $stmt_update_status->bindParam(':penarikan_id', $penarikan_id);
    $stmt_update_status->execute();

    header("Location: penarikan_bendahara.php");
    exit();
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penarikan Bendahara</title>
    <style>
        /* General Styling */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #dfefff, #dfefff);
            color: #2d3436;
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
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 2.5em;
            color: #0984e3;
        }
        .header h2 {
            color: #636e72;
            margin-top: 10px;
            font-size: 1.2em;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #ffffff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #dfe6e9;
            font-size: 1em;
        }
        table th {
            background: #0984e3;
            color: #fff;
            text-transform: uppercase;
        }
        table tr:nth-child(even) {
            background: #f5f5f5;
        }
        table tr:hover {
            background: #dfe6e9;
        }

        /* Button Styling */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            background: #0984e3;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1em;
            transition: background 0.3s, transform 0.3s;
        }
        .btn:hover {
            background: #74b9ff;
            transform: scale(1.05);
        }
        .btn-approve {
            background: #00b894;
        }
        .btn-approve:hover {
            background: #55efc4;
        }
        .btn-reject {
            background: #d63031;
        }
        .btn-reject:hover {
            background: #ff7675;
        }

        /* Responsive Styling */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="home-section">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1>Penarikan Bendahara</h1>
                <h2>Daftar Penarikan yang Menunggu Persetujuan</h2>
            </div>

            <!-- Tabel Penarikan -->
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Jumlah (Rp)</th>
                        <th>Saldo (Rp)</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($penarikans) > 0): ?>
                        <?php foreach ($penarikans as $index => $penarikan): ?>
                            <tr>
                                <td><?= $index + 1; ?></td>
                                <td><?= htmlspecialchars($penarikan['name']); ?></td>
                                <td><?= number_format($penarikan['nominal'], 0, ',', '.'); ?></td>
                                <td><?= number_format($penarikan['saldo'], 0, ',', '.'); ?></td>
                                <td>
                                    <?= isset($penarikan['created_at']) && $penarikan['created_at'] ? date('d-m-Y H:i', strtotime($penarikan['created_at'])) : 'Tanggal Tidak Tersedia'; ?>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="penarikan_id" value="<?= $penarikan['id']; ?>">
                                        <button type="submit" name="action" value="approved" class="btn btn-approve">Setujui</button>
                                    </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="penarikan_id" value="<?= $penarikan['id']; ?>">
                                        <button type="submit" name="action" value="rejected" class="btn btn-reject">Tolak</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Tidak ada penarikan yang menunggu persetujuan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>