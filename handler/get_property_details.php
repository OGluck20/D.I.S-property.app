<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
        throw new Exception('Invalid property ID');
    }

    $stmt = $conn->prepare("SELECT plot_size FROM properties WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Property not found');
    }

    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
exit();