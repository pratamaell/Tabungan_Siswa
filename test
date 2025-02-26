<?php
require_once 'config/database.php';

// Ambil daftar kelas dari database
try {
    $db = (new Database())->getConnection();
    $query = "SELECT id, nama_kelas FROM kelas";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $kelasList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kelasList = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register - Tabungan Siswa</title>
    <style>
        /* General Styling */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }
        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        .logo {
            margin-bottom: 20px;
        }
        .logo img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
        }
        .form-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .form-box h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-box input, .form-box select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            outline: none;
        }
        .form-box button {
            width: 100%;
            padding: 10px;
            background: #1e3c72;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .form-box button:hover {
            background: #2a5298;
        }
        .form-box .toggle-link {
            margin-top: 10px;
            color: #fff;
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="gambar.png" alt="Logo">
        </div>

        <!-- Form Login -->
        <div class="form-box" id="loginForm">
            <h2>Login</h2>
            <form action="auth_process.php" method="post">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="toggle-link" onclick="toggleForm()">Belum punya akun? Daftar</div>
        </div>

        <!-- Form Register -->
        <div class="form-box" id="registerForm" style="display: none;">
            <h2>Register</h2>
            <form action="register_process.php" method="post">
                <input type="text" name="name" placeholder="Nama" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                
                <select name="role" id="roleSelect" required onchange="toggleKelasField()">
                    <option value="">Pilih Role</option>
                    <option value="siswa">Siswa</option>
                    <option value="admin">Admin</option>
                    <option value="bendahara">Bendahara</option>
                </select>
                
                <!-- Dropdown Kelas dari Database -->
                <select name="kelas_id" id="kelasField" style="display: none;">
                    <option value="">Pilih Kelas</option>
                    <?php foreach ($kelasList as $kelas) : ?>
                        <option value="<?= htmlspecialchars($kelas['id']) ?>">
                            <?= htmlspecialchars($kelas['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Register</button>
            </form>
            <div class="toggle-link" onclick="toggleForm()">Sudah punya akun? Login</div>
        </div>
    </div>

    <script>
        function toggleForm() {
            document.getElementById("loginForm").style.display = 
                document.getElementById("loginForm").style.display === "none" ? "block" : "none";
            document.getElementById("registerForm").style.display = 
                document.getElementById("registerForm").style.display === "none" ? "block" : "none";
        }

        function toggleKelasField() {
            const roleSelect = document.getElementById("roleSelect");
            const kelasField = document.getElementById("kelasField");
            kelasField.style.display = roleSelect.value === "siswa" ? "block" : "none";
        }
    </script>
</body>
</html>
