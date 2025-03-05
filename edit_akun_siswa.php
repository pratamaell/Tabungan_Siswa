<?php
session_start();
include 'config/database.php';
include 'navbar_siswa.php';

$database = new Database();
$conn = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Ambil data siswa berdasarkan user_id
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

// Ambil daftar kelas untuk opsi dropdown
$query_kelas = "SELECT id, nama_kelas FROM kelas";
$stmt_kelas = $conn->prepare($query_kelas);
$stmt_kelas->execute();
$kelas_list = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $kelas_id = $_POST['kelas'];

    // Update data user
    $query_update_user = "UPDATE users SET name = :name, email = :email WHERE id = :user_id";
    $stmt_update_user = $conn->prepare($query_update_user);
    $stmt_update_user->execute([
        'name' => $nama,
        'email' => $email,
        'user_id' => $user_id
    ]);

    // Update data siswa
    $query_update_siswa = "UPDATE siswa SET kelas_id = :kelas_id WHERE user_id = :user_id";
    $stmt_update_siswa = $conn->prepare($query_update_siswa);
    $stmt_update_siswa->execute([
        'kelas_id' => $kelas_id,
        'user_id' => $user_id
    ]);

    // Redirect dengan pesan sukses
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Profil berhasil diperbarui.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'profil_siswa.php';
            });
        });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    :root {
        --primary-color:  #4361ee;
        --secondary-color: #3f37c9;
        --background-color: #f8fafc;
        --text-color: #1e293b;
        --input-bg: rgba(2, 0, 0, 0.05);
        --input-border: rgba(66, 64, 64, 0.1);
        --input-focus:rgb(108, 109, 174);
        --card-bg: rgba(255, 255, 255, 0.95);
    }

    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background: var(--background-color);
        color: var(--text-color);
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    .main-content {
        position: relative;
        z-index: 2;
        padding: 2rem;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 80px);
    }

    .form-container {
        background: var(--card-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        padding: 2.5rem;
        border-radius: 1.5rem;
        width: 100%;
        max-width: 700px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
        overflow: hidden;
        margin-left: 240px;
    }

    .form-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
    }

    .form-container h3 {
        color: var(--text-color);
        font-size: 1.875rem;
        font-weight: 600;
        margin-bottom: 2rem;
        text-align: center;
    }

    .form-container form {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* Creates 3 columns */
        gap: 2rem;
        align-items: start;
    }

    .form-container label {
        color: var(--text-color);
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-container input,
    .form-container select {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid var(--input-border);
        background: var(--input-bg);
        color: var(--text-color);
        font-size: 1rem;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-container input:focus,
    .form-container select:focus {
        outline: none;
        border-color: var(--input-focus);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }

    .form-container .save-button {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1rem;
        border: none;
        border-radius: 0.75rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .form-container .save-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
    }

    .form-container .save-button:active {
        transform: translateY(0);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-container {
        animation: fadeIn 0.5s ease-out;
    }


    .button-container {
        grid-column: 1 / -1; /* Spans full width */
        display: flex;
        justify-content: flex-end;
        margin-top: 2rem;
    }

    .save-button {
        width: auto; /* Changed from 100% */
        min-width: 200px;
        padding: 1rem 2rem;
    }


    @media (max-width: 768px) {
        .main-content {
            padding: 1rem;
        }

        .form-container {
            padding: 1.5rem;
            margin: 1rem;
        }

        .form-container h3 {
            font-size: 1.5rem;
        }

        .form-container form {
            grid-template-columns: 1fr;
        }

        .button-container {
            justify-content: center;
        }
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-color);
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-content">
    <div class="form-container">
    <h3>Ubah Data</h3>
    <form method="POST">
        <div class="form-group">
            <label for="nama">Nama:</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($profil['nama_siswa']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profil['email_siswa']); ?>" required>
        </div>

        <div class="form-group">
            <label for="kelas">Kelas:</label>
            <select id="kelas" name="kelas" required>
                <?php foreach ($kelas_list as $kelas): ?>
                    <option value="<?php echo $kelas['id']; ?>" <?php echo ($kelas['id'] == $profil['kelas_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="button-container">
            <button type="submit" class="save-button">Simpan Perubahan</button>
        </div>
    </form>
</div>
    </div>
    <script>
    // Add ripple effect to button
    document.querySelector('.save-button').addEventListener('click', function(e) {
        let ripple = document.createElement('div');
        ripple.className = 'ripple';
        this.appendChild(ripple);
        
        let rect = this.getBoundingClientRect();
        let x = e.clientX - rect.left;
        let y = e.clientY - rect.top;
        
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        
        setTimeout(() => ripple.remove(), 1000);
    });

    // Smooth focus transitions
    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', () => {
            input.parentElement.classList.remove('focused');
        });
    });
</script>
</body>
</html>
