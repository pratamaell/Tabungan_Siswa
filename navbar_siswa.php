<!DOCTYPE html>
<!-- Coding by CodingNepal || www.codingnepalweb.com -->
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Siswa Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <style>
            /* Import Google font - Poppins */
            @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");
            * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
            }
            body {
            min-height: 100vh;
            background: #eef5fe;
            }
            /* Pre css */
            .flex {
            display: flex;
            align-items: center;
            }
            .nav_image {
            display: flex;
            min-width: 55px;
            justify-content: center;
            }
            .nav_image img {
            height: 35px;
            width: 35px;
            border-radius: 50%;
            object-fit: cover;
            }

            /* Sidebar */
            .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 270px;
            background: #fff;
            padding: 15px 10px;
            box-shadow: 0 0 2px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            }
            .sidebar.close {
            width: calc(55px + 20px);
            }
            .logo_items {
            gap: 8px;
            }
            .logo_name {
            font-size: 22px;
            color: #333;
            font-weight: 500px;
            transition: all 0.3s ease;
            }
            .sidebar.close .logo_name,
            .sidebar.close #lock-icon,
            .sidebar.close #sidebar-close {
            opacity: 0;
            pointer-events: none;
            }
            #lock-icon,
            #sidebar-close {
            padding: 10px;
            color: #4070f4;
            font-size: 25px;
            cursor: pointer;
            margin-left: -4px;
            transition: all 0.3s ease;
            }
            #sidebar-close {
            display: none;
            color: #333;
            }
            .menu_container {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin-top: 40px;
            overflow-y: auto;
            height: calc(100% - 82px);
            }
            .menu_container::-webkit-scrollbar {
            display: none;
            }
            .menu_title {
            position: relative;
            height: 50px;
            width: 55px;
            }
            .menu_title .title {
            margin-left: 15px;
            transition: all 0.3s ease;
            }
            .sidebar.close .title {
            opacity: 0;
            }
            .menu_title .line {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            height: 3px;
            width: 20px;
            border-radius: 25px;
            background: #aaa;
            transition: all 0.3s ease;
            }
            .menu_title .line {
            opacity: 0;
            }
            .sidebar.close .line {
            opacity: 1;
            }
            .item {
            list-style: none;
            }
            .link {
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 8px;
            color: #707070;
            }
            .link:hover {
            color: #fff;
            background-color: #4070f4;
            }
            .link span {
            white-space: nowrap;
            }
            .link i {
            height: 50px;
            min-width: 55px;
            display: flex;
            font-size: 22px;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            }

            .sidebar_profile {
            padding-top: 15px;
            margin-top: 15px;
            gap: 15px;
            border-top: 2px solid rgba(0, 0, 0, 0.1);
            }
            .sidebar_profile .name {
            font-size: 18px;
            color: #333;
            }
            .sidebar_profile .email {
            font-size: 15px;
            color: #333;
            }

            /* Navbar */
            .navbar {
            max-width: 500px;
            width: 100%;
            position: fixed;
            top: 0;
            left: 60%;
            transform: translateX(-50%);
            background: #fff;
            padding: 10px 20px;
            border-radius: 0 0 8px 8px;
            justify-content: space-between;
            }
            #sidebar-open {
            font-size: 30px;
            color: #333;
            cursor: pointer;
            margin-right: 20px;
            display: none;
            }
            .search_box {
            height: 46px;
            max-width: 500px;
            width: 100%;
            border: 1px solid #aaa;
            outline: none;
            border-radius: 8px;
            padding: 0 15px;
            font-size: 18px;
            color: #333;
            }
            .navbar img {
            height: 40px;
            width: 40px;
            margin-left: 20px;
            }

            /* Responsive */
            @media screen and (max-width: 1100px) {
            .navbar {
                left: 65%;
            }
            }
            @media screen and (max-width: 800px) {
            .sidebar {
                left: 0;
                z-index: 1000;
            }
            .sidebar.close {
                left: -100%;
            }
            #sidebar-close {
                display: block;
            }
            #lock-icon {
                display: none;
            }
            .navbar {
                left: 0;
                max-width: 100%;
                transform: translateX(0%);
            }
            #sidebar-open {
                display: block;
            }
            }
   </style>
    <!-- Boxicons CSS -->
    <link flex href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.querySelector(".sidebar");
        const sidebarOpenBtn = document.querySelector("#sidebar-open");
        const sidebarCloseBtn = document.querySelector("#sidebar-close");
        const sidebarLockBtn = document.querySelector("#lock-icon");

        if (!sidebar || !sidebarOpenBtn || !sidebarCloseBtn || !sidebarLockBtn) {
            console.error("One or more sidebar elements not found.");
            return;
        }

        const toggleLock = () => {
            sidebar.classList.toggle("locked");
            if (!sidebar.classList.contains("locked")) {
                sidebar.classList.add("hoverable");
                sidebarLockBtn.classList.replace("bx-lock-alt", "bx-lock-open-alt");
            } else {
                sidebar.classList.remove("hoverable");
                sidebarLockBtn.classList.replace("bx-lock-open-alt", "bx-lock-alt");
            }
        };

        const hideSidebar = () => {
            if (sidebar.classList.contains("hoverable")) {
                sidebar.classList.add("close");
            }
        };

        const showSidebar = () => {
            if (sidebar.classList.contains("hoverable")) {
                sidebar.classList.remove("close");
            }
        };

        const toggleSidebar = () => {
            sidebar.classList.toggle("close");
        };

        if (window.innerWidth < 800) {
            sidebar.classList.add("close");
            sidebar.classList.remove("locked");
            sidebar.classList.remove("hoverable");
        }

        sidebarLockBtn.addEventListener("click", toggleLock);
        sidebar.addEventListener("mouseleave", hideSidebar);
        sidebar.addEventListener("mouseenter", showSidebar);
        sidebarOpenBtn?.addEventListener("click", toggleSidebar);
        sidebarCloseBtn?.addEventListener("click", toggleSidebar);
    });
</script>
  </head>
  <body>
    <nav class="sidebar locked">
      <div class="logo_items flex">
        <span class="nav_image">
          <img src="gambar.png" alt="logo_img" />
        </span>
        <span class="logo_name">Tabungan</span>
      </div>
      <div class="menu_container">
        <div class="menu_items">
        <ul class="menu_item">
            <li class="item">
                <a href="dashboard_siswa.php" class="link flex">
                    <i class="bx bx-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="item">
                <a href="penarikan_siswa.php" class="link flex">
                    <i class="bx bx-wallet"></i>
                    <span>Penarikan</span>
                </a>
            </li>
            <li class="item">
                <a href="riwayat_siswa.php" class="link flex">
                    <i class="bx bx-history"></i>
                    <span>Riwayat</span>
                </a>
            </li>
            <li class="item">
                <a href="profil_siswa.php" class="link flex">
                    <i class="bx bx-user"></i>
                    <span>Profil</span>
                </a>
            </li>
            <li class="item">
                <a href="logout.php" class="link flex">
                    <i class="bx bx-log-out"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
        </div>
      </div>
    </nav>
  </body>
</html>
