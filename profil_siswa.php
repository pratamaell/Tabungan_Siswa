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
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg,rgb(175, 173, 189),rgb(65, 60, 123));
            color: #fff;
            padding-top: 80px; /* Offset for the fixed navbar */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .main-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .header {
            background: #fdcb6e;
            padding: 20px;
            border-radius: 10px;
            color: #2d3436;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            margin-left:380px;
            width: 100%;
            max-width: 700px;
        }

        .profile-container {
            background:rgb(64, 55, 135);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            color: #333;
            margin-left:380px;
        }

        .profile-container h3 {
            margin-bottom: 20px;
            font-size: 22px;
            color:rgb(232, 231, 241);
        }

        .profile-container .profile-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .profile-container .profile-info div {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 5px;
        }

        .profile-container .profile-info div span {
            font-weight: bold;
        }

        .profile-container .profile-info div p {
            margin: 0;
        }

        .profile-container .edit-button {
            margin-top: 20px;
            padding: 10px;
            background: #6c5ce7;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .profile-container .edit-button:hover {
            background: #a29bfe;
        }

        @media (max-width: 768px) {
            .home-section {
                left: 0;
                width: 100%;
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
                <button class="edit-button">Edit Profil</button>
            </div>
        </div>
    </div>
</body>
</html>