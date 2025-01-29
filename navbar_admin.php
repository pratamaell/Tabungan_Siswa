<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* Base Styles */
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: #f5f6fa;
            color: #2d3436;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(145deg, #2d3436, #3a3e41);
            color: #dfe6e9;
            position: fixed;
            top: 0;
            left: 0;
            transition: width 0.3s ease, background 0.3s ease;
            overflow: hidden;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .logo {
            text-align: center;
            padding: 20px;
            background:rgb(85, 172, 171);
            color: #2d3436;
            font-size: 30px;
            font-weight: bold;
            transition: opacity 0.3s ease, padding 0.3s ease;
        }

        .logo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
            transition: width 0.3s ease, height 0.3s ease;
        }

        .sidebar.collapsed .logo img {
            width: 40px;
            height: 40px;
        }

        .sidebar.collapsed .logo {
            padding: 10px 0;
            font-size: 0;
        }

        #sidebar-title {
            text-align: center;
            font-size: 20px;
            margin: 20px 0;
            color: #00cec9;
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed #sidebar-title {
            opacity: 0;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        ul li {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            transition: background 0.3s ease, color 0.3s ease;
        }

        ul li:hover {
            background: #00cec9;
            color: #2d3436;
            box-shadow: inset 5px 0 5px rgba(0, 0, 0, 0.2);
        }

        ul li a {
            text-decoration: none;
            color: inherit;
            font-size: 16px;
            display: flex;
            align-items: center;
            width: 100%;
            transition: color 0.3s ease;
        }

        ul li a .icon {
            font-size: 20px;
            margin-right: 15px;
            transition: margin 0.3s ease;
        }

        ul li a .text {
            flex-grow: 1;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed ul li a .icon {
            margin-right: 0;
            text-align: center;
            width: 100%;
        }

        .sidebar.collapsed ul li a .text {
            opacity: 0;
            pointer-events: none;
        }

        /* Toggle Button */
        .toggle-btn {
            position: absolute;
            top: 20px;
            right: -20px;
            width: 40px;
            height: 40px;
            background: #00cec9;
            border: none;
            color: #2d3436;
            font-size: 20px;
            cursor: pointer;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .toggle-btn:hover {
            background: #74b9ff;
            transform: rotate(90deg);
        }

        /* Content Area */
        body .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        body.collapsed .content {
            margin-left: 70px;
        }

        /* Media Query for Mobile */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="ikon.png" alt="Logo">
        </div>
        <button class="toggle-btn" id="toggle-btn" onclick="toggleSidebar()">☰</button>
        <ul>
            <li><a href="dashboard_admin.php"><i class="icon fas fa-home"></i><span class="text">Home Admin</span></a></li>
            <li><a href="#"><i class="icon fas fa-user-graduate"></i><span class="text">Kelola Siswa</span></a></li>
            <li><a href="#"><i class="icon fas fa-chalkboard"></i><span class="text">Kelola Kelas</span></a></li>
            <li><a href="#"><i class="icon fas fa-money-bill-alt"></i><span class="text">Laporan Keuangan</span></a></li>
            <li><a href="manajemen_akun_pengaturan.php"><i class="icon fas fa-cogs"></i><span class="text">Akun</span></a></li>
            <li><a href="logout.php"><i class=" icon fas fa-sign-out-alt"></i><span class="text">Logout</span></a></li>
        </ul>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;

            sidebar.classList.toggle('collapsed');
            body.classList.toggle('collapsed');
        }
    </script>
</body>
</html>
