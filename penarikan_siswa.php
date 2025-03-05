<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login_register.php");
    exit();
}

$user_id = $_SESSION['user_id']; 

include 'config/database.php';
include 'navbar_siswa.php';

// Mengambil data siswa
$database = new Database();
$conn = $database->getConnection();

// Query untuk mengambil saldo siswa
$query_siswa = "SELECT id FROM siswa WHERE user_id = :user_id";
$stmt_siswa = $conn->prepare($query_siswa);
$stmt_siswa->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_siswa->execute();
$siswa = $stmt_siswa->fetch(PDO::FETCH_ASSOC);

if ($siswa) {
    $siswa_id = $siswa['id'];

    $query_saldo = "SELECT saldo FROM siswa WHERE id = :siswa_id";
    $stmt_saldo = $conn->prepare($query_saldo);
    $stmt_saldo->bindParam(':siswa_id', $siswa_id, PDO::PARAM_INT);
    $stmt_saldo->execute();
    $result = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $saldo = $result['saldo']; // Ambil saldo dari database
    } else {
        $saldo = 0; // Jika data tidak ditemukan, set saldo jadi 0
    }
    }

// Proses pengajuan penarikan
if (isset($_POST['submit'])) {
    $nominal = $_POST['nominal'];

    if ($nominal > 0 && $nominal <= $saldo) {
        // Simpan permintaan penarikan ke database
        $query_penarikan = "INSERT INTO penarikan (siswa_id, nominal, status, nomor) VALUES (:siswa_id, :nominal, 'pending', :nomor)";
        $stmt_penarikan = $conn->prepare($query_penarikan);
        $stmt_penarikan->bindParam(':siswa_id', $siswa_id);
        $stmt_penarikan->bindParam(':nominal', $nominal);
        $nomor = uniqid('TRX'); // Menggunakan metode unik untuk nomor transaksi
        $stmt_penarikan->bindParam(':nomor', $nomor);
        $stmt_penarikan->execute();
    
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Permintaan penarikan berhasil diajukan. Nomor transaksi: " . $nomor . "',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'penarikan_siswa.php';
                    });
                });
            </script>";
        exit();

    } else {
        // SweetAlert2 Error
        echo "<script>
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Nominal penarikan melebihi saldo atau tidak valid.',
                    icon: 'error',
                    confirmButtonText: 'Coba Lagi'
                });
              </script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penarikan Tabungan Siswa</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --accent: #4cc9f0;
        --background: #f8fafc;
        --card-bg: rgba(255, 255, 255, 0.9);
        --text-primary: #2d3436;
        --text-secondary: #636e72;
        --success: #2ecc71;
        --error: #e74c3c;
        --gradient: linear-gradient(135deg, #4361ee, #3f37c9);
        --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    }

    body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: var(--background);
        background-image: 
            radial-gradient(at 40% 20%, rgba(67, 97, 238, 0.1) 0px, transparent 50%),
            radial-gradient(at 80% 0%, rgba(76, 201, 240, 0.1) 0px, transparent 50%),
            radial-gradient(at 0% 50%, rgba(67, 97, 238, 0.1) 0px, transparent 50%);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .container {
        width: 100%;
        max-width: 500px;
        padding: 2.5rem;
        background: var(--card-bg);
        backdrop-filter: blur(16px);
        border-radius: 24px;
        box-shadow: var(--shadow);
        border: 1px solid rgba(255, 255, 255, 0.18);
        margin-left: 350px;
        transform: translateY(0);
        transition: all 0.3s ease;
        animation: fadeIn 0.5s ease-out;
    }

    .container:hover {
        transform: translateY(-5px);
    }

    h1 {
        color: var(--text-primary);
        font-size: 2rem;
        margin-bottom: 1.5rem;
        text-align: center;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .saldo {
        background: rgba(67, 97, 238, 0.05);
        padding: 1.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        border: 1px solid rgba(67, 97, 238, 0.1);
    }

    .saldo strong {
        color: var(--text-primary);
        font-size: 1.1rem;
        display: block;
        margin-bottom: 0.5rem;
    }

    .saldo-amount {
        color: var(--primary);
        font-size: 2rem;
        font-weight: 600;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    label {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    input[type="number"] {
        width: 100%;
        padding: 1rem;
        border: 2px solid rgba(67, 97, 238, 0.1);
        border-radius: 12px;
        font-size: 1rem;
        background: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
    }

    input[type="number"]:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
    }

    button[type="submit"] {
        background: var(--gradient);
        color: white;
        border: none;
        padding: 1rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    button[type="submit"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
    }

    button[type="submit"]:active {
        transform: translateY(0);
    }

    .alert {
        background: rgba(231, 76, 60, 0.1);
        color: var(--error);
        padding: 1rem;
        border-radius: 12px;
        margin-top: 1rem;
        font-size: 0.9rem;
        border: 1px solid rgba(231, 76, 60, 0.2);
    }

    /* Custom Sweet Alert Styling */
    .swal2-popup {
        border-radius: 24px !important;
        padding: 2rem !important;
    }

    .swal2-title {
        font-size: 1.5rem !important;
        color: var(--text-primary) !important;
    }

    .swal2-confirm {
        background: var(--gradient) !important;
        border-radius: 12px !important;
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
            padding: 1.5rem;
        }

        h1 {
            font-size: 1.5rem;
        }

        .saldo-amount {
            font-size: 1.5rem;
        }
    }
</style>
</head>
<body>
z
    <div class="container">
        <h1>Penarikan Tabungan Siswa</h1>
        <div class="saldo">
            <strong>Saldo Anda:</strong> Rp <?= number_format($saldo, 2, ',', '.'); ?>
        </div>

        <form method="POST">
            <label for="nominal">Nominal Penarikan:</label>
            <input type="number" id="nominal" name="nominal" min="1" max="<?= $saldo ?>" required>

            <button type="submit" name="submit">Ajukan Penarikan</button>
        </form>

        <!-- Add alert messages here if any -->
        <?php if (isset($error_message)): ?>
            <div class="alert"><?= $error_message ?></div>
        <?php endif; ?>
    </div>

</body>
</html>
