<?php
function logActivity($userId, $action, $description = null, $status = 'pending') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO activities (user_id, action, description, status, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([$userId, $action, $description, $status]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}

function getNotificationIcon($type) {
    switch ($type) {
        case 'application':
            return 'file-alt';
        case 'user':
            return 'user';
        case 'property':
            return 'building';
        case 'device':
            return 'mobile-alt';
        default:
            return 'bell';
    }
}

function getTimeAgo($timestamp) {
    $time = strtotime($timestamp);
    $curr_time = time();
    $time_diff = $curr_time - $time;

    if ($time_diff < 60) {
        return 'Just now';
    } elseif ($time_diff < 3600) {
        $mins = round($time_diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($time_diff < 86400) {
        $hours = round($time_diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } else {
        $days = round($time_diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
}