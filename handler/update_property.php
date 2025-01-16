<?php
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $sql = "UPDATE properties SET title = :title, location = :location, price = :price, status = :status WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'id' => $id,
        'title' => $title,
        'location' => $location,
        'price' => $price,
        'status' => $status
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Property updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}