<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar Bendahara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: #f4f7fc;
        }

        .navbar {
            width: 100%;
            background: linear-gradient(90deg, #2d3436, #00a8ff);
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .navbar .logo {
            display: flex;
            align-items: center;
        }

        .navbar .logo img {
            height: 40px;
            margin-right: 10px;
        }

        .navbar .logo span {
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .navbar ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
            flex-grow: 1;
            justify-content: center;
        }

        .navbar ul li {
            margin: 0 20px;
        }

        .navbar ul li a {
            color: #ffffff;
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .navbar ul li a i {
            margin-right: 8px;
        }

        .navbar ul li a:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .navbar .logout {
            margin-left: auto;
        }

        .main-content {
            margin-top: 80px; /* Adjust based on navbar height */
            padding: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar ul {
                flex-direction: column;
                align-items: flex-start;
                background: #2d3436;
                position: absolute;
                top: 70px;
                left: 0;
                width: 100%;
                padding: 15px 20px;
                display: none;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            }

            .navbar ul.show {
                display: flex;
            }

            .navbar ul li {
                margin: 10px 0;
            }

            .menu-toggle {
                display: block;
                cursor: pointer;
                font-size: 24px;
                color: #ffffff;
            }

            .navbar .logout {
                margin-left: 0;
            }
        }

        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
            <span>Tabungan Siswa</span>
        </div>
        <div class="menu-toggle"><i class="fas fa-bars"></i></div>
        <ul>
            <li><a href="dashboard_bendahara.php"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="kostumisasi_struk.php"><i class="fas fa-receipt"></i>Pembayaran</a></li>
            <li><a href="penarikan_bendahara.php"><i class="fas fa-money-check-alt"></i>Penarikan</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
        </ul>
    </div>
    <script>
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.navbar ul');

        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('show');
        });
    </script>
</body>
</html>
