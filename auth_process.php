<?php
session_start();
include 'config/database.php';

$email = $_POST['email'];
$password = $_POST['password'];

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM users WHERE email = :email";
$stmt = $db->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] == 'admin') {
        header("Location: dashboard_admin.php");
    } elseif ($user['role'] == 'bendahara') {
        header("Location: dashboard_bendahara.php");
    } else {
        header("Location: dashboard_siswa.php");
    }
} else {
    echo "Login gagal! Email atau password salah.";
}
?>
