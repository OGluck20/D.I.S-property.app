<?php
require_once '../includes/db.php';
session_start();
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['user_id']) || !isset($data['status'])) {
        throw new Exception('Missing required fields');
    }

    $stmt = $conn->prepare("
        UPDATE users 
        SET status = ? 
        WHERE id = ? AND role = 'user'
    ");
    
    $result = $stmt->execute([$data['status'], $data['user_id']]);

    if (!$result) {
        throw new Exception('Failed to update user status');
    }

    echo json_encode([
        'success' => true,
        'message' => 'User status updated successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}