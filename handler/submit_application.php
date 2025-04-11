<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required data
    if (!isset($data['propertyId'], $data['serviceType'], $data['amount'], $data['reference'])) {
        throw new Exception('Missing required data');
    }

    $stmt = $conn->prepare("
        INSERT INTO service_applications 
        (user_id, property_id, service_type, amount, reference_code) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $result = $stmt->execute([
        $_SESSION['user_id'],
        $data['propertyId'],
        $data['serviceType'],
        $data['amount'],
        $data['reference']
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Application submitted successfully'
        ]);
    } else {
        throw new Exception('Failed to submit application');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}