<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Detailed debug logging
error_log('=============== NEW NOTIFICATION REQUEST ===============');
error_log('SESSION: ' . print_r($_SESSION, true));
error_log('POST RAW: ' . file_get_contents('php://input'));

try {
    $data = json_decode(file_get_contents('php://input'), true);
    error_log('DECODED JSON: ' . print_r($data, true));
    
    // Check all possible sources of user_id
    error_log('Checking user_id sources:');
    error_log('Session user_id: ' . ($_SESSION['user_id'] ?? 'not set'));
    error_log('Data payload user_id: ' . ($data['user_id'] ?? 'not set'));
    
    // Get user ID with explicit checking
    if (isset($data['user_id'])) {
        $user_id = $data['user_id'];
        error_log('Using user_id from data payload: ' . $user_id);
    } else if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        error_log('Using user_id from session: ' . $user_id);
    } else {
        error_log('No user_id found in any source');
        throw new Exception('No user ID available');
    }

    // Validate user_id is not 1
    if ($user_id == 1) {
        error_log('WARNING: user_id is 1. Tracing call stack:');
        error_log(print_r(debug_backtrace(), true));
    }
    
    // Ensure we have the required data
    if (!isset($data['serviceType']) || !isset($data['reference'])) {
        throw new Exception('Missing required data');
    }
    
    // Get user details for the notification
    $stmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found for ID: ' . $user_id);
    }
    
    // Create notification with the correct structure
    $stmt = $conn->prepare("
        INSERT INTO notifications (
            user_id,
            type,
            message,
            reference_no,
            created_at
        ) VALUES (
            :user_id,
            :type,
            :message,
            :reference_no,
            NOW()
        )
    ");

    $message = "{$user['firstname']} {$user['lastname']} submitted a new {$data['serviceType']} service application";
    
    $params = [
        ':user_id' => $user_id,
        ':type' => 'service_application',
        ':message' => $message,
        ':reference_no' => $data['reference']
    ];
    
    // Debug parameters
    error_log('SQL parameters: ' . print_r($params, true));
    
    $result = $stmt->execute($params);
    
    if (!$result) {
        error_log('Database error: ' . print_r($stmt->errorInfo(), true));
        throw new Exception('Failed to create notification');
    }

    error_log('Notification created successfully for user_id: ' . $user_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification created successfully'
    ]);

} catch (Exception $e) {
    error_log('Error creating notification: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}