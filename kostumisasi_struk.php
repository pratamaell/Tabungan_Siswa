<?php
ob_start(); 
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
$nomor_transaksi_baru = 'TRX-' . time();
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

                        // Redirect ke halaman generate_receipt
                        header("Location: generate_receipt.php?nomor=" . urlencode($nomor));
                        exit();
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
    <!-- Import Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Import Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --secondary-color: #4cc9f0;
            --text-color: #333;
            --text-light: #666;
            --background: #f8f9fa;
            --card-bg: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --input-bg: #f8f9fa;
            --input-border: #e2e8f0;
            --success: #10b981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px 15px;
        }

        .container {
            width: 100%;
            max-width: 850px;
            padding: 0;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            background: var(--card-bg);
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin: 0;
            letter-spacing: 1px;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 6px;
            background-color: white;
            border-radius: 3px;
        }

        .form-container {
            padding: 35px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group {
            margin-bottom: 5px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 8px;
        }

        select, input, textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            background-color: var(--input-bg);
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            transition: all 0.3s ease;
            color: var(--text-color);
        }

        select:focus, input:focus, textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 110px;
        }

        .full-width {
            grid-column: span 2;
        }

        .icon-input {
            position: relative;
        }

        .icon-input i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .icon-input input,
        .icon-input select {
            padding-left: 45px;
        }

        .transaction-id {
            background-color: rgba(67, 97, 238, 0.08);
            font-weight: 500;
            color: var(--primary-color);
        }

        .button-container {
            margin-top: 30px;
            grid-column: span 2;
        }

        button {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        button:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.25);
        }

        button:active {
            transform: translateY(0);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: span 1;
            }

            .header {
                padding: 25px 15px;
            }

            .form-container {
                padding: 25px 20px;
            }
            
            .container {
                box-shadow: none;
            }
        }

        /* Form Animations */
        select, input, textarea {
            transform-origin: left top;
            transition: transform 0.3s, border 0.3s, box-shadow 0.3s;
        }

        select:focus, input:focus, textarea:focus {
            transform: scale(1.01);
        }

        /* Loader Animation for Submit */
        .loader {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid #ffffff;
            border-bottom-color: transparent;
            border-radius: 50%;
            animation: rotate 1s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Success Indicator */
        .success-indicator {
            display: none;
            color: var(--success);
            font-size: 20px;
        }
        .home-section {
        position: relative;
        min-height: 100vh;
        width: calc(100% - 78px);
        left: 78px;
        transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="home-section">
    <div class="container">
        <div class="header">
            <h1>INPUT PENYETORAN</h1>
        </div>
        <div class="form-container">
            <form method="POST" action="" id="depositForm">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="siswa_id"><i class="fas fa-user"></i> Siswa:</label>
                        <div class="icon-input">
                            <i class="fas fa-user-graduate"></i>
                            <select name="siswa_id" required>
                                <?php foreach ($result_siswa as $row): ?>
                                    <option value="<?= htmlspecialchars($row['id']); ?>">
                                        <?= htmlspecialchars($row['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-hashtag"></i> Nomor Transaksi:</label>
                        <div class="icon-input">
                            <i class="fas fa-receipt"></i>
                            <input type="hidden" name="nomor" value="<?= htmlspecialchars($nomor_transaksi_baru); ?>">
                            <input type="text" class="transaction-id" value="<?= htmlspecialchars($nomor_transaksi_baru); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Tanggal:</label>
                        <div class="icon-input">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="datetime-local" name="tanggal" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-money-bill-wave"></i> Nominal:</label>
                        <div class="icon-input">
                            <i class="fas fa-coins"></i>
                            <input type="number" name="nominal" placeholder="Rp" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label><i class="fas fa-info-circle"></i> Deskripsi:</label>
                        <textarea name="keterangan" placeholder="Masukkan deskripsi transaksi di sini..." required></textarea>
                    </div>
                </div>
                
                <div class="button-container">
                    <button type="submit" id="submitBtn">
                        <span>Simpan Transaksi</span>
                        <i class="fas fa-save"></i>
                        <span class="loader" id="submitLoader"></span>
                        <i class="fas fa-check success-indicator" id="successIndicator"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <script>
        document.getElementById('depositForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            const submitLoader = document.getElementById('submitLoader');
            const successIndicator = document.getElementById('successIndicator');
            
            // Text content and icon
            submitBtn.querySelector('span').textContent = 'Menyimpan...';
            submitBtn.querySelector('.fa-save').style.display = 'none';
            
            // Show loader
            submitLoader.style.display = 'inline-block';
        });

        // Auto-fill current date and time
        const today = new Date();
        const formattedDate = today.toISOString().slice(0, 16);
        document.querySelector('input[type="datetime-local"]').value = formattedDate;
    </script>
</body>
</html>