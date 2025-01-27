<?php
// Include connection file (database connection)
include 'config/database.php';
include 'navbar_bendahara.php';

// Inisialisasi koneksi menggunakan PDO
$database = new Database();
$conn = $database->getConnection();

// Ambil data siswa dengan join ke tabel users
$query_siswa = "SELECT siswa.id, users.name 
                FROM siswa 
                INNER JOIN users ON siswa.user_id = users.id";
$stmt_siswa = $conn->prepare($query_siswa);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->fetchAll(PDO::FETCH_ASSOC);

// Generate nomor transaksi baru
$nomor_transaksi_baru = 'TRX-' . time(); // Format: TRX-1674601234

// Proses penyetoran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siswa_id = $_POST['siswa_id'];
    $nomor = $_POST['nomor'];
    $tanggal = $_POST['tanggal'];
    $nominal = $_POST['nominal'];
    $keterangan = $_POST['keterangan'];

    // Masukkan data ke tabel transaksi
    $query_transaksi = "INSERT INTO transaksi (siswa_id, nomor, tanggal, nominal, keterangan) VALUES (:siswa_id, :nomor, :tanggal, :nominal, :keterangan)";
    $stmt_transaksi = $conn->prepare($query_transaksi);
    $stmt_transaksi->bindParam(':siswa_id', $siswa_id);
    $stmt_transaksi->bindParam(':nomor', $nomor);
    $stmt_transaksi->bindParam(':tanggal', $tanggal);
    $stmt_transaksi->bindParam(':nominal', $nominal);
    $stmt_transaksi->bindParam(':keterangan', $keterangan);

    if ($stmt_transaksi->execute()) {
        // Perbarui saldo siswa di tabel siswa
        $query_saldo = "UPDATE siswa SET saldo = saldo + :nominal WHERE id = :siswa_id";
        $stmt_saldo = $conn->prepare($query_saldo);
        $stmt_saldo->bindParam(':nominal', $nominal);
        $stmt_saldo->bindParam(':siswa_id', $siswa_id);
        $stmt_saldo->execute();

        echo "<script>alert('Transaksi berhasil disimpan dan saldo siswa diperbarui');</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat menyimpan transaksi');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Receipt</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #dfefff, #dfefff);
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
            width: 100%;
            max-width: 1200px;
            margin-top: 100px; /* Avoid collision with navbar */
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }

        h1 {
            grid-column: span 2;
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            font-size: 16px;
            color: #555;
            margin-bottom: 8px;
            display: block;
        }

        select, input[type="text"], input[type="number"], input[type="datetime-local"], textarea {
            width: 90%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: #fafafa;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        input[type="text"], input[type="number"], input[type="datetime-local"] {
            height: 40px;
        }

        button {
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        button:active {
            background-color: #004085;
        }

        .button-container {
            grid-column: span 2;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                padding: 20px;
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="container">
            <h1>INPUT PENYETORAN</h1>

            <!-- Pilih Siswa -->
            <div>
                <label for="siswa_id">SISWA:</label>
                <select name="siswa_id" required>
                    <?php if (!empty($result_siswa)): ?>
                        <?php foreach ($result_siswa as $row): ?>
                            <option value="<?= htmlspecialchars($row['id']); ?>"><?= htmlspecialchars($row['name']); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option disabled>No students available</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Nomor Transaksi -->
            <div>
                <label for="transaction_number">Transaction Number:</label>
                <input type="hidden" name="nomor" value="<?= htmlspecialchars($nomor_transaksi_baru); ?>">
                <input type="text" value="<?= htmlspecialchars($nomor_transaksi_baru); ?>" readonly>
            </div>

            <!-- Tanggal -->
            <div>
                <label for="date">TANGGAL:</label>
                <input type="datetime-local" name="tanggal" required>
            </div>

            <!-- Harga Item -->
            <div>
                <label for="item_price">NOMINAL:</label>
                <input type="number" name="nominal" required>
            </div>

            <!-- Deskripsi Item -->
            <div>
                <label for="item_description">DESKRIPSI:</label>
                <textarea name="keterangan" required></textarea>
            </div>

            <!-- Tombol Submit -->
            <div class="button-container">
                <button type="submit">STRUK</button>
            </div>
        </div>
    </div>
</body>
</html>
