<?php
session_start(); // Memulai session untuk menampilkan pesan error/sukses
require_once 'config/database.php'; // Menghubungkan ke database

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ambil input dari form
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validasi input
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['error'] = "Semua field harus diisi.";
        header("Location: login_register.php");
        exit();
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid.";
        header("Location: login_register.php");
        exit();
    }

    // Hash password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Koneksi ke database
        $db = (new Database())->getConnection();

        // Periksa apakah email sudah terdaftar
        $checkEmailQuery = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($checkEmailQuery);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email sudah terdaftar.";
            header("Location: login_register.php");
            exit();
        }

        // Simpan data ke database
        $insertQuery = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $db->prepare($insertQuery);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Registrasi berhasil. Silakan login.";
            header("Location: login_register.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal menyimpan data. Silakan coba lagi.";
            header("Location: login_register.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: login_register.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Akses tidak valid.";
    header("Location: login_register.php");
    exit();
}
?>
