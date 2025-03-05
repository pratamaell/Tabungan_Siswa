<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];
    
    // Verify current password
    $verify_query = "SELECT password FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($verify_query);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_query = "UPDATE users SET password = :password WHERE id = :user_id";
            $stmt = $conn->prepare($update_query);
            $stmt->execute([
                'password' => $hashed_password,
                'user_id' => $user_id
            ]);
            
            $success = "Password berhasil diubah!";
        } else {
            $error = "Password baru tidak cocok!";
        }
    } else {
        $error = "Password saat ini salah!";
    }
}

// Include navbar after all header operations
include 'navbar_siswa.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4070f4;
            --secondary: #3f37c9;
            --background: #f8fafc;
            --card-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e6e5e5;
            --gradient: linear-gradient(135deg, #4070f4, #3f37c9);
        }

        .home-section {
            position: relative;
            background: var(--background);
            min-height: 100vh;
            left: 250px;
            width: calc(100% - 250px);
            transition: all 0.5s ease;
            padding: 12px;
        }

        .sidebar.close ~ .home-section {
            left: 73px;
            width: calc(100% - 73px);
        }

        .card-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--card-bg);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .card-title {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(64, 112, 244, 0.1);
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: var(--gradient);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .home-section {
                left: 73px;
                width: calc(100% - 73px);
                padding: 0.5rem;
            }

            .card-container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="home-section">
        <div class="card-container">
            <div class="card-title">Ubah Password</div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message" style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <script>
                    setTimeout(() => {
                        window.location.href = 'profil_siswa.php';
                    }, 2000);
                </script>
            <?php endif; ?>

            <form method="POST">
            <div class="form-group">
                    <label>Password Saat Ini</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-submit">Ubah Password</button>
             </div>
            </form>
        </div>
    </div>
</body>
</html>