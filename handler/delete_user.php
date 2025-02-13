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
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    echo json_encode(['success' => true]);
    
} catch(PDOException $e) {
    error_log("User delete error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 