<?php
require_once '../includes/db.php';
session_start();
header('Content-Type: application/json');

try {
    if (!isset($_POST['property_id'])) {
        throw new Exception('Property ID is required');
    }

    $propertyId = $_POST['property_id'];
    $media = null;

    // Handle file upload if new image is provided
    if (isset($_FILES['media']) && $_FILES['media']['error'] === 0) {
        $media = time() . '_' . basename($_FILES['media']['name']);
        $targetFile = "../uploads/properties/" . $media;
        
        if (!move_uploaded_file($_FILES['media']['tmp_name'], $targetFile)) {
            throw new Exception('Failed to upload image');
        }

        // Delete old image
        $stmt = $conn->prepare("SELECT media FROM properties WHERE id = ?");
        $stmt->execute([$propertyId]);
        $oldMedia = $stmt->fetchColumn();
        
        if ($oldMedia && file_exists("../uploads/properties/" . $oldMedia)) {
            unlink("../uploads/properties/" . $oldMedia);
        }
    }

    $sql = "UPDATE properties SET 
            title = ?, description = ?, price = ?, 
            plot_size = ?, address = ?, city = ?, 
            state = ?, zip_code = ?";
    $params = [
        $_POST['title'],
        $_POST['description'],
        $_POST['price'],
        $_POST['plot_size'],
        $_POST['address'],
        $_POST['city'],
        $_POST['state'],
        $_POST['zip_code']
    ];

    if ($media) {
        $sql .= ", media = ?";
        $params[] = $media;
    }

    $sql .= " WHERE id = ?";
    $params[] = $propertyId;

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);

    if (!$result) {
        throw new Exception('Failed to update property');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Property updated successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}