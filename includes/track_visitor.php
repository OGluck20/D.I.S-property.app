<?php
function trackVisitor($conn) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $page = $_SERVER['REQUEST_URI'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $timestamp = date('Y-m-d H:i:s');

    // Determine device type from user agent
    $deviceType = 'Desktop';
    if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $userAgent)) {
        $deviceType = 'Tablet';
    } else if (preg_match('/(mobile|android|iphone|ipod|windows phone)/i', $userAgent)) {
        $deviceType = 'Mobile';
    }

    try {
        // First check if this IP has visited in the last hour
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM visitors 
            WHERE ip_address = ? 
            AND visited_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            AND page_visited = ?
        ");
        $stmt->execute([$ip, $page]);
        $recentVisit = $stmt->fetchColumn();

        // If no recent visit from this IP to this page, insert new record
        if ($recentVisit == 0) {
            $stmt = $conn->prepare("
                INSERT INTO visitors (
                    ip_address, 
                    page_visited, 
                    user_agent, 
                    device_type,
                    visited_at
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$ip, $page, $userAgent, $deviceType, $timestamp]);
        }

        // Update total page views regardless of IP
        $stmt = $conn->prepare("
            INSERT INTO page_views (
                page_url,
                view_date,
                view_count
            ) VALUES (?, CURRENT_DATE(), 1)
            ON DUPLICATE KEY UPDATE view_count = view_count + 1
        ");
        $stmt->execute([$page]);

    } catch (PDOException $e) {
        error_log("Error tracking visitor: " . $e->getMessage());
    }
}