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
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        .form-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .form-box img {
            width: 150px;
        }
        .form-box h2 {
            margin-bottom: 20px;
            font-size: 26px;
            color: #fff;
        }
        .form-box label {
            display: block;
            text-align: left;
            font-weight: bold;
            margin-bottom: 6px;
            color: #dfe6e9;
        }
        .form-box input, .form-box select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 16px;
            outline: none;
        }
        .form-box input::placeholder {
            color: #dfe6e9;
        }
        .form-box button {
            width: 100%;
            padding: 12px;
            background: #0984e3;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s;
        }
        .form-box button:hover {
            background: #74b9ff;
        }
        .toggle-link {
            margin-top: 15px;
        }
        .toggle-link a {
            color: #74b9ff;
            text-decoration: none;
            font-weight: bold;
        }
        .toggle-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-box">
            <img src="ikon.png" alt="Logo Tabungan Siswa">
            <h2 id="form-title">Login</h2>
            <form id="auth-form" action="auth_process.php" method="POST">
                <div id="name-section" style="display: none;">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" name="name" id="name" placeholder="Masukkan nama lengkap">
                </div>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Masukkan email Anda" required>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Masukkan password Anda" required>
                <div id="role-section" style="display: none;">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="siswa">Siswa</option>
                        <option value="bendahara">Bendahara</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" id="submit-button">Login</button>
                <div class="toggle-link">
                    <a href="#" id="toggle-link">Belum punya akun? Daftar</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        const formTitle = document.getElementById('form-title');
        const authForm = document.getElementById('auth-form');
        const nameSection = document.getElementById('name-section');
        const roleSection = document.getElementById('role-section');
        const submitButton = document.getElementById('submit-button');
        const toggleLink = document.getElementById('toggle-link');

        toggleLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (formTitle.textContent === 'Login') {
                formTitle.textContent = 'Register';
                submitButton.textContent = 'Register';
                nameSection.style.display = 'block';
                roleSection.style.display = 'block';
                toggleLink.textContent = 'Sudah punya akun? Login';
                authForm.action = 'register_process.php';
            } else {
                formTitle.textContent = 'Login';
                submitButton.textContent = 'Login';
                nameSection.style.display = 'none';
                roleSection.style.display = 'none';
                toggleLink.textContent = 'Belum punya akun? Daftar';
                authForm.action = 'auth_process.php';
            }
        });
    </script>
</body>
</html>
