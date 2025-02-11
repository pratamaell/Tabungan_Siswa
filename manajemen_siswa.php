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

$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$filter_nama = isset($_GET['nama']) ? $_GET['nama'] : '';
$filter_saldo = isset($_GET['saldo']) ? $_GET['saldo'] : '';
$limit = 6; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$query = "SELECT siswa.id, users.name, kelas.nama_kelas, siswa.saldo FROM siswa 
          JOIN users ON siswa.user_id = users.id 
          JOIN kelas ON siswa.kelas_id = kelas.id WHERE 1=1";

if ($filter_kelas) {
    $query .= " AND siswa.kelas_id = :kelas_id";
}
if ($filter_nama) {
    $query .= " AND users.name LIKE :nama";
}
if ($filter_saldo && is_numeric($filter_saldo)) {
    $query .= " AND siswa.saldo = :saldo";
}

$query .= " LIMIT :start, :limit";

$stmt = $db->prepare($query);
if ($filter_kelas) {
    $stmt->bindParam(':kelas_id', $filter_kelas);
}
if ($filter_nama) {
    $nama_param = "%$filter_nama%";
    $stmt->bindParam(':nama', $nama_param);
}
if ($filter_saldo && is_numeric($filter_saldo)) {
    $stmt->bindParam(':saldo', $filter_saldo);
}
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();

$total_query = "SELECT COUNT(*) as total FROM siswa";
$total_stmt = $db->prepare($total_query);
$total_stmt->execute();
$total_data = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_data / $limit);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $saldo = $_POST['saldo'];

    if (empty($id) || empty($nama) || empty($kelas) || !is_numeric($saldo)) {
        echo "<script>alert('Harap isi semua bidang dengan benar!'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }

    $query = "UPDATE siswa 
              JOIN users ON siswa.user_id = users.id 
              JOIN kelas ON siswa.kelas_id = kelas.id 
              SET users.name = :nama, kelas.nama_kelas = :kelas, siswa.saldo = :saldo 
              WHERE siswa.id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nama', $nama);
    $stmt->bindParam(':kelas', $kelas);
    $stmt->bindParam(':saldo', $saldo);

    if ($stmt->execute()) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location.href='manajemen_siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.'); window.location.href='manajemen_siswa.php';</script>";
    }
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $query = "DELETE FROM siswa WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo "<script>alert('Siswa berhasil dihapus!'); window.location.href='manajemen_siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus siswa.'); window.location.href='manajemen_siswa.php';</script>";
    }
} elseif (isset($_GET['id'])) {
    echo "<script>alert('ID tidak valid.'); window.location.href='manajemen_siswa.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/manajemen_siswa.css">
</head>

<body>
    <div class="home-section">
        <!-- Filter Pencarian -->
        <div class="search-container">
            <form method="GET" class="search-box">
                <input type="text" name="nama" placeholder="Cari Nama"
                    value="<?php echo htmlspecialchars($filter_nama); ?>">
                <select name="kelas">
                    <option value="">Semua Kelas</option>
                    <?php
                    $kelas_query = "SELECT id, nama_kelas FROM kelas";
                    $kelas_stmt = $db->prepare($kelas_query);
                    $kelas_stmt->execute();
                    while ($kelas_row = $kelas_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($filter_kelas == $kelas_row['id']) ? 'selected' : '';
                        echo "<option value='{$kelas_row['id']}' $selected>{$kelas_row['nama_kelas']}</option>";
                    }
                    ?>
                </select>
                <input type="number" name="saldo" placeholder="Cari Saldo"
                    value="<?php echo htmlspecialchars($filter_saldo); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <!-- Export & Import Buttons -->
        <div class="button-container">
            <div class="dropdown">
                <button class="btn btn-success dropdown-toggle" onclick="toggleDropdown()">Export Data</button>
                <div class="dropdown-menu" id="exportDropdown">
                    <a href="export_siswa.php?format=xlsx" class="dropdown-item">Excel</a>
                    <a href="export_siswa.php?format=csv" class="dropdown-item">CSV</a>
                </div>
            </div>

            <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">Import
                Data</button>
            <form action="import_siswa.php" method="POST" enctype="multipart/form-data" id="importForm"
                style="display: none;">
                <input type="file" name="file" id="fileInput" accept=".csv, .xls, .xlsx"
                    onchange="document.getElementById('importForm').submit()">
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Saldo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1; // Inisialisasi nomor urut
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['nama_kelas'] ?></td>
                                <td>Rp <?= number_format($row['saldo'], 0, ',', '.') ?></td>
                                <td>
                                    <a href="#"
                                        onclick="openEditModal('<?= $row['id'] ?>', '<?= $row['name'] ?>', '<?= $row['nama_kelas'] ?>', <?= $row['saldo'] ?>)"
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
                            <td colspan="5" style="text-align: center; font-weight: bold;">Data Kosong</td>
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


        <?php
        // Ambil daftar kelas untuk dropdown
        $kelas_query = "SELECT id, nama_kelas FROM kelas";
        $kelas_stmt = $db->prepare($kelas_query);
        $kelas_stmt->execute();
        $kelas_options = $kelas_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <!-- Modal Edit -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <span>Edit Siswa</span>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form action="update_siswa.php" method="POST">
                        <input type="hidden" id="editId" name="id">

                        <label for="editNama">Nama:</label>
                        <input type="text" id="editNama" name="nama" class="form-input" required>

                        <label for="editKelas">Kelas:</label>
                        <div class="dropdown-container">
                            <select id="editKelas" name="kelas" class="form-input" required>
                                <?php foreach ($kelas_options as $kelas) { ?>
                                    <option value="<?= $kelas['id'] ?>" id="kelas-<?= $kelas['id'] ?>">
                                        <?= $kelas['nama_kelas'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <label for="editSaldo">Saldo:</label>
                        <input type="number" id="editSaldo" name="saldo" class="form-input" required>

                        <div class="modal-footer">
                            <button type="button" class="close-btn" onclick="closeEditModal()">Batal</button>
                            <button type="submit" class="save-btn">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            .form-input {
                width: 100%;
                padding: 10px;
                margin: 5px 0;
                border: 1px solid #ccc;
                border-radius: 5px;
                font-size: 16px;
            }

            select.form-input {
                appearance: none;
                background-color: white;
                cursor: pointer;
            }

            .dropdown-container {
                position: relative;
            }

            select.form-input:focus {
                outline: none;
            }
        </style>

        <script>
            function openEditModal(id, nama, kelasId, saldo) {
                document.getElementById('editId').value = id;
                document.getElementById('editNama').value = nama;
                document.getElementById('editKelas').value = kelasId;
                document.getElementById('editSaldo').value = saldo;

                let modal = document.getElementById('editModal');
                let modalContent = document.querySelector('.modal-content');

                modal.style.display = 'flex';
                setTimeout(() => {
                    modalContent.style.transform = 'scale(1)';
                    modalContent.style.opacity = '1';
                }, 50);
            }
        </script>

        <script>
            function openEditModal(id, nama, kelas, saldo) {
                document.getElementById('editId').value = id;
                document.getElementById('editNama').value = nama;
                document.getElementById('editKelas').value = kelas;
                document.getElementById('editSaldo').value = saldo;

                let modal = document.getElementById('editModal');
                let modalContent = document.querySelector('.modal-content');

                modal.style.display = 'flex';
                setTimeout(() => {
                    modalContent.style.transform = 'scale(1)';
                    modalContent.style.opacity = '1';
                }, 50);
            }

            function closeEditModal() {
                let modal = document.getElementById('editModal');
                let modalContent = document.querySelector('.modal-content');

                modalContent.style.transform = 'scale(0.8)';
                modalContent.style.opacity = '0';
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }

            function showAlert(type, title, text) {
                Swal.fire({
                    icon: type,
                    title: title,
                    text: text,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            }

            function confirmDelete(id) {
                Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Data ini akan dihapus secara permanen!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Ya, hapus!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "manajemen_siswa.php?id=" + id;
                    }
                });
            }

            function toggleDropdown() {
                let dropdown = document.getElementById('exportDropdown');
                dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
            }

            // Tutup dropdown jika klik di luar
            window.onclick = function (event) {
                if (!event.target.matches('.dropdown-toggle')) {
                    document.getElementById('exportDropdown').style.display = "none";
                }
            };
        </script>
</body>

</html>