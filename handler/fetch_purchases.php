<?php
session_start();
require_once '../includes/db.php';

// Ensure proper JSON response headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    $stmt = $conn->prepare("
        SELECT p.*, DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
        FROM properties p
        WHERE p.purchaser_id = ? AND p.status = 'sold'
        ORDER BY p.updated_at DESC
    ");
    
    $stmt->execute([$_SESSION['user_id']]);
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure proper data formatting
    $formatted_purchases = array_map(function($purchase) {
        return [
            'id' => (int)$purchase['id'],
            'title' => htmlspecialchars($purchase['title']),
            'media' => $purchase['media'] ?? 'default-property.jpg',
            'price' => (float)$purchase['price'],
            'plot_size' => (float)$purchase['plot_size'],
            'purchase_code' => $purchase['purchase_code'],
            'location' => htmlspecialchars($purchase['location']),
            'updated_at' => $purchase['updated_at']
        ];
    }, $purchases);
    
    echo json_encode([
        'success' => true,
        'purchases' => $formatted_purchases
    ]);
    
} catch (Exception $e) {
    error_log("Purchases fetch error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load purchases: ' . $e->getMessage()
    ]);
}