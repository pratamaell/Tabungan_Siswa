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
    <title>Digital Receipt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2962ff;
            --secondary-color: #0039cb;
            --accent-color: #768fff;
            --text-color: #333;
            --background-gradient: linear-gradient(135deg, #f5f7ff 0%, #ffffff 100%);
        }

        body {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            background: var(--background-gradient);
            margin: 0;
            padding: 2rem;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(41, 98, 255, 0.1),
                        0 1px 8px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 600px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        h1 {
            font-size: 2rem;
            color: var(--text-color);
            margin: 0.5rem 0;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .receipt-details {
            background: rgba(41, 98, 255, 0.03);
            padding: 1.5rem;
            border-radius: 15px;
            margin: 1.5rem 0;
        }

        .receipt-details p {
            margin: 1rem 0;
            padding: 0.8rem;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .receipt-details p:hover {
            transform: translateX(5px);
        }

        .receipt-details i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .receipt-details strong {
            min-width: 140px;
            color: var(--text-color);
        }

        .footer {
            text-align: center;
            color: var(--text-color);
            margin: 2rem 0;
            padding: 1rem;
            border-top: 2px dashed rgba(41, 98, 255, 0.1);
        }

        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        button {
            padding: 0.8rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(41, 98, 255, 0.2);
        }

        button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(41, 98, 255, 0.3);
        }

        button i {
            font-size: 1.1rem;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .container {
                box-shadow: none;
                border: none;
            }

            button {
                display: none;
            }
        }

        @media (max-width: 600px) {
            body {
                padding: 1rem;
            }

            .container {
                padding: 1.5rem;
            }

            .receipt-details p {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .button-group {
                flex-direction: column;
            }

            button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($transaksi): ?>
            <div class="receipt-header">
                <i class="fa-solid fa-receipt icon"></i>
                <h1>Digital Receipt</h1>
            </div>
            <div class="receipt-details">
                <p>
                    <i class="fa-solid fa-hashtag"></i>
                    <strong>Transaction ID</strong>
                    <span><?= htmlspecialchars($transaksi['nomor']); ?></span>
                </p>
                <p>
                    <i class="fa-solid fa-user"></i>
                    <strong>Nama Siswa/Siswi</strong>
                    <span><?= htmlspecialchars($transaksi['nama_siswa']); ?></span>
                </p>
                <p>
                    <i class="fa-solid fa-calendar"></i>
                    <strong>Tanggal</strong>
                    <span><?= htmlspecialchars($transaksi['tanggal']); ?></span>
                </p>
                <p>
                    <i class="fa-solid fa-money-bill"></i>
                    <strong>Nominal</strong>
                    <span>Rp <?= number_format($transaksi['nominal'], 2, ',', '.'); ?></span>
                </p>
                <p>
                    <i class="fa-solid fa-file-lines"></i>
                    <strong>Description</strong>
                    <span><?= htmlspecialchars($transaksi['keterangan']); ?></span>
                </p>
            </div>
            <div class="footer">
                <p>Thank you for your transaction!</p>
                <small>Keep this receipt for your records</small>
            </div>

            <div class="button-group">
                <button onclick="printReceipt()">
                    <i class="fa-solid fa-print"></i>
                    Print Receipt
                </button>
                <button onclick="goToPage()">
                    <i class="fa-solid fa-home"></i>
                    Back to Home
                </button>
            </div>
        <?php else: ?>
            <div class="receipt-header">
                <i class="fa-solid fa-triangle-exclamation icon"></i>
                <h1>No Transaction Found</h1>
            </div>
            <div class="button-group">
                <button onclick="goToPage()">
                    <i class="fa-solid fa-home"></i>
                    Back to Home
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function printReceipt() {
            window.print();
        }

        function goToPage() {
            window.location.href = "kostumisasi_struk.php";
        }
    </script>
</body>
</html>