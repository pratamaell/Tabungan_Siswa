<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';
include 'navbar_Admin.php';

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
        echo "<script>alert('Terjadi kesalahan saat menambahkan akun');</script>";
    }
}

// Ambil data akun
$query = "SELECT * FROM users WHERE role IN ('admin', 'bendahara')";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses hapus akun
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $query_delete = "DELETE FROM users WHERE id = :id";
    $stmt_delete = $db->prepare($query_delete);
    $stmt_delete->bindParam(':id', $delete_id);

    if ($stmt_delete->execute()) {
        header("Location: manage_accounts.php");
    }
}

// Proses update akun
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_account'])) {
    $edit_id = $_POST['edit_id'];
    $edit_name = $_POST['edit_name'];
    $edit_email = $_POST['edit_email'];
    $edit_role = $_POST['edit_role'];

    $query_edit = "UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id";
    $stmt_edit = $db->prepare($query_edit);
    $stmt_edit->bindParam(':name', $edit_name);
    $stmt_edit->bindParam(':email', $edit_email);
    $stmt_edit->bindParam(':role', $edit_role);
    $stmt_edit->bindParam(':id', $edit_id);

    if ($stmt_edit->execute()) {
        echo "<script>alert('Akun berhasil diperbarui');</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memperbarui akun');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Akun</title>
    <style>
        /* General styling */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg,rgb(235, 240, 244), #74b9ff);
            color: #fff;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: #2d3436;
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #fff;
        }

        table th {
            background: #0984e3;
            color: #fff;
        }

        table tr:nth-child(even) {
            background-color: #34495e;
        }

        button {
            background-color: #0984e3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #74b9ff;
        }

        .form-container {
            background: #34495e;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .form-container input,
        .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: none;
            background-color: #dfe6e9;
            color: #2d3436;
        }

        .form-container button {
            width: 100%;
            background-color: #00cec9;
            border-radius: 5px;
            font-size: 16px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manajemen Akun</h1>

        <!-- Form untuk menambah akun -->
        <div class="form-container">
            <h3>Tambah Akun</h3>
            <form method="POST" action="">
                <input type="text" name="name" placeholder="Nama" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="bendahara">Bendahara</option>
                </select>
                <button type="submit" name="add_account">Tambah Akun</button>
            </form>
        </div>

        <!-- Tabel untuk menampilkan daftar akun -->
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td>
                        <a href="edit_account.php?id=<?php echo $user['id']; ?>"><button>Edit</button></a>
                        <a href="?delete_id=<?php echo $user['id']; ?>"><button>Hapus</button></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
