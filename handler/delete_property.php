<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('Property ID is required');
    }

    // Get property image before deletion
    $stmt = $conn->prepare("SELECT media FROM properties WHERE id = ?");
    $stmt->execute([$data['id']]);
    $property = $stmt->fetch();

    // Delete property
    $stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
    $result = $stmt->execute([$data['id']]);

    if (!$result) {
        throw new Exception('Failed to delete property');
    }

    // Delete property image if exists
    if ($property && $property['media']) {
        $imagePath = "../uploads/properties/" . $property['media'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Property deleted successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}