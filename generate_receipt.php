<?php
// Pastikan sudah menyertakan koneksi ke database
include 'config/database.php';

$transaksi = null; // Tambahkan variabel transaksi untuk menyimpan hasil yang diambil dari database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $siswa_id = $_POST['siswa_id'];
    $transaction_number = $_POST['nomor']; // Nomor transaksi dari hidden input
    $tanggal = $_POST['tanggal'];
    $nominal = $_POST['nominal'];
    $keterangan = $_POST['keterangan'];

    // Inisialisasi koneksi ke database
    $database = new Database();
    $conn = $database->getConnection();

    // Query untuk memasukkan data ke dalam tabel transaksi
    $query = "INSERT INTO transaksi (siswa_id, nomor, tanggal, nominal, keterangan)
              VALUES (:siswa_id, :nomor, :tanggal, :nominal, :keterangan)";

    $stmt = $conn->prepare($query);

    // Bind data ke query
    $stmt->bindParam(':siswa_id', $siswa_id);
    $stmt->bindParam(':nomor', $transaction_number);
    $stmt->bindParam(':tanggal', $tanggal);
    $stmt->bindParam(':nominal', $nominal);
    $stmt->bindParam(':keterangan', $keterangan);

    // Eksekusi query dan cek apakah berhasil
    if ($stmt->execute()) {
        // Pembaruan saldo setelah transaksi berhasil disimpan
        $query_saldo = "UPDATE siswa SET saldo = saldo + :nominal WHERE id = :siswa_id";
        $stmt_saldo = $conn->prepare($query_saldo);
        $stmt_saldo->bindParam(':nominal', $nominal);
        $stmt_saldo->bindParam(':siswa_id', $siswa_id);

        if ($stmt_saldo->execute()) {
            $saldo_updated = true; // Tandai saldo berhasil diperbarui
        } else {
            echo "Terjadi kesalahan saat memperbarui saldo.";
        }

        // Ambil data transaksi yang baru saja disimpan untuk ditampilkan di halaman
        $query_transaksi = "SELECT transaksi.*, users.name FROM transaksi 
                            INNER JOIN siswa ON transaksi.siswa_id = siswa.id
                            INNER JOIN users ON siswa.user_id = users.id
                            WHERE transaksi.nomor = :transaction_number";
        
        $stmt_transaksi = $conn->prepare($query_transaksi);
        $stmt_transaksi->bindParam(':transaction_number', $transaction_number);
        $stmt_transaksi->execute();

        $transaksi = $stmt_transaksi->fetch(PDO::FETCH_ASSOC); // Ambil data transaksi
    } else {
        echo "Terjadi kesalahan saat menyimpan data.";
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .content-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 80%;
            max-width: 350px;
        }

        .container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .receipt {
            width: 300px;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            font-size: 14px;
            margin-top: 30px;
        }

        .receipt h1 {
            font-size: 18px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .receipt p {
            margin: 5px 0;
        }

        .receipt .total {
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
        }

        .receipt .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }

        .receipt .transaction-info {
            margin: 10px 0;
            border-top: 1px dashed #ddd;
            padding-top: 10px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
            display: block;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        button:active {
            background-color: #004085;
        }
    </style>
    <script>
        function printReceipt() {
            window.print();
        }
        <?php if ($saldo_updated): ?>
            window.onload = function() {
                alert("Saldo berhasil diperbarui!");
            }
        <?php endif; ?>
    </script>
</head>
<body>

    <div class="content-wrapper">
        <div class="container">
            <?php if ($transaksi): ?>
                <div class="receipt">
                    <h1>Receipt</h1>
                    <p><strong>Transaction No:</strong> <?= htmlspecialchars($transaksi['nomor']); ?></p>
                    <p><strong>Student:</strong> <?= htmlspecialchars($transaksi['name']); ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars(date('d-m-Y H:i', strtotime($transaksi['tanggal']))); ?></p>

                    <div class="transaction-info">
                        <p><strong>Item Price:</strong> Rp <?= number_format($transaksi['nominal'], 2, ',', '.'); ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($transaksi['keterangan']); ?></p>
                    </div>

                    <div class="total">
                        <p>Total: Rp <?= number_format($transaksi['nominal'], 2, ',', '.'); ?></p>
                    </div>

                    <div class="footer">
                        <p>Thank you for your transaction!</p>
                    </div>

                    <!-- Button to print the receipt -->
                    <button onclick="printReceipt()">Print Receipt</button>
                    <button onclick="goToPage()">Go to Home Page</button>
                </div>
            <?php else: ?>
                <p>No transactions found.</p>
            <?php endif; ?>
        </div>
    </div>
   

<script>
    function goToPage() {
        window.location.href = "kostumisasi_struk.php";  // Ganti dengan halaman yang dituju
    }
</script>

</body>
</html>
