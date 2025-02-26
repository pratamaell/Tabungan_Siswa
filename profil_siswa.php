<?php
session_start();
include 'config/database.php';
include 'navbar_siswa.php';

// Inisialisasi koneksi menggunakan PDO
$database = new Database();
$conn = $database->getConnection();

// Ambil data user ID dari sesi
$user_id = $_SESSION['user_id'];

// Ambil data siswa untuk ditampilkan
$query_profil = "
    SELECT 
        u.name AS nama_siswa, 
        u.email AS email_siswa, 
        k.id AS kelas_id,
        k.nama_kelas
    FROM siswa s
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN kelas k ON s.kelas_id = k.id
    WHERE u.id = :user_id
";
$stmt_profil = $conn->prepare($query_profil);
$stmt_profil->execute(['user_id' => $user_id]);
$profil = $stmt_profil->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Siswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --accent: #4cc9f0;
        --background: #f8fafc;
        --card-bg: rgba(255, 255, 255, 0.95);
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --success: #2ecc71;
        --gradient: linear-gradient(135deg, #4361ee, #3f37c9);
    }

    body {
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: var(--background);
        min-height: 100vh;
        background-image: 
            radial-gradient(at 40% 20%, rgba(67, 97, 238, 0.1) 0px, transparent 50%),
            radial-gradient(at 80% 0%, rgba(76, 201, 240, 0.1) 0px, transparent 50%),
            radial-gradient(at 0% 50%, rgba(67, 97, 238, 0.1) 0px, transparent 50%);
    }

    .home-section {
        position: relative;
        min-height: 100vh;
        width: calc(100% - 250px);
        left: 250px;
        transition: all 0.3s ease;
        padding: 2rem;
    }

    .main-content {
        max-width: 800px;
        margin: 0 auto;
        animation: fadeIn 0.5s ease-out;
    }

    .header {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .header h3 {
        margin: 0;
        font-size: 2rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-align: center;
    }

    .profile-container {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .profile-container h3 {
        color: var(--text-primary);
        font-size: 1.5rem;
        margin-bottom: 2rem;
        text-align: center;
    }

    .profile-info {
        display: grid;
        gap: 1.5rem;
    }

    .profile-info div {
        background: rgba(67, 97, 238, 0.05);
        padding: 1.5rem;
        border-radius: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.3s ease;
    }

    .profile-info div:hover {
        transform: translateX(10px);
        background: rgba(67, 97, 238, 0.1);
    }

    .profile-info span {
        color: var(--text-secondary);
        font-size: 0.9rem;
        font-weight: 500;
    }

    .profile-info p {
        color: var(--text-primary);
        font-weight: 600;
        margin: 0;
        font-size: 1.1rem;
    }

    .edit-button {
        width: 100%;
        padding: 1rem;
        margin-top: 2rem;
        background: var(--gradient);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .edit-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
    }

    @keyframes fadeIn {
        from { 
            opacity: 0;
            transform: translateY(20px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .home-section {
            width: 100%;
            left: 0;
            padding: 1rem;
        }

        .header, .profile-container {
            padding: 1.5rem;
        }

        .profile-info div {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .profile-info div:hover {
            transform: none;
        }
    }
</style>
</head>
<body>
    <div class="home-section">
        <div class="main-content">
            <div class="header">
                <h3>Profil Siswa</h3>
            </div>
            <div class="profile-container">
                <h3>Informasi Profil</h3>
                <div class="profile-info">
                    <div>
                        <span>Nama:</span>
                        <p><?php echo htmlspecialchars($profil['nama_siswa']); ?></p>
                    </div>
                    <div>
                        <span>Email:</span>
                        <p><?php echo htmlspecialchars($profil['email_siswa']); ?></p>
                    </div>
                    <div>
                        <span>Kelas:</span>
                        <p><?php echo htmlspecialchars($profil['nama_kelas']); ?></p>
                    </div>
                </div>
                <a href="edit_akun_siswa.php">
                    <button class="edit-button">Edit Profil</button>
                </a>
            </div>
        </div>
    </div>
</body>
</html>