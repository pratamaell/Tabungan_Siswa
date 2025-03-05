<?php
require_once 'config/database.php';

try {
    $db = (new Database())->getConnection();
    
    // Query untuk menghitung total siswa aktif dan total saldo
    $query_stats = "SELECT 
        (SELECT COUNT(*) FROM siswa s 
         INNER JOIN users u ON s.user_id = u.id 
         WHERE u.role = 'siswa') as total_siswa,
        (SELECT COALESCE(SUM(saldo), 0) FROM siswa) as total_saldo";
    
    $stmt_stats = $db->prepare($query_stats);
    $stmt_stats->execute();
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    $total_siswa = $stats['total_siswa'];
    $total_saldo = $stats['total_saldo'];

} catch (PDOException $e) {
    $total_siswa = 0;
    $total_saldo = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tabungan Digital Siswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #7C3AED;
            --accent: #38BDF8;
            --dark: #1E293B;
            --light: #F8FAFC;
            --success: #10B981;
            --warning: #F59E0B;
            --error: #EF4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--dark), #0F172A);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            overflow: hidden;
        }

        .brand-section {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: var(--light);
            position: relative;
            overflow: hidden;
        }

        .brand-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
        }

        .brand-logo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 24px;
            margin-bottom: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: float 6s ease-in-out infinite;
        }

        .brand-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
            background: linear-gradient(to right, var(--accent), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-description {
            font-size: 1.25rem;
            line-height: 1.6;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .fun-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-item i {
            font-size: 2rem;
            color: var(--accent);
        }

        .login-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-title {
            font-size: 2.5rem;
            color: var(--light);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.7);
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-label {
            display: block;
            color: #ffff;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent);
            font-size: 1.2rem;
        }

        .input-field {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--light);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.1);
        }

        .login-button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .floating-shapes div {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        @media (max-width: 1024px) {
            body {
                grid-template-columns: 1fr;
            }
            .brand-section {
                display: none;
            }
            .login-section {
                padding: 2rem;
            }
        }

        .page-rocket {
    position: fixed;
    font-size: 1.5rem;
    z-index: 9999;
    pointer-events: none;
    transform-origin: center;
}

@keyframes fadeOut {
    from {
        transform: scale(1) translateY(0);
        opacity: 0.7;
    }
    to {
        transform: scale(4) translateY(50px);
        opacity: 0;
    }
}



    </style>
</head>
<body>
    <section class="brand-section">
        <div class="brand-content">
            <img src="gambar.png" alt="Logo" class="brand-logo">
            <h1 class="brand-title">Celengan Digital 🎯</h1>
            <p class="brand-description">
                Yuk! Nabung bersama kami dengan cara yang seru dan aman! 🚀 
                Setiap koin yang kamu simpan adalah langkah menuju impian besarmu! ✨
            </p>
            
             <div class="fun-stats">
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <div>
                        <h3><?php echo number_format($total_siswa) ; ?>+</h3>
                        <p>Penabung Aktif</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-star"></i>
                    <div>
                        <h3>⭐⭐⭐⭐⭐</h3>
                        <p>Rating Terpercaya</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="floating-shapes"></div>
    </section>

    <section class="login-section">
        <div class="login-container">
            <div class="login-header">
                <h2 class="login-title">Halo! 👋</h2>
                <p class="login-subtitle">Siap untuk mulai menabung?</p>
            </div>

            <form action="auth_process.php" method="post" id="loginForm">
                <div class="input-group">
                    <label class="input-label" for="email" placeholder="Email Kamu">Email Kamu</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="input-field" required>
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label" for="password" placeholder="Kata Sandi">Kata Sandi</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="input-field" required>
                    </div>
                </div>

                <button type="submit" class="login-button" id="loginButton">
                    Mulai Menabung! 🚀
                </button>
            </form>
        </div>
    </section>

    <script>
        // Create floating shapes
        function createFloatingShapes() {
            const shapes = document.querySelector('.floating-shapes');
            for (let i = 0; i < 10; i++) {
                const shape = document.createElement('div');
                shape.style.width = Math.random() * 100 + 20 + 'px';
                shape.style.height = shape.style.width;
                shape.style.left = Math.random() * 100 + '%';
                shape.style.top = Math.random() * 100 + '%';
                shape.style.animationDelay = Math.random() * 5 + 's';
                shapes.appendChild(shape);
            }
        }

        // Initialize shapes
        document.addEventListener('DOMContentLoaded', createFloatingShapes);

        document.getElementById('loginForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const button = document.getElementById('loginButton');
    const form = e.target;
    
    // Get the position of the rocket emoji in the button
    const buttonRect = button.getBoundingClientRect();
    const rocketStartX = buttonRect.right - 30; // Adjust based on rocket position
    const rocketStartY = buttonRect.top + (buttonRect.height / 2);
    
    // Create page-wide rocket
    const pageRocket = document.createElement('div');
    pageRocket.innerHTML = '🚀';
    pageRocket.className = 'page-rocket';
    document.body.appendChild(pageRocket);
    
    // Position rocket initially at button's rocket position
    pageRocket.style.position = 'fixed';
    pageRocket.style.left = `${rocketStartX}px`;
    pageRocket.style.top = `${rocketStartY}px`;
    
    // Change button text (remove rocket emoji)
    button.textContent = 'Tunggu Sebentar...';
    button.disabled = true;
    button.classList.add('loading');

    // Add trail effect
    function createTrail() {
        const trail = document.createElement('div');
        trail.innerHTML = '✨';
        trail.style.position = 'fixed';
        trail.style.left = pageRocket.style.left;
        trail.style.top = pageRocket.style.top;
        trail.style.fontSize = '1rem';
        trail.style.opacity = '0.7';
        trail.style.animation = 'fadeOut 1s forwards';
        document.body.appendChild(trail);
        setTimeout(() => trail.remove(), 1000);
    }

    // Update CSS animation
    pageRocket.style.transition = 'all 1.5s cubic-bezier(0.4, 0, 0.2, 1)';
    setTimeout(() => {
        pageRocket.style.left = '90%';
        pageRocket.style.top = '-100px';
        pageRocket.style.transform = 'rotate(45deg) scale(2)';
        pageRocket.style.opacity = '0';
    }, 100);
    
    // Create trail effect
    let trailInterval = setInterval(createTrail, 50);

    // Submit form after animation
    setTimeout(() => {
        clearInterval(trailInterval);
        pageRocket.remove();
        form.submit();
    }, 1000);
});
    </script>
</body>
</html>