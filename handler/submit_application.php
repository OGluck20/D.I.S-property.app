<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Enable error logging
ini_set('display_errors', 1);
error_log("Application submission started");

// Log the raw request
$raw_input = file_get_contents('php://input');
error_log("Raw request data: " . $raw_input);

try {
    if (!isset($_SESSION['user_id'])) {
        error_log("User not authenticated. Session user_id missing.");
        throw new Exception('Unauthorized access');
    }

    // Decode and validate JSON data
    $data = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }

    // Validate required fields
    $required_fields = ['property_id', 'service_type', 'amount', 'reference', 'recipient_name', 'recipient_phone'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Log all received data
    error_log("Decoded data: " . print_r($data, true));

    // Start transaction
    $conn->beginTransaction();
    error_log("Transaction started");

    try {
        // Insert application
        $stmt = $conn->prepare("
            INSERT INTO service_applications (
                user_id, 
                property_id, 
                service_type, 
                amount, 
                reference,
                recipient_name,
                recipient_phone,
                status, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $params = [
            $_SESSION['user_id'],
            $data['property_id'],
            $data['service_type'],
            $data['amount'],
            $data['reference'],
            $data['recipient_name'],
            $data['recipient_phone']
        ];

        error_log("Executing query with params: " . print_r($params, true));
        
        $success = $stmt->execute($params);

        if (!$success) {
            throw new Exception('Database error: ' . implode(', ', $stmt->errorInfo()));
        }

        // Create notification
        $applicationId = $conn->lastInsertId();
        
        $notifyStmt = $conn->prepare("
            INSERT INTO notifications (
                user_id,
                type,
                message,
                reference_no,  -- Changed from reference_id
                created_at
            ) VALUES (?, 'application', ?, ?, NOW())
        ");

        error_log("Creating notification for admin");
        $message = "New {$data['service_type']} application submitted";
        $success = $notifyStmt->execute([
            1, // admin user_id
            $message,
            $data['reference'] // Using payment reference from Paystack
        ]);

        if (!$success) {
            error_log("Notification creation failed: " . implode(', ', $notifyStmt->errorInfo()));
            throw new Exception('Failed to create notification');
        }

        // Commit transaction
        $conn->commit();
        error_log("Transaction committed successfully");

        error_log("Application submitted successfully");

        echo json_encode([
            'success' => true,
            'message' => 'Application submitted successfully',
            'application_id' => $applicationId
        ]);

    } catch (Exception $e) {
        error_log("Inner transaction error: " . $e->getMessage());
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Application submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'post_data' => $data ?? null,
            'session' => $_SESSION ?? null
        ]
    ]);
    exit;
}