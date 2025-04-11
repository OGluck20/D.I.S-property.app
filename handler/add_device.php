<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Handle file upload
    $targetDir = "../uploads/devices/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $media = '';
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $fileName = time() . '_' . basename($_FILES['media']['name']);
        $targetPath = $targetDir . $fileName;
        
        if (move_uploaded_file($_FILES['media']['tmp_name'], $targetPath)) {
            $media = $fileName;
        } else {
            throw new Exception('Failed to upload image.');
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO devices (name, brand, ram, storage, price, media, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $_POST['name'],
        $_POST['brand'],
        $_POST['ram'],
        $_POST['storage'],
        $_POST['price'],
        $media
    ]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to add device');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}