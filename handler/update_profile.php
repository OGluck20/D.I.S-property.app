<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Get and decode JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['firstname', 'lastname', 'phone', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate phone number
    if (!preg_match('/^\d{11}$/', $data['phone'])) {
        throw new Exception('Invalid phone number format');
    }

    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Check if email already exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$data['email'], $_SESSION['user_id']]);
    if ($stmt->fetchColumn()) {
        throw new Exception('Email already in use by another account');
    }

    // Update user profile without updated_at field
    $stmt = $conn->prepare("
        UPDATE users 
        SET firstname = ?, 
            lastname = ?, 
            phone = ?, 
            email = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $data['firstname'],
        $data['lastname'],
        $data['phone'],
        $data['email'],
        $_SESSION['user_id']
    ]);

    // Update session data
    $_SESSION['firstname'] = $data['firstname'];
    $_SESSION['lastname'] = $data['lastname'];
    $_SESSION['email'] = $data['email'];

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}