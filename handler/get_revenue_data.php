<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $period = $_GET['period'] ?? 'weekly';
    
    switch($period) {
        case 'weekly':
            $query = "
                SELECT 
                    DATE(created_at) as date,
                    SUM(amount) as total
                FROM service_applications 
                WHERE status = 'completed'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ";
            break;
            
        case 'monthly':
            $query = "
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as date,
                    SUM(amount) as total
                FROM service_applications
                WHERE status = 'completed'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY date ASC
            ";
            break;
            
        case 'yearly':
            $query = "
                SELECT 
                    YEAR(created_at) as date,
                    SUM(amount) as total
                FROM service_applications
                WHERE status = 'completed'
                GROUP BY YEAR(created_at)
                ORDER BY date ASC
            ";
            break;
    }
    
    $stmt = $conn->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert amounts to numbers
    $data = array_map(function($row) {
        $row['total'] = floatval($row['total']);
        return $row;
    }, $data);

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch revenue data: ' . $e->getMessage()
    ]);
}