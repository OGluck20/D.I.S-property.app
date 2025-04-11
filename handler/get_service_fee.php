<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['service'])) {
        throw new Exception('Service type required');
    }

    $stmt = $conn->prepare("SELECT price_per_sqm, fixed_fee FROM services WHERE service_type = ?");
    $stmt->execute([$_GET['service']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Invalid service type');
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