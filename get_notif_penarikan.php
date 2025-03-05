<?php
session_start();
include 'config/database.php';


try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT COUNT(*) as total FROM penarikan WHERE status = 'pending'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo $result['total'];
} catch(PDOException $e) {
    http_response_code(500);
    echo "0";
}