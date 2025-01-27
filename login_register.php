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
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: #fff;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .form-box {
            background: #2d3436;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        .form-box h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #dfe6e9;
        }
        .form-box label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #b2bec3;
        }
        .form-box input[type="text"],
        .form-box input[type="email"],
        .form-box input[type="password"],
        .form-box select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
            background: #dfe6e9;
            color: #2d3436;
            font-size: 16px;
        }
        .form-box button {
            width: 100%;
            padding: 10px;
            background: #0984e3;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s;
        }
        .form-box button:hover {
            background: #74b9ff;
        }
        .form-box .toggle-link {
            text-align: center;
            margin-top: 10px;
        }
        .form-box .toggle-link a {
            color: #74b9ff;
            text-decoration: none;
            font-weight: bold;
        }
        .form-box .toggle-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h2 id="form-title">Login</h2>
            <form id="auth-form" action="auth_process.php" method="POST">
                <!-- Name Input for Register -->
                <div id="name-section" style="display: none;">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" name="name" id="name" placeholder="Masukkan nama lengkap">
                </div>

                <!-- Email Input -->
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Masukkan email Anda" required>

                <!-- Password Input -->
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Masukkan password Anda" required>

                <!-- Role Selection for Register -->
                <div id="role-section" style="display: none;">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="siswa">Siswa</option>
                        <option value="bendahara">Bendahara</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submit-button">Login</button>

                <!-- Toggle Link -->
                <div class="toggle-link">
                    <a href="#" id="toggle-link">Belum punya akun? Daftar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle between Login and Register
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
