<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->query("
        SELECT * FROM properties 
        ORDER BY created_at DESC
    ");
    
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'properties' => $properties
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch properties'
    ]);
}