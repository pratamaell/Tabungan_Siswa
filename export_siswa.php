<?php
require 'config/database.php';
require 'vendor/autoload.php'; // Pastikan PhpSpreadsheet terinstall

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$database = new Database();
$db = $database->getConnection();

$query = "SELECT siswa.id, users.name, kelas.nama_kelas, siswa.saldo FROM siswa
          JOIN users ON siswa.user_id = users.id 
          JOIN kelas ON siswa.kelas_id = kelas.id";
$stmt = $db->prepare($query);
$stmt->execute();

if (isset($_GET['format']) && $_GET['format'] == 'csv') {
    // **Export CSV**
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=siswa.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Nama', 'Kelas', 'Saldo']);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
} else {
    // **Export Excel**
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Nama');
    $sheet->setCellValue('C1', 'Kelas');
    $sheet->setCellValue('D1', 'Saldo');

    $rowIndex = 2;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sheet->setCellValue('A' . $rowIndex, $row['id']);
        $sheet->setCellValue('B' . $rowIndex, $row['name']);
        $sheet->setCellValue('C' . $rowIndex, $row['nama_kelas']);
        $sheet->setCellValue('D' . $rowIndex, $row['saldo']);
        $rowIndex++;
    }

    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="siswa.xlsx"');
    $writer->save('php://output');
    exit();
}
?>
