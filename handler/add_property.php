<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Validate inputs
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $purchase_code = uniqid('purchase_');

    if (empty($title) || empty($price)) {
        throw new Exception('Required fields missing');
    }

    // Handle file upload
    $target_dir = "../uploads/properties/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $new_filename = '';
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['media'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'];
        
        if (!in_array($file_ext, $allowed_types)) {
            throw new Exception('Invalid file type');
        }
        
        $new_filename = uniqid('property_', true) . '.' . $file_ext;
        if (!move_uploaded_file($file['tmp_name'], $target_dir . $new_filename)) {
            throw new Exception('Failed to upload file');
        }
    }

    // Database insert
    $sql = "INSERT INTO properties (user_id, title, description, price, address, city, state, zip_code, media, purchase_code) 
            VALUES (:user_id, :title, :description, :price, :address, :city, :state, :zip_code, :media, :purchase_code)";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'user_id' => $user_id,
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'zip_code' => $zip_code,
        'media' => $new_filename,
        'purchase_code' => $purchase_code
    ]);

    if ($result) {
        // WhatsApp notification
        $admin_phone = '+2349046741088';
        $whatsapp_message = "New property added:\n\n"
            . "Title: $title\n"
            . "Price: ₦$price\n"
            . "Address: $address\n"
            . "City: $city, $state\n"
            . "Purchase Code: $purchase_code\n"
            . "Media: " . ($new_filename ? "uploads/properties/$new_filename" : "No media");

        echo json_encode([
            'success' => true,
            'message' => 'Property added successfully',
            'whatsapp_url' => "https://wa.me/$admin_phone?text=" . urlencode($whatsapp_message)
        ]);
    } else {
        throw new Exception('Failed to add property');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}