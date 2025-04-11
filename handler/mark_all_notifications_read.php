<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("UPDATE notifications SET read_at = NOW() WHERE read_at IS NULL");
    $result = $stmt->execute();

    echo json_encode(['success' => $result]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}