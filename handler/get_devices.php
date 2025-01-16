<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
    // Get all devices
    $stmt = $conn->query("SELECT id, name, brand, ram, storage, price, media FROM devices ORDER BY created_at DESC");
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format price and add image URLs
    foreach ($devices as &$device) {
        $device['price'] = number_format($device['price'], 2);
        $device['image_url'] = $device['media'] ? 'uploads/devices/' . $device['media'] : null;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $devices
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}