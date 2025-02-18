<?php
include 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$nomor_transaksi = $_GET['nomor'] ?? '';

$query = "SELECT t.nomor, t.tanggal, t.nominal, t.keterangan, u.name AS nama_siswa 
          FROM transaksi t
          INNER JOIN siswa s ON t.siswa_id = s.id
          INNER JOIN users u ON s.user_id = u.id
          WHERE t.nomor = :nomor
          UNION ALL
          SELECT p.nomor, p.tanggal, p.nominal, p.status AS keterangan, u.name AS nama_siswa 
          FROM penarikan p
          INNER JOIN siswa s ON p.siswa_id = s.id
          INNER JOIN users u ON s.user_id = u.id
          WHERE p.nomor = :nomor";
$stmt = $conn->prepare($query);
$stmt->bindParam(':nomor', $nomor_transaksi);
$stmt->execute();
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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

        .receipt-details {
            text-align: left;
            margin-bottom: 20px;
        }

        .receipt-details p {
            margin: 5px 0;
            display: flex;
            align-items: center;
        }

        .receipt-details p i {
            margin-right: 8px;
        }

        .footer {
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            margin: 5px;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($transaksi): ?>
            <i class="fa-solid fa-receipt icon"></i>
            <h1>Receipt</h1>
            <div class="receipt-details">
                <p><i class="fa-solid fa-hashtag"></i><strong>Nomor Transaksi:</strong> <?= htmlspecialchars($transaksi['nomor']); ?></p>
                <p><i class="fa-solid fa-user"></i><strong>Nama Siswa:</strong> <?= htmlspecialchars($transaksi['nama_siswa']); ?></p>
                <p><i class="fa-solid fa-calendar"></i><strong>Tanggal:</strong> <?= htmlspecialchars($transaksi['tanggal']); ?></p>
                <p><i class="fa-solid fa-money-bill"></i><strong>Nominal:</strong> Rp <?= number_format($transaksi['nominal'], 2, ',', '.'); ?></p>
                <p><i class="fa-solid fa-file-lines"></i><strong>Deskripsi:</strong> <?= htmlspecialchars($transaksi['keterangan']); ?></p>
            </div>
            <div class="footer">
                <p>Thank you for your transaction!</p>
            </div>

            <!-- Button to print the receipt -->
            <button onclick="printReceipt()"><i class="fa-solid fa-print"></i> Print Receipt</button>
            <button onclick="goToPage()"><i class="fa-solid fa-home"></i> Go to Home Page</button>
        <?php else: ?>
            <p>No transactions found.</p>
        <?php endif; ?>
    </div>

    <script>
        function printReceipt() {
            window.print();
        }

        function goToPage() {
            window.location.href = "kostumisasi_struk.php";  // Ganti dengan halaman yang dituju
        }
    </script>
</body>
</html>