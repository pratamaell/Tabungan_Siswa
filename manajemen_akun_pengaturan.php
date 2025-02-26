<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit();
}

include 'config/database.php';
include 'navbar_admin.php';

$database = new Database();
$db = $database->getConnection();

// Tambah atau Edit akun
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $kelas_id = $_POST['kelas_id'] ?? null;

    if ($id) { // Update akun
        $query = "UPDATE users SET name=:name, email=:email, role=:role WHERE id=:id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
    } else { // Tambah akun baru
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password', $password);
    }

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role', $role);

    if ($stmt->execute()) {
        if (!$id && $role == 'siswa') {
            $user_id = $db->lastInsertId();
            $query_siswa = "INSERT INTO siswa (user_id, kelas_id, saldo) VALUES (:user_id, :kelas_id, 0)";
            $stmt_siswa = $db->prepare($query_siswa);
            $stmt_siswa->bindParam(':user_id', $user_id);
            $stmt_siswa->bindParam(':kelas_id', $kelas_id);
            $stmt_siswa->execute();
        }
        echo "<script>alert('Akun berhasil disimpan!'); window.location='';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan akun!');</script>";
    }
}

// Hapus akun
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM users WHERE id=:id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        echo "<script>alert('Akun berhasil dihapus!'); window.location='manajemen_akun_pengaturan.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus akun!');</script>";
    }
}

// Ambil semua data akun
$query = "SELECT * FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar kelas dari database
$query_kelas = "SELECT id, nama_kelas FROM kelas";
$stmt_kelas = $db->prepare($query_kelas);
$stmt_kelas->execute();
$kelasList = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Add this near the top of the file, after database connection
$limit = 10; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Modify the query to get total users
$query_count = "SELECT COUNT(*) as total FROM users";
$stmt_count = $db->prepare($query_count);
$stmt_count->execute();
$total_users = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_users / $limit);

// Modify the query to get users with pagination
$query = "SELECT * FROM users LIMIT :start, :limit";
$stmt = $db->prepare($query);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add this query after your database connection
$query_counts = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$stmt_counts = $db->prepare($query_counts);
$stmt_counts->execute();
$role_counts = array_column($stmt_counts->fetchAll(PDO::FETCH_ASSOC), 'count', 'role');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        }
        .form-input {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .form-input:focus {
            border-left: 4px solid #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
        }
        .btn-primary {
            background-image: linear-gradient(135deg, #4f46e5, #6366f1);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .btn-edit {
            background-image: linear-gradient(135deg, #f59e0b, #fbbf24);
            transition: all 0.3s ease;
        }
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .btn-delete {
            background-image: linear-gradient(135deg, #ef4444, #f87171);
            transition: all 0.3s ease;
        }
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .table-row-animate {
            transition: all 0.2s ease;
        }
        .page-bg {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 10% 20%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
                radial-gradient(at 80% 90%, rgba(79, 70, 229, 0.05) 0px, transparent 50%);
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .clock {
            font-size: 2.5rem;
            font-weight: 300;
            color: #4f46e5;
            text-shadow: 0 0 20px rgba(79, 70, 229, 0.3);
        }
        .clock-container {
            position: relative;
            padding: 1.5rem;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.1);
            overflow: hidden;
        }
        .clock-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, #4f46e5, #6366f1);
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .pagination-btn.active {
            background: var(--gradient);
            color: white;
        }

        .pagination-btn:hover:not(.active) {
            background: rgba(79, 70, 229, 0.1);
            transform: translateY(-2px);
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .stat-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: currentColor;
            opacity: 0.2;
        }

        .home-section {
        position: relative;
        min-height: 100vh;
        width: calc(100% - 78px);
        left: 78px;
        transition: all 0.3s ease;
        }
    </style>
</head>
<body class="page-bg min-h-screen">
    <div class="home-section">
    <div class="container mx-auto px-4 py-12">
        <div x-data="{ isEditing: false, togglePasswordView: false }" class="glass-effect rounded-2xl p-8 max-w-5xl mx-auto animate-fade-in">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">
                <i class="fas fa-users-cog mr-2 text-indigo-600"></i>
                Manajemen Akun
            </h2>
            
            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <div class="col-span-1 space-y-5">
                    <h3 class="text-xl font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-user-plus mr-2 text-indigo-500"></i>
                        <span x-text="isEditing ? 'Edit Akun' : 'Tambah Akun Baru'"></span>
                    </h3>
                    
                    <form method="post" @submit="isEditing = false" class="space-y-5">
                        <input type="hidden" name="id" id="id" x-ref="id">
                        
                        <div class="relative">
                            <span class="absolute inset-y-0 left-4 flex items-center">
                                <i class="fas fa-user text-gray-400"></i>
                            </span>
                            <input type="text" name="name" id="name" x-ref="name" placeholder="Nama Lengkap" required
                                   class="form-input w-full pl-12 pr-4 py-3 rounded-xl focus:outline-none text-gray-700 bg-white">
                        </div>
                        
                        <div class="relative">
                            <span class="absolute inset-y-0 left-4 flex items-center">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </span>
                            <input type="email" name="email" id="email" x-ref="email" placeholder="Email" required
                                   class="form-input w-full pl-12 pr-4 py-3 rounded-xl focus:outline-none text-gray-700 bg-white">
                        </div>
                        
                        <div class="relative">
                            <span class="absolute inset-y-0 left-4 flex items-center">
                                <i class="fas fa-lock text-gray-400"></i>
                            </span>
                            <input x-bind:type="togglePasswordView ? 'text' : 'password'" 
                                   name="password" id="password" x-ref="password" 
                                   x-bind:placeholder="isEditing ? 'Kosongkan jika tidak ingin mengubah' : 'Password'" 
                                   x-bind:required="!isEditing"
                                   class="form-input w-full pl-12 pr-12 py-3 rounded-xl focus:outline-none text-gray-700 bg-white">
                            <button type="button" @click="togglePasswordView = !togglePasswordView" 
                                    class="absolute inset-y-0 right-4 flex items-center">
                                <i x-bind:class="togglePasswordView ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-gray-400"></i>
                            </button>
                        </div>
                        
                        <div class="relative">
                            <span class="absolute inset-y-0 left-4 flex items-center">
                                <i class="fas fa-user-tag text-gray-400"></i>
                            </span>
                            <select name="role" id="role" x-ref="role" required 
                                    class="form-input w-full pl-12 pr-4 py-3 rounded-xl focus:outline-none text-gray-700 bg-white appearance-none" 
                                    onchange="toggleKelasField()">
                                <option value="">Pilih Role</option>
                                <option value="admin">Admin</option>
                                <option value="bendahara">Bendahara</option>
                                <option value="siswa">Siswa</option>
                            </select>
                            <span class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </span>
                        </div>
                        
                        <div id="kelasFieldContainer" class="relative hidden">
                            <span class="absolute inset-y-0 left-4 flex items-center">
                                <i class="fas fa-school text-gray-400"></i>
                            </span>
                            <select name="kelas_id" id="kelasField" 
                                    class="form-input w-full pl-12 pr-4 py-3 rounded-xl focus:outline-none text-gray-700 bg-white appearance-none">
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($kelasList as $kelas) : ?>
                                    <option value="<?= htmlspecialchars($kelas['id']) ?>">
                                        <?= htmlspecialchars($kelas['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </span>
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="submit" class="btn-primary flex-1 py-3 px-4 rounded-xl text-white font-medium">
                                <i class="fas fa-save mr-2"></i> Simpan Akun
                            </button>
                            <button type="button" @click="isEditing = false; 
                                   $refs.id.value = ''; 
                                   $refs.name.value = ''; 
                                   $refs.email.value = ''; 
                                   $refs.password.value = '';
                                   $refs.role.value = '';
                                   document.getElementById('kelasField').value = '';
                                   toggleKelasField();" 
                                   x-show="isEditing"
                                   class="bg-gray-500 hover:bg-gray-600 py-3 px-4 rounded-xl text-white font-medium transition">
                                <i class="fas fa-times mr-2"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="col-span-1 flex items-center justify-center">
                    <div class="text-center">
                        <div class="clock-container mb-5">
                            <div id="digitalClock" class="clock"></div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mt-6">
                            <div class="stat-card bg-purple-50 p-4 rounded-xl border border-purple-100">
                                <div class="text-purple-600 text-2xl font-bold">
                                    <?= $role_counts['admin'] ?? 0 ?>
                                </div>
                                <div class="text-purple-500 text-sm font-medium">
                                    <i class="fas fa-user-shield mr-1"></i> Admin
                                </div>
                            </div>
                            <div class="stat-card bg-blue-50 p-4 rounded-xl border border-blue-100">
                                <div class="text-blue-600 text-2xl font-bold">
                                    <?= $role_counts['bendahara'] ?? 0 ?>
                                </div>
                                <div class="text-blue-500 text-sm font-medium">
                                    <i class="fas fa-user-tie mr-1"></i> Bendahara
                                </div>
                            </div>
                            <div class="stat-card bg-green-50 p-4 rounded-xl border border-green-100">
                                <div class="text-green-600 text-2xl font-bold">
                                    <?= $role_counts['siswa'] ?? 0 ?>
                                </div>
                                <div class="text-green-500 text-sm font-medium">
                                    <i class="fas fa-user-graduate mr-1"></i> Siswa
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-500 mt-4 italic">Panel manajemen untuk mengelola akun pengguna sistem</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-10">
                <h3 class="text-xl font-semibold text-gray-700 mb-5 flex items-center">
                    <i class="fas fa-list mr-2 text-indigo-500"></i>
                    Daftar Akun
                </h3>
                <div class="overflow-x-auto rounded-xl shadow-lg">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-left">
                                <th class="p-4 rounded-tl-xl">No</th>
                                <th class="p-4"><i class="fas fa-user mr-2"></i>Nama</th>
                                <th class="p-4"><i class="fas fa-envelope mr-2"></i>Email</th>
                                <th class="p-4"><i class="fas fa-user-tag mr-2"></i>Role</th>
                                <th class="p-4 rounded-tr-xl text-center"><i class="fas fa-cogs mr-2"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $index => $user): ?>
                                <tr class="table-row-animate border-b border-gray-200 hover:bg-indigo-50 bg-white">
                                    <td class="p-4 font-medium text-gray-700"><?= $index + 1; ?></td>
                                    <td class="p-4 font-medium text-gray-700"><?= htmlspecialchars($user['name']); ?></td>
                                    <td class="p-4 text-gray-500"><?= htmlspecialchars($user['email']); ?></td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                            <?php
                                            $roleColor = '';
                                            switch ($user['role']) {
                                                case 'admin':
                                                    $roleColor = 'bg-purple-100 text-purple-700';
                                                    break;
                                                case 'bendahara':
                                                    $roleColor = 'bg-blue-100 text-blue-700';
                                                    break;
                                                case 'siswa':
                                                    $roleColor = 'bg-green-100 text-green-700';
                                                    break;
                                                default:
                                                    $roleColor = 'bg-gray-100 text-gray-700';
                                            }
                                            echo $roleColor;
                                            ?>">
                                            <?= htmlspecialchars($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button class="btn-edit text-white px-4 py-2 rounded-lg text-sm mr-2" 
                                                @click="isEditing = true; 
                                                        $refs.id.value = '<?= $user['id'] ?>'; 
                                                        $refs.name.value = '<?= htmlspecialchars($user['name']) ?>'; 
                                                        $refs.email.value = '<?= htmlspecialchars($user['email']) ?>'; 
                                                        $refs.password.value = '';
                                                        $refs.password.required = false;
                                                        $refs.role.value = '<?= htmlspecialchars($user['role']) ?>';
                                                        toggleKelasField();">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </button>
                                        <a href="?delete=<?= $user['id']; ?>" 
                                           class="btn-delete text-white px-4 py-2 rounded-lg text-sm" 
                                           onclick="return confirm('Yakin ingin menghapus akun <?= htmlspecialchars($user['name']) ?>?');">
                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($users) == 0): ?>
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-gray-500 bg-white">
                                        <i class="fas fa-info-circle mr-2"></i> Belum ada data akun tersedia
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- filepath: /c:/laragon/www/tabungan/manajemen_akun_pengaturan.php -->

<?php if ($total_pages > 1): ?>
    <div class="pagination mt-6">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>" class="pagination-btn bg-white text-indigo-600 border border-indigo-200">
                <i class="fas fa-chevron-left mr-2"></i> Previous
            </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" 
               class="pagination-btn <?= $i === $page ? 'active' : 'bg-white text-indigo-600 border border-indigo-200' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page+1 ?>" class="pagination-btn bg-white text-indigo-600 border border-indigo-200">
                Next <i class="fas fa-chevron-right ml-2"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
    </div>
    </div>

    <script>
        function toggleKelasField() {
            const roleSelect = document.getElementById("role");
            const kelasFieldContainer = document.getElementById("kelasFieldContainer");
            
            if (roleSelect.value === "siswa") {
                kelasFieldContainer.classList.remove("hidden");
            } else {
                kelasFieldContainer.classList.add("hidden");
            }
        }
        
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            document.getElementById('digitalClock').textContent = `${hours}:${minutes}:${seconds}`;
            setTimeout(updateClock, 1000);
        }
        
        // Start the clock when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateClock();
        });
    </script>
</body>
</html>