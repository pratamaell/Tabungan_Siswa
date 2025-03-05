<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361EE;
            --secondary-color: #3A0CA3;
            --accent-color: #4CC9F0;
            --dark-color: #0F1729;
            --light-color: #F8F9FA;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, var(--dark-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .error-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 3rem;
            max-width: 1200px;
            width: 90%;
            margin: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }

        .error-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: var(--light-color);
        }

        .error-code {
            font-size: 8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 1rem;
            position: relative;
        }

        .error-code::after {
            content: attr(data-text);
            position: absolute;
            left: 0;
            top: 0;
            z-index: -1;
            filter: blur(10px);
            opacity: 0.5;
        }

        .error-message {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .error-description {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .home-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: fit-content;
        }

        .home-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }

        .illustration-container {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .illustration-container svg {
            width: 100%;
            height: auto;
            max-width: 400px;
            filter: drop-shadow(0 0 20px rgba(76, 201, 240, 0.3));
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .floating-element {
            position: absolute;
            animation: float 6s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        @media (max-width: 968px) {
            .error-container {
                grid-template-columns: 1fr;
                text-align: center;
                padding: 2rem;
            }

            .error-content {
                align-items: center;
                order: 2;
            }

            .illustration-container {
                order: 1;
            }

            .error-code {
                font-size: 6rem;
            }

            .error-message {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <h1 class="error-code" data-text="404">404</h1>
            <p class="error-message">Oops! Halaman Tidak Ditemukan</p>
            <p class="error-description">
                Halaman yang Anda cari telah berpindah ke dimensi lain atau tidak pernah ada sejak awal.
                Mari kembali ke beranda untuk memulai perjalanan baru!
            </p>
            <a href="login_register.php" class="home-button">
                <i class="fas fa-home"></i>
                Kembali ke Beranda
            </a>
        </div>
        
        <div class="illustration-container">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <!-- existing SVG code -->
            </svg>
            <div class="floating-elements">
                <i class="fas fa-star floating-element" style="color: var(--accent-color); font-size: 24px; top: 10%; left: 10%;"></i>
                <i class="fas fa-rocket floating-element" style="color: var(--primary-color); font-size: 32px; top: 40%; right: 20%;"></i>
                <i class="fas fa-meteor floating-element" style="color: var(--secondary-color); font-size: 28px; bottom: 20%; left: 30%;"></i>
            </div>
        </div>
    </div>

    <script>
        // Add random delays to floating elements
        document.querySelectorAll('.floating-element').forEach(element => {
            element.style.animationDelay = Math.random() * -6 + 's';
        });
    </script>
</body>
</html>