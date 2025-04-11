<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $notifId = $data['id'] ?? null;

    if (!$notifId) {
        throw new Exception('Notification ID is required');
    }

    $stmt = $conn->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$notifId]);

    echo json_encode(['success' => $result]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}