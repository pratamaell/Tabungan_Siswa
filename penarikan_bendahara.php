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
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --success: #2ecc71;
        --danger: #e74c3c;
        --background: #f8fafc;
        --card-bg: rgba(255, 255, 255, 0.95);
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --border: rgba(148, 163, 184, 0.1);
        --gradient: linear-gradient(135deg, #4361ee, #3f37c9);
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: var(--background);
        background-image: 
            radial-gradient(at 40% 20%, rgba(67, 97, 238, 0.1) 0px, transparent 50%),
            radial-gradient(at 80% 0%, rgba(76, 201, 240, 0.1) 0px, transparent 50%),
            radial-gradient(at 0% 50%, rgba(67, 97, 238, 0.1) 0px, transparent 50%);
        margin: 0;
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 2rem auto;
        margin-left: 90px;
        padding: 2rem;
        background: var(--card-bg);
        border-radius: 24px;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid var(--border);
        transition: all 0.3s ease;
        animation: fadeIn 0.5s ease-out;
    }

    .sidebar.open ~ .container {
        margin-left: 280px;
    }

    h1, h2 {
        color: var(--text-primary);
        margin-bottom: 1.5rem;
    }

    h1 {
        font-size: 2rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 2rem;
    }

    th {
        background: rgba(67, 97, 238, 0.05);
        color: var(--primary);
        font-weight: 600;
        padding: 1.25rem 1rem;
        text-align: left;
        font-size: 0.95rem;
        border-bottom: 2px solid rgba(67, 97, 238, 0.1);
    }

    td {
        padding: 1.25rem 1rem;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border);
        font-size: 0.95rem;
    }

    tbody tr {
        transition: all 0.3s ease;
    }

    tbody tr:hover {
        background: rgba(67, 97, 238, 0.02);
        transform: translateX(5px);
    }

    .btn {
        padding: 0.75rem 1.25rem;
        border: none;
        color: white;
        cursor: pointer;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0 0.25rem;
    }

    .btn-approve {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
    }

    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
    }

    .btn-reject {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(231, 76, 60, 0.2);
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
        }

        table {
            display: block;
            overflow-x: auto;
        }

        td, th {
            white-space: nowrap;
        }
    }

    .home-section {
        position: relative;
        min-height: 100vh;
        width: calc(100% - 78px);
        left: 78px;
        transition: all 0.3s ease;
        }

       
        /* Add this for sidebar open state */
        .sidebar.open ~ .home-section {
            width: calc(100% - 250px);
            left: 250px;
        }
</style>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<body>
    <div class="home-section">
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
    </div>
    <script>
        <?= $notif; ?>
    </script>
</body>
</html>