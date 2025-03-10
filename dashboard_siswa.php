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
        COALESCE((SELECT SUM(nominal) FROM penarikan WHERE siswa_id = :siswa_id AND status = 'approved'), 0)) 
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

$today = date('d'); // Ambil tanggal hari ini
$showReminder = ($today == '10'); // Jika tanggal 8, tampilkan pengingat

// Check if the welcome alert has been shown
$showWelcomeAlert = !isset($_SESSION['welcome_alert_shown']);
if ($showWelcomeAlert) {
    $_SESSION['welcome_alert_shown'] = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --accent: #4cc9f0;
        --success: #2ecc71;
        --warning: #f1c40f;
        --danger: #e74c3c;
        --dark: #2d3436;
        --light: #f8fafc;
        --gradient: linear-gradient(135deg, #4361ee, #3f37c9);
    }

    body {
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #f4f7fe;
        color: var(--dark);
        padding-top: 80px;
        background-image: 
            radial-gradient(at 40% 20%, rgba(67, 97, 238, 0.1) 0px, transparent 50%),
            radial-gradient(at 80% 0%, rgba(76, 201, 240, 0.1) 0px, transparent 50%),
            radial-gradient(at 0% 50%, rgba(67, 97, 238, 0.1) 0px, transparent 50%);
    }

    .main-content {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-left: 250px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .header {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        color: var(--dark);
        width: 100%;
        max-width: 1000px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .header h3 {
        margin: 0;
        font-size: 1.8rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header p {
        color: #64748b;
        margin-top: 0.5rem;
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        width: 100%;
        max-width: 1000px;
    }

    .card {
        background: white;
        padding: 1.5rem;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(10px);
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card h3 {
        color: #1e293b;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .card h4 {
        font-size: 1.8rem;
        color: var(--primary);
        margin: 0.5rem 0;
    }

    .card p {
        color: #64748b;
        margin: 0.5rem 0;
    }

    .card b {
        color: var(--primary);
        font-size: 1.4rem;
    }

    .riwayat {
        grid-column: 1 / -1;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 1rem;
    }

    .table th, .table td {
        padding: 1rem;
        text-align: left;
        border: none;
    }

    .table th {
        background: rgba(67, 97, 238, 0.05);
        color: var(--primary);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .table tr:first-child th:first-child {
        border-top-left-radius: 10px;
    }

    .table tr:first-child th:last-child {
        border-top-right-radius: 10px;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: rgba(67, 97, 238, 0.02);
    }

    .table td {
        color: #64748b;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Sweet Alert Customization */
    .swal2-popup {
        border-radius: 20px;
        padding: 2rem;
    }

    .swal2-title {
        color: var(--dark) !important;
        font-size: 1.5rem !important;
    }

    .swal2-content {
        color: #64748b !important;
    }

    .swal2-confirm {
        background: var(--gradient) !important;
        border-radius: 10px !important;
        padding: 1rem 2rem !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 1rem;
        }

        .header {
            padding: 1.5rem;
        }

        .cards {
            grid-template-columns: 1fr;
        }

        .table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header, .card {
        animation: fadeIn 0.5s ease-out forwards;
    }

    .card:nth-child(1) { animation-delay: 0.1s; }
    .card:nth-child(2) { animation-delay: 0.2s; }
    .card:nth-child(3) { animation-delay: 0.3s; }
    .card:nth-child(4) { animation-delay: 0.4s; }
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
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            <?php if ($showReminder): ?>
                Swal.fire({
                    title: "Pengingat KAS!",
                    text: "Hari ini tanggal 10, jangan lupa untuk bayar KAS ya!",
                    icon: "info",
                    confirmButtonText: "Siap!"
                });
            <?php elseif ($showWelcomeAlert): ?>
                Swal.fire({
                    title: "Selamat Datang!",
                    text: "Halo, <?php echo htmlspecialchars($user['name']); ?>! Selamat datang di dashboard siswa.",
                    icon: "success",
                    confirmButtonText: "Terima Kasih!"
                });
            <?php endif; ?>
        });
    </script>

</body>
</html>
