<?php
ob_start();
include 'config/database.php';
include 'navbar_bendahara.php';

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

$notif = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $penarikan_id = $_POST['penarikan_id'];
    $status = $_POST['action'];

    if ($status == 'approved') {
        $query_penarikan = "SELECT * FROM penarikan WHERE id = :penarikan_id";
        $stmt_penarikan = $conn->prepare($query_penarikan);
        $stmt_penarikan->bindParam(':penarikan_id', $penarikan_id);
        $stmt_penarikan->execute();
        $penarikan = $stmt_penarikan->fetch(PDO::FETCH_ASSOC);

        $query_update_saldo = "UPDATE siswa SET saldo = saldo - :nominal WHERE id = :siswa_id";
        $stmt_update_saldo = $conn->prepare($query_update_saldo);
        $stmt_update_saldo->bindParam(':nominal', $penarikan['nominal']);
        $stmt_update_saldo->bindParam(':siswa_id', $penarikan['siswa_id']);
        $stmt_update_saldo->execute();
    }

    $query_update_status = "UPDATE penarikan SET status = :status WHERE id = :penarikan_id";
    $stmt_update_status = $conn->prepare($query_update_status);
    $stmt_update_status->bindParam(':status', $status);
    $stmt_update_status->bindParam(':penarikan_id', $penarikan_id);
    $stmt_update_status->execute();

    if ($status == 'approved') {
        $notif = "Swal.fire({title: 'Berhasil!', text: 'Penarikan telah disetujui.', icon: 'success'}).then(() => {window.location.href = window.location.href;});";
    } else {
        $notif = "Swal.fire({title: 'Ditolak!', text: 'Penarikan telah ditolak.', icon: 'error'}).then(() => {window.location.href = window.location.href;});";
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penarikan Bendahara</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            color: #2d3436;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-left: 80px; /* Sesuaikan dengan lebar sidebar */
            transition: margin-left 0.3s ease; /* Animasi jika sidebar bisa dibuka/tutup */
        }

        /* Jika sidebar bisa dibuka dan lebih lebar, tambahkan kelas tambahan */
        .sidebar.open ~ .container {
            margin-left: 250px; /* Sesuaikan dengan lebar sidebar saat terbuka */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #0984e3;
            color: #fff;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Penarikan Bendahara</h1>
        <h2>Daftar Penarikan yang Menunggu Persetujuan</h2>
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
                <?php foreach ($penarikans as $index => $penarikan): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($penarikan['name']); ?></td>
                        <td><?= number_format($penarikan['nominal'], 0, ',', '.'); ?></td>
                        <td><?= number_format($penarikan['saldo'], 0, ',', '.'); ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($penarikan['created_at'])); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="penarikan_id" value="<?= $penarikan['id']; ?>">
                                <button type="submit" name="action" value="approved" class="btn btn-approve">Setujui</button>
                                <button type="submit" name="action" value="rejected" class="btn btn-reject">Tolak</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        <?= $notif; ?>
    </script>
</body>
</html>