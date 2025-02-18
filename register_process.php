<?php
session_start();
require_once 'config/database.php'; // Koneksi ke database

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ambil input dari form
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);
    $kelas_id = isset($_POST['kelas_id']) ? trim($_POST['kelas_id']) : null; // Hanya untuk siswa

    // Validasi input
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['error'] = "Semua field harus diisi.";
        header("Location: login_register.php");
        exit();
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid.";
        header("Location: login_register.php");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Koneksi database
        $db = (new Database())->getConnection();

        // Cek apakah email sudah terdaftar
        $checkEmailQuery = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($checkEmailQuery);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email sudah terdaftar.";
            header("Location: login_register.php");
            exit();
        }

        // Simpan data ke tabel users
        $insertUserQuery = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $db->prepare($insertUserQuery);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            $user_id = $db->lastInsertId(); // Ambil ID user yang baru saja dibuat

            // Jika role adalah siswa, tambahkan ke tabel siswa
            if ($role === "siswa" && !empty($kelas_id)) {
                $insertSiswaQuery = "INSERT INTO siswa (user_id, kelas_id, saldo) VALUES (:user_id, :kelas_id, 0)";
                $stmt_siswa = $db->prepare($insertSiswaQuery);
                $stmt_siswa->bindParam(':user_id', $user_id);
                $stmt_siswa->bindParam(':kelas_id', $kelas_id);
                $stmt_siswa->execute();
            }

            $_SESSION['success'] = "Registrasi berhasil. Silakan login.";
            header("Location: login_register.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal menyimpan data.";
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
