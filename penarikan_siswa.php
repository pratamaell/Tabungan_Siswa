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

    // Ambil total saldo siswa (setoran - penarikan)
    $query_saldo = "
        SELECT 
            SUM(CASE WHEN jenis = 'setoran' THEN nominal ELSE 0 END) - 
            SUM(CASE WHEN jenis = 'penarikan' THEN nominal ELSE 0 END) AS total_saldo
        FROM transaksi 
        WHERE siswa_id = :siswa_id";
    $stmt_saldo = $conn->prepare($query_saldo);
    $stmt_saldo->bindParam(':siswa_id', $siswa_id, PDO::PARAM_INT);
    $stmt_saldo->execute();
    $saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC);
    $saldo = $saldo['total_saldo'] ?? 0; // Jika saldo tidak ada, set 0
} else {
    $saldo = 0;
}

// Proses pengajuan penarikan
if (isset($_POST['submit'])) {
    $nominal = $_POST['nominal'];

    if ($nominal > 0 && $nominal <= $saldo) {
        // Simpan permintaan penarikan ke database
        $query_penarikan = "INSERT INTO penarikan (siswa_id, nominal, status) VALUES (:siswa_id, :nominal, 'pending')";
        $stmt_penarikan = $conn->prepare($query_penarikan);
        $stmt_penarikan->bindParam(':siswa_id', $siswa_id);
        $stmt_penarikan->bindParam(':nominal', $nominal);
        $stmt_penarikan->execute();

        // Tampilkan pesan berhasil
        echo "<script>alert('Permintaan penarikan berhasil diajukan.');</script>";
    } else {
        echo "<script>alert('Nominal penarikan melebihi saldo atau tidak valid.');</script>";
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
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative; /* Pastikan posisi sidebar tidak mempengaruhi layout */
        }

        .container {
            width: 100%;
            max-width: 500px;
            padding: 30px;
            background: #2c3e50;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: white;
            position: absolute;
            top: 50%;
            transform: translateY(-50%); /* Memastikan container berada tepat di tengah halaman */
        }

        h1 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }

        .saldo {
            font-size: 18px;
            color: #fff;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="number"] {
            padding: 10px;
            margin-bottom: 15px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
            outline: none;
        }

        input[type="number"]:focus {
            border-color: #3498db;
        }

        button {
            padding: 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .alert {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

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
