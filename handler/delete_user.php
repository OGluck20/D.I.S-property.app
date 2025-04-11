<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $userId = $_POST['id'] ?? null;
    
    if (!$userId) {
        throw new Exception('User ID is required');
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $result = $stmt->execute([$userId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('User not found or cannot be deleted');
    }

    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}