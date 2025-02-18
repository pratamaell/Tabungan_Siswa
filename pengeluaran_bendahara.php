<?php
ob_start();
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bendahara') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';
include 'navbar_bendahara.php';

$database = new Database();
$conn = $database->getConnection();

// Ambil total saldo dari semua siswa
$querySaldo = "SELECT SUM(saldo) AS total_saldo FROM siswa";
$stmtSaldo = $conn->prepare($querySaldo);
$stmtSaldo->execute();
$resultSaldo = $stmtSaldo->fetch(PDO::FETCH_ASSOC);
$totalSaldo = $resultSaldo['total_saldo'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'];
    $nominal = $_POST['nominal'];
    $keterangan = $_POST['keterangan'];

    // Cek apakah saldo cukup
    if ($nominal > $totalSaldo) {
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'Saldo Tidak Cukup!',
            'text' => 'Saldo tidak mencukupi untuk pengeluaran ini.',
        ];
    } else {
        // Kalau saldo cukup, lakukan pengeluaran
        $query = "INSERT INTO pengeluaran (tanggal, nominal, keterangan) VALUES (:tanggal, :nominal, :keterangan)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':nominal', $nominal);
        $stmt->bindParam(':keterangan', $keterangan);

        if ($stmt->execute()) {
            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Pengeluaran Berhasil!',
                'text' => 'Pengeluaran telah dicatat dengan sukses.',
            ];
        } else {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Gagal!',
                'text' => 'Terjadi kesalahan saat mencatat pengeluaran.',
            ];
        }
    }

    // Redirect agar form tidak dikirim ulang saat refresh
    header("Location: pengeluaran_bendahara.php");
    exit();
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Pengeluaran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Tambahkan SweetAlert -->
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
            max-width: 600px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input, textarea {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Catat Pengeluaran</h1>
        <form method="POST" action="">
            <input type="date" name="tanggal" required>
            <input type="number" name="nominal" placeholder="Nominal" required>
            <textarea name="keterangan" placeholder="Keterangan" required></textarea>
            <button type="submit">Simpan</button>
        </form>
    </div>

    <script>
        // Cek apakah ada alert dari session
        <?php if (isset($_SESSION['alert'])) : ?>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['icon'] ?>',
                title: '<?= $_SESSION['alert']['title'] ?>',
                text: '<?= $_SESSION['alert']['text'] ?>',
                confirmButtonColor: '#3085d6'
            });
            <?php unset($_SESSION['alert']); ?> // Hapus session agar tidak muncul lagi setelah refresh
        <?php endif; ?>
    </script>
</body>
</html>
