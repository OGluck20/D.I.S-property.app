<?php
// filepath: /c:/Users/USER/xampp/htdocs/D.I.S-property.app/handler/notifications.php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'mark_read':
            $notificationId = $_POST['id'] ?? null;
            if ($notificationId) {
                $stmt = $conn->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND read_at IS NULL");
                $stmt->execute([$notificationId]);
            }
            break;

        case 'mark_all_read':
            $stmt = $conn->prepare("UPDATE notifications SET read_at = NOW() WHERE read_at IS NULL");
            $stmt->execute();
            break;

        case 'get_unread_count':
            $count = $conn->query("SELECT COUNT(*) FROM notifications WHERE read_at IS NULL")->fetchColumn();
            echo json_encode(['count' => $count]);
            exit;

        case 'get_recent':
            $notifications = $conn->query("
                SELECT n.*, u.username 
                FROM notifications n 
                LEFT JOIN users u ON n.user_id = u.id 
                ORDER BY n.created_at DESC 
                LIMIT 10
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications
            ]);
            exit;

        default:
            throw new Exception('Invalid action');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}