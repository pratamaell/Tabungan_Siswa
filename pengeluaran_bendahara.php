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
$querySaldo = "SELECT 
                    (SUM(s.saldo) - COALESCE((SELECT SUM(nominal) FROM pengeluaran), 0)) AS total_saldo 
                FROM siswa s";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3a7bd5;
            --primary-gradient: linear-gradient(to right, #3a7bd5, #00d2ff);
            --secondary-color: #6c7989;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: radial-gradient(circle at top right, rgba(58, 123, 213, 0.1), transparent);
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 600px;
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .container::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 150px;
            height: 150px;
            background: var(--primary-gradient);
            border-radius: 50%;
            opacity: 0.2;
            z-index: -1;
        }

        .container::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: var(--primary-gradient);
            border-radius: 50%;
            opacity: 0.2;
            z-index: -1;
        }

        h1 {
            margin-bottom: 30px;
            color: var(--dark-color);
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-gradient);
            border-radius: 2px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            position: relative;
            margin-bottom: 5px;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }

        input, textarea {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 1px solid #e1e5ea;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            box-sizing: border-box;
            background: var(--light-color);
            font-family: 'Poppins', sans-serif;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(58, 123, 213, 0.2);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        button {
            padding: 15px 20px;
            background: var(--primary-gradient);
            color: #fff;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-size: 16px;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 5px 15px rgba(58, 123, 213, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 1;
            font-family: 'Poppins', sans-serif;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            z-index: -1;
        }

        button:hover::before {
            width: 100%;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(58, 123, 213, 0.4);
        }

        .status-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background: rgba(58, 123, 213, 0.1);
            padding: 15px;
            border-radius: var(--border-radius);
        }

        .status-item {
            text-align: center;
        }

        .status-item .value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .status-item .label {
            font-size: 0.8rem;
            color: var(--secondary-color);
            margin-top: 5px;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            animation: fadeIn 0.5s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-money-bill-wave" style="margin-right: 10px; color: var(--primary-color);"></i>Catat Pengeluaran</h1>
        
        <div class="status-container">
            <div class="status-item">
                <div class="value">Rp <?= number_format($totalSaldo, 0, ',', '.') ?></div>
                <div class="label">Total Saldo</div>
            </div>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <i class="fas fa-calendar-alt"></i>
                <input type="date" name="tanggal" required>
            </div>
            
            <div class="form-group">
                <i class="fas fa-money-bill-alt"></i>
                <input type="number" name="nominal" placeholder="Nominal Pengeluaran" required>
            </div>
            
            <div class="form-group">
                <i class="fas fa-sticky-note"></i>
                <textarea name="keterangan" placeholder="Keterangan Pengeluaran" required></textarea>
            </div>
            
            <button type="submit">
                <i class="fas fa-save" style="margin-right: 10px;"></i>Simpan Pengeluaran
            </button>
        </form>
    </div>

    <script>
        // Cek apakah ada alert dari session
        <?php if (isset($_SESSION['alert'])) : ?>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['icon'] ?>',
                title: '<?= $_SESSION['alert']['title'] ?>',
                text: '<?= $_SESSION['alert']['text'] ?>',
                confirmButtonColor: '#3a7bd5',
                background: '#fff',
                backdrop: `
                    rgba(0,0,123,0.4)
                    url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M25.3995 25.3995C37.8787 12.9203 57.9597 12.9203 70.4389 25.3995C82.9181 37.8787 82.9181 57.9597 70.4389 70.4389C57.9597 82.9181 37.8787 82.9181 25.3995 70.4389C12.9203 57.9597 12.9203 37.8787 25.3995 25.3995Z' fill='none' fill-rule='evenodd' stroke-width='3'/%3E%3C/svg%3E")
                    center top
                    no-repeat
                `,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>

        // Tambahkan efek ripple pada button
        const button = document.querySelector('button');
        button.addEventListener('mousedown', function(e) {
            const x = e.clientX - e.target.getBoundingClientRect().left;
            const y = e.clientY - e.target.getBoundingClientRect().top;
            
            const ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.width = '100px';
            ripple.style.height = '100px';
            ripple.style.borderRadius = '50%';
            ripple.style.backgroundColor = 'rgba(255, 255, 255, 0.4)';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.style.transform = 'translate(-50%, -50%) scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.pointerEvents = 'none';
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
        
        // Definisikan animasi ripple
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: translate(-50%, -50%) scale(3);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>