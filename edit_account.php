<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Ambil ID akun yang akan diedit
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Ambil data akun berdasarkan ID
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Proses update akun
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_account'])) {
    $edit_name = $_POST['edit_name'];
    $edit_email = $_POST['edit_email'];
    $edit_role = $_POST['edit_role'];

    $query_edit = "UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id";
    $stmt_edit = $db->prepare($query_edit);
    $stmt_edit->bindParam(':name', $edit_name);
    $stmt_edit->bindParam(':email', $edit_email);
    $stmt_edit->bindParam(':role', $edit_role);
    $stmt_edit->bindParam(':id', $user_id);

    if ($stmt_edit->execute()) {
        echo "<script>alert('Akun berhasil diperbarui'); window.location.href = 'manage_accounts.php';</script>";
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
    <title>Edit Akun</title>
    <style>
        /* General styling */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0984e3, #74b9ff);
            color: #fff;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Akun</h1>

        <!-- Form untuk mengedit akun -->
        <div class="form-container">
            <form method="POST" action="">
                <input type="text" name="edit_name" value="<?php echo $user['name']; ?>" placeholder="Nama" required>
                <input type="email" name="edit_email" value="<?php echo $user['email']; ?>" placeholder="Email" required>
                <select name="edit_role" required>
                    <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Super Admin</option>
                    <option value="bendahara" <?php if ($user['role'] == 'bendahara') echo 'selected'; ?>>Bendahara</option>
                </select>
                <button type="submit" name="edit_account">Perbarui Akun</button>
            </form>
        </div>

        <a href="manage_accounts.php"><button>Kembali ke Manajemen Akun</button></a>
    </div>
</body>
</html>
