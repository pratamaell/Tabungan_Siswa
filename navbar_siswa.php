<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Dashboard</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #3b82f6;
            --secondary-color: #10b981;
            --background-color: #f8fafc;
            --sidebar-color: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --hover-color: #f3f4f6;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.5;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 280px;
            background: var(--sidebar-color);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            border-right: 1px solid var(--border-color);
            transition: var(--transition);
            z-index: 1000;
            overflow: hidden;
        }

        .sidebar.close {
            width: 90px;
        }

        .sidebar .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .logo-container img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 12px;
            transition: var(--transition);
            transform: scale(1);
        }

        .logo-container img:hover {
            transform: scale(1.05);
        }

        .sidebar.close .logo-container img {
            width: 40px;
            height: 40px;
        }

        .menu-items {
            display: flex;
            flex-direction: column;
            height: calc(100% - 120px);
            padding: 20px 15px;
            overflow-y: auto;
        }

        .nav-links, .logout {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-items li {
            list-style: none;
        }

        .menu-items li a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-secondary);
            transition: var(--transition);
            position: relative;
        }

        .menu-items li a:hover {
            background-color: var(--hover-color);
            color: var(--primary-color);
        }

        .menu-items li a i {
            font-size: 22px;
            min-width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .menu-items li a:hover i {
            color: var(--primary-color);
        }

        .menu-items li a .link-name {
            font-size: 15px;
            font-weight: 500;
            white-space: nowrap;
            opacity: 1;
            transition: var(--transition);
        }

        .sidebar.close .link-name {
            opacity: 0;
            pointer-events: none;
        }

        .toggle-sidebar {
            position: absolute;
            top: 50%;
            right: -25px;
            transform: translateY(-50%);
            height: 40px;
            width: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .toggle-sidebar:hover {
            transform: translateY(-50%) scale(1.1);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 90px;
                left: -90px;
            }
            .sidebar.open {
                left: 0;
                width: 280px;
            }
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <div class="logo-container">
            <img src="logo_utama.png" alt="logo">
        </div>
        <div class="menu-items">
            <ul class="nav-links">
                <li>
                    <a href="dashboard_siswa.php">
                        <i class='bx bx-home'></i>
                        <span class="link-name">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="penarikan_siswa.php">
                        <i class='bx bx-wallet'></i>
                        <span class="link-name">Penarikan</span>
                    </a>
                </li>
                <li>
                    <a href="riwayat_siswa.php">
                        <i class='bx bx-history'></i>
                        <span class="link-name">Riwayat</span>
                    </a>
                </li>
                <li>
                    <a href="profil_siswa.php">
                        <i class='bx bx-user'></i>
                        <span class="link-name">Profil</span>
                    </a>
                </li>
            </ul>
            <ul class="logout">
                <li>
                    <a href="logout.php">
                        <i class='bx bx-log-out'></i>
                        <span class="link-name">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="toggle-sidebar">
            <i class='bx bx-chevron-left'></i>
        </div>
    </nav>

    <script>
        const sidebar = document.querySelector(".sidebar");
        const toggleBtn = document.querySelector(".toggle-sidebar");
        const mobileBreakpoint = 768;

        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            toggleBtn.querySelector("i").classList.toggle("bx-chevron-right");
            toggleBtn.querySelector("i").classList.toggle("bx-chevron-left");
        });

        // Responsive handling
        function handleResponsiveness() {
            if (window.innerWidth <= mobileBreakpoint) {
                sidebar.classList.add("close");
            } else {
                sidebar.classList.remove("close");
            }
        }

        // Initial check
        handleResponsiveness();

        // Check on resize
        window.addEventListener("resize", handleResponsiveness);
    </script>
</body>
</html>