<?php
session_start();
require_once '../includes/db.php';

// Verify admin
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $conn->prepare("
        UPDATE users 
        SET username = ?, email = ?, role = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $data['username'],
        $data['email'],
        $data['role'],
        $data['id']
    ]);

    echo json_encode(['success' => true]);
    
} catch(PDOException $e) {
    error_log("User update error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 