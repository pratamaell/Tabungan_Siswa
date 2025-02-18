<?php
require 'config/database.php';
require 'vendor/autoload.php'; // Pastikan PhpSpreadsheet terinstall

use PhpOffice\PhpSpreadsheet\IOFactory;

$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $file = $_FILES["file"]["tmp_name"];
    $fileType = $_FILES["file"]["type"];
    
    if (!$file) {
        echo "<script>alert('Pilih file terlebih dahulu!'); window.location.href='manajemen_siswa.php';</script>";
        exit();
    }

    // Jika file CSV
    if ($fileType == "text/csv") {
        $handle = fopen($file, "r");
        fgetcsv($handle); // Lewati baris header

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $nama = $data[1];
            $kelas = $data[2];
            $saldo = $data[3];

            $query = "INSERT INTO siswa (user_id, kelas_id, saldo) VALUES (
                        (SELECT id FROM users WHERE name = :nama LIMIT 1),
                        (SELECT id FROM kelas WHERE nama_kelas = :kelas LIMIT 1),
                        :saldo)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':kelas', $kelas);
            $stmt->bindParam(':saldo', $saldo);
            $stmt->execute();
        }
        fclose($handle);
    } else {
        // Jika file Excel
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Lewati baris header

            $nama = $row[1];
            $kelas = $row[2];
            $saldo = $row[3];

            $query = "INSERT INTO siswa (user_id, kelas_id, saldo) VALUES (
                        (SELECT id FROM users WHERE name = :nama LIMIT 1),
                        (SELECT id FROM kelas WHERE nama_kelas = :kelas LIMIT 1),
                        :saldo)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':kelas', $kelas);
            $stmt->bindParam(':saldo', $saldo);
            $stmt->execute();
        }
    }

    echo "<script>alert('Data berhasil diimport!'); window.location.href='manajemen_siswa.php';</script>";
    exit();
}
?>
