<?php
session_start();
include 'config/database.php';
include 'navbar_siswa.php';

// Inisialisasi koneksi menggunakan PDO
$database = new Database();
$conn = $database->getConnection();

// Ambil data user ID dari sesi
$user_id = $_SESSION['user_id'];

// Ambil data siswa untuk ditampilkan
$query_profil = "
    SELECT 
        u.name AS nama_siswa, 
        u.email AS email_siswa, 
        k.id AS kelas_id,
        k.nama_kelas
    FROM siswa s
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN kelas k ON s.kelas_id = k.id
    WHERE u.id = :user_id
";
$stmt_profil = $conn->prepare($query_profil);
$stmt_profil->execute(['user_id' => $user_id]);
$profil = $stmt_profil->fetch(PDO::FETCH_ASSOC);

// Cek apakah tombol edit ditekan
$is_edit = isset($_GET['edit']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 90%;
            max-width: 600px;
            padding: 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .profile-info p {
            font-size: 18px;
            margin: 10px 0;
            color: #555;
        }

        .btn {
            padding: 10px 20px;
            margin: 10px 5px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-edit {
            background-color: #28a745;
            color: #fff;
        }

        .btn-edit:hover {
            background-color: #218838;
        }

        .btn-back {
            background-color: #007bff;
            color: #fff;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f9f9f9;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 6px rgba(0, 123, 255, 0.3);
        }

        .btn-submit {
            background-color: #007bff;
            color: #fff;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Profil Siswa</h1>

        <?php if ($is_edit): ?>
            <!-- Tampilkan form edit jika user menekan tombol edit -->
            <form method="POST" action="profil_siswa.php">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($profil['nama_siswa']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($profil['email_siswa']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="kelas_id">Kelas</label>
                    <select id="kelas_id" name="kelas_id" required>
                        <?php
                        // Ambil daftar kelas untuk dropdown
                        $query_kelas = "SELECT id, nama_kelas FROM kelas";
                        $stmt_kelas = $conn->prepare($query_kelas);
                        $stmt_kelas->execute();
                        $kelas = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($kelas as $k):
                        ?>
                            <option value="<?= $k['id']; ?>" <?= $k['id'] == $profil['kelas_id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($k['nama_kelas']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-submit">Simpan Perubahan</button>
            </form>
        <?php else: ?>
            <!-- Tampilkan informasi profil jika tidak dalam mode edit -->
            <div class="profile-info">
                <p><strong>Nama:</strong> <?= htmlspecialchars($profil['nama_siswa']); ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($profil['email_siswa']); ?></p>
                <p><strong>Kelas:</strong> <?= htmlspecialchars($profil['nama_kelas']); ?></p>

                <!-- Tombol Edit Profil -->
                <a href="profil_siswa.php?edit=true"><button class="btn btn-edit">Edit Profil</button></a>
            </div>
        <?php endif; ?>

        <a href="dashboard_siswa.php"><button class="btn btn-back">Kembali ke Dashboard</button></a>
    </div>
</body>
</html>