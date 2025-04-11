<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $applicationId = $data['id'] ?? null;

    if (!$applicationId) {
        throw new Exception('Application ID is required');
    }

    $stmt = $conn->prepare("UPDATE service_applications SET status = 'completed' WHERE id = ?");
    $result = $stmt->execute([$applicationId]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update application status');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}