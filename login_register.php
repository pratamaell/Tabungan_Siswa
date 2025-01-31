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
            width: 150px; /* Adjusted width */
            height: 150px; /* Adjusted height */
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
        <div class="form-box">
            <h2>Login</h2>
            <form action="login.php" method="post">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="toggle-link" onclick="toggleForm()">Belum punya akun? Daftar</div>
        </div>
        <div class="form-box" style="display: none;">
            <h2>Register</h2>
            <form action="register.php" method="post">
                <input type="text" name="name" placeholder="Nama" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="">Pilih Role</option>
                    <option value="siswa">Siswa</option>
                    <option value="admin">Admin</option>
                    <option value="bendahara">Bendahara</option>
                </select>
                <button type="submit">Register</button>
            </form>
            <div class="toggle-link" onclick="toggleForm()">Sudah punya akun? Login</div>
        </div>
    </div>
    <script>
        function toggleForm() {
            const forms = document.querySelectorAll('.form-box');
            forms.forEach(form => form.style.display = form.style.display === 'none' ? 'block' : 'none');
        }
    </script>
</body>
</html>