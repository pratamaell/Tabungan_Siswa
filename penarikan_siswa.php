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

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg,rgb(175, 173, 189),rgb(65, 60, 123));
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 500px;
            padding: 30px;
            background:rgb(64, 55, 135);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: white;
            text-align: center;
            margin-left: 350px;
        }

        h1 {
            color: #fff;
            margin-bottom: 20px;
        }

        .saldo {
            font-size: 18px;
            color: #fff;
            margin-bottom: 20px;
            font-weight: bold;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        input[type="number"] {
            padding: 10px;
            width: 100%;
            max-width: 300px;
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
            width: 100%;
            max-width: 300px;
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
