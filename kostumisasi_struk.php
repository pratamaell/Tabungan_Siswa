<?php
// Include file koneksi database dan navbar
include 'config/database.php';
include 'navbar_bendahara.php';

// Inisialisasi koneksi menggunakan PDO
$database = new Database();
$conn = $database->getConnection();

// Ambil data siswa dengan join ke tabel users
$query_siswa = "SELECT siswa.id, users.name, siswa.saldo 
                FROM siswa 
                INNER JOIN users ON siswa.user_id = users.id";
$stmt_siswa = $conn->prepare($query_siswa);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->fetchAll(PDO::FETCH_ASSOC);

// Generate nomor transaksi baru
$nomor_transaksi_baru = 'TRX-' . time(); // Format: TRX-1674601234

// Proses penyetoran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siswa_id = $_POST['siswa_id'] ?? null;
    $nomor = $_POST['nomor'] ?? null;
    $tanggal = $_POST['tanggal'] ?? null;
    $nominal = $_POST['nominal'] ?? null;
    $keterangan = $_POST['keterangan'] ?? null;

    // Validasi input
    if (!$siswa_id || !$nomor || !$tanggal || !$nominal || !$keterangan) {
        echo "<script>alert('Semua kolom harus diisi!');</script>";
    } elseif (!is_numeric($nominal) || $nominal <= 0) {
        echo "<script>alert('Nominal harus berupa angka positif!');</script>";
    } else {
        // Cek apakah siswa ada di database
        $query_cek_siswa = "SELECT saldo FROM siswa WHERE id = :siswa_id";
        $stmt_cek_siswa = $conn->prepare($query_cek_siswa);
        $stmt_cek_siswa->bindParam(':siswa_id', $siswa_id);
        $stmt_cek_siswa->execute();
        $siswa = $stmt_cek_siswa->fetch(PDO::FETCH_ASSOC);

        if (!$siswa) {
            echo "<script>alert('Siswa tidak ditemukan!');</script>";
        } else {
            try {
                // Mulai transaksi database
                $conn->beginTransaction();

                // Masukkan data ke tabel transaksi
                $query_transaksi = "INSERT INTO transaksi (siswa_id, nomor, tanggal, nominal, keterangan) 
                                    VALUES (:siswa_id, :nomor, :tanggal, :nominal, :keterangan)";
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
                    
                    if ($stmt_saldo->execute()) {
                        $conn->commit(); // Konfirmasi transaksi
                        echo "<script>alert('Transaksi berhasil disimpan dan saldo siswa diperbarui');</script>";
                    } else {
                        $conn->rollBack(); // Batalkan transaksi jika gagal
                        echo "<script>alert('Gagal memperbarui saldo siswa!');</script>";
                    }
                } else {
                    $conn->rollBack();
                    echo "<script>alert('Gagal menyimpan transaksi!');</script>";
                }
            } catch (Exception $e) {
                $conn->rollBack();
                echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Penyetoran</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #dfefff, #dfefff);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 16px;
            color: #555;
            margin-bottom: 5px;
        }

        select, input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: #fafafa;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .full-width {
            grid-column: span 2;
        }

        .button-container {
            text-align: center;
            grid-column: span 2;
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

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>INPUT PENYETORAN</h1>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="siswa_id">Siswa:</label>
                    <select name="siswa_id" required>
                        <?php foreach ($result_siswa as $row): ?>
                            <option value="<?= htmlspecialchars($row['id']); ?>">
                                <?= htmlspecialchars($row['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Nomor Transaksi:</label>
                    <input type="hidden" name="nomor" value="<?= htmlspecialchars($nomor_transaksi_baru); ?>">
                    <input type="text" value="<?= htmlspecialchars($nomor_transaksi_baru); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Tanggal:</label>
                    <input type="datetime-local" name="tanggal" required>
                </div>

                <div class="form-group">
                    <label>Nominal:</label>
                    <input type="number" name="nominal" required>
                </div>

                <div class="form-group full-width">
                    <label>Deskripsi:</label>
                    <textarea name="keterangan" required></textarea>
                </div>
            </div>
            <br>
            <div class="button-container">
                <button type="submit">Simpan</button>
            </div>
        </form>
    </div>
</body>
</html>
