<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Property ID is required');
    }

    $stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
    $result = $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Property deleted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}