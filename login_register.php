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
    <title>Login - Tabungan Siswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);
            --secondary-gradient: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            --text-color: #fff;
            --input-bg: rgba(255, 255, 255, 0.08);
            --input-text: #fff;
            --button-hover: #4C51FF;
            --error-color: #ff4757;
            --success-color: #2ed573;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-color);
            position: relative;
            overflow: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #6B73FF, #000DFF);
            opacity: 0.8;
            z-index: -1;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            width: 100%;
            max-width: 440px;
            padding: 20px;
            position: relative;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            transform: scale(1);
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.1);
        }

        .form-box {
            background: var(--secondary-gradient);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .form-box:hover {
            transform: translateY(-5px);
        }

        .form-box h2 {
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
        }

        .form-box input {
            width: 100%;
            padding: 15px 20px 15px 45px;
            border: none;
            border-radius: 12px;
            background: var(--input-bg);
            color: var(--input-text);
            font-size: 16px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-box input:focus {
            background: rgba(255, 255, 255, 0.15);
            outline: none;
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 15px rgba(107, 115, 255, 0.3);
        }

        .form-box input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-box button {
            width: 100%;
            padding: 15px;
            background: #fff;
            border: none;
            border-radius: 12px;
            color: #000DFF;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-box button:hover {
            background: var(--button-hover);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            .form-box {
                padding: 30px 20px;
            }

            .logo img {
                width: 100px;
                height: 100px;
            }

            .form-box h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="gambar.png" alt="Logo" loading="lazy">
        </div>

        <div class="form-box" id="loginForm">
            <h2>Welcome Back</h2>
            <form action="auth_process.php" method="post">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Enter your email" required autocomplete="email">
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit">
                    Sign In <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Add loading state to button
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button');
            button.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Signing In...';
            button.disabled = true;
        });
    </script>
</body>
</html>