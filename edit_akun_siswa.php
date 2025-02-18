<?php
session_start();
include 'config/database.php';
include 'navbar_siswa.php';

$database = new Database();
$conn = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Ambil data siswa berdasarkan user_id
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

// Ambil daftar kelas untuk opsi dropdown
$query_kelas = "SELECT id, nama_kelas FROM kelas";
$stmt_kelas = $conn->prepare($query_kelas);
$stmt_kelas->execute();
$kelas_list = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $kelas_id = $_POST['kelas'];

    // Update data user
    $query_update_user = "UPDATE users SET name = :name, email = :email WHERE id = :user_id";
    $stmt_update_user = $conn->prepare($query_update_user);
    $stmt_update_user->execute([
        'name' => $nama,
        'email' => $email,
        'user_id' => $user_id
    ]);

    // Update data siswa
    $query_update_siswa = "UPDATE siswa SET kelas_id = :kelas_id WHERE user_id = :user_id";
    $stmt_update_siswa = $conn->prepare($query_update_siswa);
    $stmt_update_siswa->execute([
        'kelas_id' => $kelas_id,
        'user_id' => $user_id
    ]);

    // Redirect dengan pesan sukses
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Profil berhasil diperbarui.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'profil_siswa.php';
            });
        });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg,rgb(175, 173, 189),rgb(65, 60, 123));
            color: #fff;
            padding-top: 80px;
        }

        .main-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .form-container {
            background: rgb(64, 55, 135);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            margin-top: 20px;
        }

        .form-container h3 {
            margin-bottom: 20px;
            font-size: 22px;
            color: rgb(232, 231, 241);
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-container input,
        .form-container select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: none;
        }

        .form-container .save-button {
            padding: 10px;
            background: #6c5ce7;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .form-container .save-button:hover {
            background: #a29bfe;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="form-container">
            <h3>Ubah Data</h3>
            <form method="POST">
                <label for="nama">Nama:</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($profil['nama_siswa']); ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profil['email_siswa']); ?>" required>

                <label for="kelas">Kelas:</label>
                <select id="kelas" name="kelas" required>
                    <?php foreach ($kelas_list as $kelas): ?>
                        <option value="<?php echo $kelas['id']; ?>" <?php echo ($kelas['id'] == $profil['kelas_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="save-button">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</body>
</html>
