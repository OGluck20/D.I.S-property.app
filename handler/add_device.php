<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $ram = $_POST['ram'];
    $storage = $_POST['storage'];
    $price = $_POST['price'];
    
    // Handle image upload
    $target_dir = "../uploads/devices/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Handle file upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $file_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            throw new Exception('Failed to upload file');
        }
    } else {
        $file_name = ''; // No image uploaded
    }
    
    $sql = "INSERT INTO devices (name, brand, ram, storage, price, media) 
            VALUES (:name, :brand, :ram, :storage, :price, :media)";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'name' => $name,
        'brand' => $brand,
        'ram' => $ram,
        'storage' => $storage,
        'price' => $price,
        'media' => $file_name
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Device added successfully'
        ]);
    } else {
        throw new Exception('Failed to add device');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}