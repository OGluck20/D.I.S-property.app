<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $deviceId = $_GET['id'] ?? null;
    if (!$deviceId) {
        throw new Exception('Device ID is required');
    }

    $stmt = $conn->prepare("SELECT * FROM devices WHERE id = ?");
    $stmt->execute([$deviceId]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        throw new Exception('Device not found');
    }

    echo json_encode([
        'success' => true,
        'data' => $device
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}