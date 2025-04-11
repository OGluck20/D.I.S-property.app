<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $userId = $_GET['id'] ?? null;
    
    if (!$userId) {
        throw new Exception('User ID is required');
    }

    // Get user details and count their properties/applications
    $stmt = $conn->prepare("
        SELECT id, firstname, lastname, email, phone, gender, created_at,
               (SELECT COUNT(*) FROM properties WHERE user_id = users.id) as properties_added,
               (SELECT COUNT(*) FROM service_applications WHERE user_id = users.id AND status = 'completed') as properties_purchased
        FROM users 
        WHERE id = :id
    ");
    
    $stmt->execute(['id' => $userId]);  // Changed from $stmt->execute([$userId])
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Debug log
    error_log('User data: ' . print_r($user, true));

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $user['id'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'gender' => $user['gender'],        // Added gender field
            'created_at' => $user['created_at'],
            'properties_added' => (int)$user['properties_added'],
            'properties_purchased' => (int)$user['properties_purchased']
        ]
    ]);

} catch (Exception $e) {
    error_log('Error in get_user_details.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}