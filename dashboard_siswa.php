<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';
include 'navbar_siswa.php';

$database = new Database();
$db = $database->getConnection();

// Ambil data siswa yang login
$user_id = $_SESSION['user_id'];
$query_user = "SELECT name FROM users WHERE id = :id";
$stmt_user = $db->prepare($query_user);
$stmt_user->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt_user->execute();
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Ambil ID siswa berdasarkan user_id
$query_siswa = "SELECT id FROM siswa WHERE user_id = :user_id";
$stmt_siswa = $db->prepare($query_siswa);
$stmt_siswa->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_siswa->execute();
$siswa = $stmt_siswa->fetch(PDO::FETCH_ASSOC);

if ($siswa) {
    $siswa_id = $siswa['id'];

    // Perbaikan perhitungan total saldo (gabungkan transaksi dan penarikan)
    $query_saldo = "
    SELECT 
        (COALESCE(SUM(CASE WHEN jenis = 'setoran' THEN nominal ELSE 0 END), 0) - 
        COALESCE((SELECT SUM(nominal) FROM penarikan WHERE siswa_id = :siswa_id), 0)) 
        AS total_saldo 
    FROM transaksi 
    WHERE siswa_id = :siswa_id";
    $stmt_saldo = $db->prepare($query_saldo);
    $stmt_saldo->bindParam(':siswa_id', $siswa_id, PDO::PARAM_INT);
    $stmt_saldo->execute();
    $saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

    // Gabungkan transaksi setoran dan penarikan dalam riwayat transaksi
    $query_transaksi = "
    (SELECT created_at, 'Setoran' AS keterangan, nominal FROM transaksi WHERE siswa_id = :siswa_id)
    UNION 
    (SELECT tanggal AS created_at, 'Penarikan' AS keterangan, nominal FROM penarikan WHERE siswa_id = :siswa_id)
    ORDER BY created_at DESC LIMIT 5";
    
    $stmt_transaksi = $db->prepare($query_transaksi);
    $stmt_transaksi->bindParam(':siswa_id', $siswa_id, PDO::PARAM_INT);
    $stmt_transaksi->execute();
    $transaksi = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);
} else {
    $saldo = ['total_saldo' => 0];
    $transaksi = [];
}

// Ambil data setoran terakhir
$query_setoran_terakhir = "
    SELECT nominal, created_at AS tanggal 
    FROM transaksi 
    WHERE siswa_id = :siswa_id AND jenis = 'setoran' 
    ORDER BY created_at DESC 
    LIMIT 1";
$stmt_setoran_terakhir = $db->prepare($query_setoran_terakhir);
$stmt_setoran_terakhir->bindParam(':siswa_id', $siswa_id);
$stmt_setoran_terakhir->execute();
$setoran_terakhir = $stmt_setoran_terakhir->fetch(PDO::FETCH_ASSOC);

// Ambil data penarikan terakhir
$query_penarikan_terakhir = "
    SELECT nominal, tanggal 
    FROM penarikan 
    WHERE siswa_id = :siswa_id 
    ORDER BY tanggal DESC 
    LIMIT 1";
$stmt_penarikan_terakhir = $db->prepare($query_penarikan_terakhir);
$stmt_penarikan_terakhir->bindParam(':siswa_id', $siswa_id);
$stmt_penarikan_terakhir->execute();
$penarikan_terakhir = $stmt_penarikan_terakhir->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg,rgb(175, 173, 189),rgb(65, 60, 123));
            color: #fff;
            padding-top: 80px;
        }

        .main-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }

        .header {
            background: #fdcb6e;
            padding: 20px;
            border-radius: 10px;
            color: #2d3436;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            width: 100%;
            max-width: 900px;
        }

        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            width: 100%;
            max-width: 900px;
        }

        .card {
            background: rgb(64, 55, 135);
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: left;
            flex: 1;
            min-width: 280px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            color: rgb(226, 228, 229);
        }

        .table th, .table td {
            border: 1px solid #fff;
            padding: 10px;
            text-align: left;
        }

        .table th {
            background: rgb(88, 83, 164);
        }

        @media (max-width: 768px) {
            .cards {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h3>Selamat Datang, <?php echo htmlspecialchars($user['name']); ?>!</h3>
            <p>Berikut adalah informasi tabungan Anda:</p>
        </div>
        <div class="cards">
        <div class="card">
                <h3>Setoran Terakhir</h3>
                <?php if ($setoran_terakhir): ?>
                    <p><b>Rp <?php echo number_format($setoran_terakhir['nominal'], 0, ',', '.'); ?></b></p>
                    <p><?php echo date('d M Y', strtotime($setoran_terakhir['tanggal'])); ?></p>
                <?php else: ?>
                    <p>Belum ada setoran.</p>
                <?php endif; ?>
            </div>
            <div class="card">
                <h3>Penarikan Terakhir</h3>
                <?php if ($penarikan_terakhir): ?>
                    <p><b>Rp <?php echo number_format($penarikan_terakhir['nominal'], 0, ',', '.'); ?></b></p>
                    <p><?php echo date('d M Y', strtotime($penarikan_terakhir['tanggal'])); ?></p>
                <?php else: ?>
                    <p>Belum ada penarikan.</p>
                <?php endif; ?>
            </div>
            <div class="card">
                <h3>Total Saldo</h3>
                <h4>Rp <?php echo number_format($saldo['total_saldo'], 0, ',', '.'); ?></h4>
            </div>
            <div class="card riwayat">
                <h3>Riwayat Transaksi</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi as $trx): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($trx['created_at'])); ?></td>
                                <td><?php echo $trx['keterangan']; ?></td>
                                <td>Rp <?php echo number_format($trx['nominal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
