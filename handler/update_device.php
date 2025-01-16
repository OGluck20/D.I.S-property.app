<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $price = $_POST['price'];

    $sql = "UPDATE devices SET name = :name, brand = :brand, price = :price WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'id' => $id,
        'name' => $name,
        'brand' => $brand,
        'price' => $price
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Device updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}