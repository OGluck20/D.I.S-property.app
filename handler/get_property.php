<?php
require_once '../includes/db.php';
session_start();
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Property ID is required');
    }

    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        throw new Exception('Property not found');
    }

    echo json_encode([
        'success' => true,
        'property' => $property
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}