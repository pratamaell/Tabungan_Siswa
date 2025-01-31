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

// Proses tambah akun
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_account'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $query = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':role', $role);

    if ($stmt->execute()) {
        echo "<script>alert('Akun berhasil ditambahkan');</script>";
    } else {
        echo "<script>alert('Gagal menambahkan akun');</script>";
    }
}

// Ambil data akun
$query = "SELECT * FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Akun</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #dff9fb, #c7ecee);
            color: #333;
        }
        .home-section {
            position: relative;
            background: #E4E9F7;
            min-height: 100vh;
            top: 0;
            left: 78px;
            width: calc(100% - 78px);
            transition: all 0.5s ease;
            z-index: 2;
            padding: 20px;
        }
        .sidebar.open ~ .home-section {
            left: 250px;
            width: calc(100% - 250px);
        }
        .header {
            background: #74b9ff;
            padding: 20px;
            text-align: center;
            color: #fff;
            font-size: 24px;
            font-weight: bold;
            border-radius: 8px;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .form-container h3 {
            margin-bottom: 20px;
            color: #0984e3;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container form input, .form-container form select {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container form button {
            padding: 10px;
            background: #0984e3;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .form-container form button:hover {
            background: #74b9ff;
        }
        .table-container {
            margin-top: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container table th, .table-container table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        .table-container table th {
            background: #0984e3;
            color: #fff;
        }
        @media (max-width: 768px) {
            .home-section {
                left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="home-section">
        <div class="header">Manajemen Akun</div>
        <div class="form-container">
            <h3>Tambah Akun Baru</h3>
            <form method="post">
                <input type="text" name="name" placeholder="Nama" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="">Pilih Role</option>
                    <option value="admin">Admin</option>
                    <option value="bendahara">Bendahara</option>
                    <option value="siswa">Siswa</option>
                </select>
                <button type="submit" name="add_account">Tambah Akun</button>
            </form>
        </div>
        <div class="table-container">
            <h3>Daftar Akun</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= htmlspecialchars($user['name']); ?></td>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><?= htmlspecialchars($user['role']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>