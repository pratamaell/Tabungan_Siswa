<?php
session_start();
ob_start(); // Mencegah output sebelum header

include 'config/database.php';
include 'navbar_admin.php';

$database = new Database();
$db = $database->getConnection();

// Ambil data kelas dari database
$stmt = $db->prepare("SELECT * FROM kelas");
$stmt->execute();
$kelas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$limit = 6; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit; // Perhitungan offset

// Ambil data kelas dengan batasan jumlah per halaman
$stmt = $db->prepare("SELECT * FROM kelas LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $start, PDO::PARAM_INT);
$stmt->execute();
$kelas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_query = "SELECT COUNT(*) as total FROM kelas";
$total_stmt = $db->prepare($total_query);
$total_stmt->execute();
$total_data = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_data / $limit);

// Handle Add Class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    $nama_kelas = $_POST['nama_kelas'];
    $stmt = $db->prepare("INSERT INTO kelas (nama_kelas) VALUES (?)");
    if ($stmt->execute([$nama_kelas])) {
        $_SESSION['success_message'] = "Kelas berhasil ditambahkan!";
    } else {
        $_SESSION['error_message'] = "Gagal menambahkan kelas.";
    }
    header("Location: manajemen_kelas.php");
    exit();
}


// Handle Edit Class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_class'])) {
    $id = $_POST['id'];
    $nama_kelas = $_POST['nama_kelas'];
    $stmt = $db->prepare("UPDATE kelas SET nama_kelas = ? WHERE id = ?");

    if ($stmt->execute([$nama_kelas, $id])) {
        $_SESSION['success_message'] = "Kelas berhasil diperbarui!";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui kelas.";
    }

    header("Location: manajemen_kelas.php");
    exit();
}


// Handle Delete Class
// Handle Delete Class
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Hapus data terkait di tabel penarikan
    $stmt = $db->prepare("DELETE FROM penarikan WHERE siswa_id IN (SELECT id FROM siswa WHERE kelas_id = ?)");
    $stmt->execute([$id]);

    // Hapus data siswa yang terkait dengan kelas ini
    $stmt = $db->prepare("DELETE FROM siswa WHERE kelas_id = ?");
    $stmt->execute([$id]);

    // Baru hapus kelas setelah data terkait dihapus
    $stmt = $db->prepare("DELETE FROM kelas WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success_message'] = "Kelas berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus kelas.";
    }

    header("Location: manajemen_kelas.php");
    exit();
}



ob_end_flush(); // Tutup output buffering jika sudah tidak dibutuhkan
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/manajemen_kelas.css">
</head>

<body>
    <div class="home-section">
        <p style="text-align: center; color: #333; font-size: 40px; font-weight: bold;">Daftar Kelas</p>

        <button onclick="openAddModal()" class="btn-add-class">Tambah Kelas</button>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (count($kelas) > 0) {
                        foreach ($kelas as $row) { ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                <td>
                                    <a href="#" onclick="openEditModal('<?= $row['id'] ?>', '<?= $row['nama_kelas'] ?>')"
                                        class="icon">
                                        <i class="fas fa-edit fa-lg" style="color: #e67e22;"></i>
                                    </a>
                                    <a href="#" onclick="confirmDelete(<?= $row['id'] ?>)">
                                        <i class="fas fa-trash-alt fa-lg" style="color:red;"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="3" style="text-align: center; font-weight: bold; color: #777;">Data Kosong</td>
                        </tr>
                    <?php } ?>
                </tbody>

            </table>
        </div>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <a href="?page=<?= $i ?>" class="<?= ($page == $i) ? 'active' : '' ?>"> <?= $i ?> </a>
            <?php } ?>

            <!-- Tombol Next -->
            <?php if ($page < $total_pages) { ?>
                <a href="?page=<?= $page + 1 ?>" class="next-btn">Next</a>
            <?php } ?>

        </div>
    </div>

    <!-- Modal Tambah Kelas -->
    <div id="addClassModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2>Tambah Kelas</h2>
            <form method="POST" class="form-container">
                <label for="nama_kelas">Nama Kelas & jurusan:</label>
                <input type="text" id="nama_kelas" name="nama_kelas" required>
                <button type="submit" name="add_class" class="btn-submit">Tambah</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Kelas -->
    <div id="editClassModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Kelas</h2>
            <form method="POST" class="form-container">
                <input type="hidden" id="edit_id" name="id">
                <label for="edit_nama_kelas">Nama Kelas & Jurusan:</label>
                <input type="text" id="edit_nama_kelas" name="nama_kelas" required>
                <button type="submit" name="edit_class" class="btn-submit">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Script untuk Modal -->
    <script>
        // Fungsi untuk membuka modal Tambah Kelas
        function openAddModal() {
            let modal = document.getElementById("addClassModal");
            modal.style.display = "flex";
            setTimeout(() => {
                modal.classList.add("show");
            }, 10); // Delay sedikit agar animasi jalan
        }

        // Fungsi untuk menutup modal Tambah Kelas
        function closeAddModal() {
            let modal = document.getElementById("addClassModal");
            modal.classList.remove("show");
            setTimeout(() => {
                modal.style.display = "none";
            }, 400); // Tunggu animasi selesai sebelum disembunyikan
        }

        // Fungsi untuk membuka modal Edit Kelas
        function openEditModal(id, nama_kelas) {
            document.getElementById("edit_id").value = id;
            document.getElementById("edit_nama_kelas").value = nama_kelas;
            let modal = document.getElementById("editClassModal");
            modal.style.display = "flex";
            setTimeout(() => {
                modal.classList.add("show");
            }, 10);
        }

        // Fungsi untuk menutup modal Edit Kelas
        function closeEditModal() {
            let modal = document.getElementById("editClassModal");
            modal.classList.remove("show");
            setTimeout(() => {
                modal.style.display = "none";
            }, 400);
        }

        // Menutup modal jika klik di luar modal
        window.onclick = function (event) {
            let addModal = document.getElementById("addClassModal");
            let editModal = document.getElementById("editClassModal");

            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        };
    </script>

    <script>
        // Cek apakah ada pesan sukses atau error di sesi
        <?php if (isset($_SESSION['success_message'])) { ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= $_SESSION['success_message']; ?>',
                showConfirmButton: false,
                timer: 2000
            });
            <?php unset($_SESSION['success_message']); // Hapus setelah ditampilkan ?>
        <?php } ?>

        <?php if (isset($_SESSION['error_message'])) { ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= $_SESSION['error_message']; ?>',
                showConfirmButton: false,
                timer: 2000
            });
            <?php unset($_SESSION['error_message']); // Hapus setelah ditampilkan ?>
        <?php } ?>
    </script>

    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "?delete_id=" + id;
                }
            });
        }
    </script>

    <script>
        // Cek apakah ada pesan sukses atau error di sesi
        <?php if (isset($_SESSION['success_message'])) { ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= $_SESSION['success_message']; ?>',
                showConfirmButton: false,
                timer: 2000
            });
            <?php unset($_SESSION['success_message']); // Hapus setelah ditampilkan ?>
        <?php } ?>

        <?php if (isset($_SESSION['error_message'])) { ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= $_SESSION['error_message']; ?>',
                showConfirmButton: false,
                timer: 2000
            });
            <?php unset($_SESSION['error_message']); // Hapus setelah ditampilkan ?>
        <?php } ?>
    </script>

</body>

</html>