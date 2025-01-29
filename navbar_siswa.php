<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Resetting body styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f8f9fd;
        }

        /* Navbar styles */
        .navbar {
            width: 100%;
            height: 60px;
            background: #357ABD;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 200;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .navbar .logo {
            display: flex;
            align-items: center;
        }

        .navbar .logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .navbar h1 {
            font-size: 20px;
            margin: 0;
        }

        .navbar .nav-links {
            display: flex;
            align-items: center;
            margin-right: 40px;
        }

        .navbar .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .navbar .nav-links a:hover {
            color: #aed4ff;
        }

        /* Content area styles */
        .content {
            margin-top: 80px; /* Offset for the navbar */
            padding: 20px;
            flex: 1;
            overflow-y: auto;
        }

        .content h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <img src="ikon.png" alt="Logo Sekolah">
            <h1>Halaman Siswa</h1>
        </div>
        <div class="nav-links">
            <a href="dashboard_siswa.php"><i class="fas fa-home"></i> Beranda</a>
            <a href="penarikan_siswa.php"><i class="fas fa-hand-holding-usd"></i> Penarikan</a>
            <a href="riwayat_siswa.php"><i class="fas fa-history"></i> Riwayat</a>
            <a href="profil_siswa.php"><i class="fas fa-user"></i> Profil</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>



</body>
</html>
