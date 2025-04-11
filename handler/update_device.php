<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('Device ID is required');
    }

    // Handle file upload if new image is provided
    $media = null;
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/devices/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['media']['name']);
        $targetPath = $targetDir . $fileName;
        
        if (move_uploaded_file($_FILES['media']['tmp_name'], $targetPath)) {
            $media = $fileName;
        } else {
            throw new Exception('Failed to upload image');
        }
    }

    // Prepare the update query
    $sql = "UPDATE devices SET 
            name = ?, 
            brand = ?, 
            ram = ?, 
            storage = ?, 
            price = ?";
    $params = [
        $_POST['name'],
        $_POST['brand'],
        $_POST['ram'],
        $_POST['storage'],
        $_POST['price']
    ];

    // Add media to update if new file was uploaded
    if ($media) {
        $sql .= ", media = ?";
        $params[] = $media;
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update device');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}