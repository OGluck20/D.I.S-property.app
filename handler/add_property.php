<?php
require_once '../includes/db.php';
session_start();

header('Content-Type: application/json');

try {
    // Validate inputs
    $required_fields = ['title', 'description', 'price', 'plot_size', 'address', 'city', 'state', 'zip_code'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate price range (up to 1 trillion)
    $price = floatval($_POST['price']);
    if ($price <= 0 || $price > 1000000000000) { // 1 trillion
        throw new Exception("Price must be between 1 and 1,000,000,000,000");
    }

    // Handle file upload
    $media = '';
    if (isset($_FILES["media"]) && $_FILES["media"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png"];
        $filename = $_FILES["media"]["name"];
        $filetype = $_FILES["media"]["type"];
        $filesize = $_FILES["media"]["size"];

        // Verify file extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!array_key_exists($ext, $allowed)) {
            throw new Exception("Error: Please select a valid file format.");
        }

        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            throw new Exception("Error: File size is larger than 5MB.");
        }

        // Generate unique filename
        $media = time() . '_' . uniqid() . '.' . $ext;
        $destination = "../uploads/properties/" . $media;

        if (!move_uploaded_file($_FILES["media"]["tmp_name"], $destination)) {
            throw new Exception("Error moving uploaded file.");
        }
    }

    // Generate purchase code
    $purchase_code = 'PROP_' . strtoupper(uniqid());

    // Insert property
    $stmt = $conn->prepare("
        INSERT INTO properties (
            user_id, title, description, price, plot_size, 
            address, city, state, zip_code, media, 
            purchase_code, status
        ) VALUES (
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?,
            ?, 'available'
        )
    ");

    $result = $stmt->execute([
        $_SESSION['user_id'],
        $_POST['title'],
        $_POST['description'],
        $price,
        $_POST['plot_size'],
        $_POST['address'],
        $_POST['city'],
        $_POST['state'],
        $_POST['zip_code'],
        $media,
        $purchase_code
    ]);

    if (!$result) {
        throw new Exception('Failed to add property');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Property added successfully',
        'property_id' => $conn->lastInsertId()
    ]);

} catch (Exception $e) {
    // Delete uploaded file if exists
    if (isset($media) && !empty($media) && file_exists("../uploads/properties/" . $media)) {
        unlink("../uploads/properties/" . $media);
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}