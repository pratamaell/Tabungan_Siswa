<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$user_id = $_SESSION['user_id'];

$query = "SELECT password FROM users WHERE id = :user_id";
$stmt = $conn->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// For security, you might want to implement additional checks here

header('Content-Type: application/json');
echo json_encode(['password' => $result['password']]);