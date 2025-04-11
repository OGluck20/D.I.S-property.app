<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->query("
        SELECT id, username, email, created_at 
        FROM users 
        WHERE role = 'user' 
        ORDER BY created_at DESC
    ");
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Users fetched: " . print_r($users, true));
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);

} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch users: ' . $e->getMessage()
    ]);
}