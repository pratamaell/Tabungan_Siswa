<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';
include 'navbar_admin.php';

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT siswa.id, siswa.user_id, users.name, siswa.kelas_id, siswa.saldo FROM siswa 
              JOIN users ON siswa.user_id = users.id WHERE siswa.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$siswa) {
        echo "<script>alert('Data tidak ditemukan'); window.location='dashboard_admin.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('ID tidak valid'); window.location='dashboard_admin.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $kelas_id = $_POST['kelas'];
    $saldo = $_POST['saldo'];

    if (!is_numeric($saldo)) {
        echo "<script>alert('Saldo harus berupa angka');</script>";
    } else {
        $updateQuery = "UPDATE siswa SET kelas_id = :kelas_id, saldo = :saldo WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':kelas_id', $kelas_id);
        $updateStmt->bindParam(':saldo', $saldo);
        $updateStmt->bindParam(':id', $id);

        $updateUserQuery = "UPDATE users SET name = :name WHERE id = :user_id";
        $updateUserStmt = $db->prepare($updateUserQuery);
        $updateUserStmt->bindParam(':name', $nama);
        $updateUserStmt->bindParam(':user_id', $siswa['user_id']);

        if ($updateStmt->execute() && $updateUserStmt->execute()) {
            echo "<script>alert('Data berhasil diperbarui'); window.location='dashboard_admin.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui data');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #dff9fb, #c7ecee);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 400px;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            color: #2980b9;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #2980b9;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
        }
        button:hover {
            background: #1f618d;
        }
        .back-link {
            display: block;
            margin-top: 15px;
            text-decoration: none;
            color: #2980b9;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Siswa</h2>
        <form method="POST">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" value="<?php echo htmlspecialchars($siswa['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Kelas</label>
                <select name="kelas" required>
                    <?php
                    $kelas_query = "SELECT id, nama_kelas FROM kelas";
                    $kelas_stmt = $db->prepare($kelas_query);
                    $kelas_stmt->execute();
                    while ($kelas_row = $kelas_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($siswa['kelas_id'] == $kelas_row['id']) ? 'selected' : '';
                        echo "<option value='{$kelas_row['id']}' $selected>{$kelas_row['nama_kelas']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Saldo</label>
                <input type="number" name="saldo" value="<?php echo htmlspecialchars($siswa['saldo']); ?>" required>
            </div>
            <button type="submit">Simpan Perubahan</button>
            <a href="dashboard_admin.php" class="back-link">Kembali</a>
        </form>
    </div>
</body>
</html>
