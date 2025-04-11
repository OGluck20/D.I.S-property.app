<?php
// File: api/get_visitor_stats.php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Get the period from request (default to daily)
$period = isset($_GET['period']) ? $_GET['period'] : 'daily';

// Set time range based on period
switch ($period) {
    case 'monthly':
        $timeRange = "DATE_SUB(NOW(), INTERVAL 12 MONTH)";
        $groupBy = "DATE_FORMAT(visited_at, '%Y-%m')";
        $dateFormat = "DATE_FORMAT(visited_at, '%b %Y')";
        break;
    case 'weekly':
        $timeRange = "DATE_SUB(NOW(), INTERVAL 12 WEEK)";
        $groupBy = "YEARWEEK(visited_at, 3)";
        $dateFormat = "DATE_FORMAT(visited_at, '%b %d')";
        break;
    case 'daily':
    default:
        $timeRange = "DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $groupBy = "DATE(visited_at)";
        $dateFormat = "DATE_FORMAT(visited_at, '%b %d')";
        break;
}

// Get visitor traffic data
$trafficStmt = $conn->prepare("
    SELECT 
        $dateFormat AS date_label,
        COUNT(DISTINCT ip_address) AS unique_visitors,
        COUNT(*) AS page_views
    FROM visitors
    WHERE visited_at >= $timeRange
    GROUP BY $groupBy
    ORDER BY MIN(visited_at)
");
$trafficStmt->execute();
$trafficData = $trafficStmt->fetchAll(PDO::FETCH_ASSOC);

// Get most visited pages
$pagesStmt = $conn->prepare("
    SELECT 
        CASE
            WHEN page_visited = '/' THEN 'Home'
            WHEN page_visited LIKE '/properties%' THEN 'Properties'
            WHEN page_visited LIKE '/about%' THEN 'About'
            WHEN page_visited LIKE '/contact%' THEN 'Contact'
            WHEN page_visited LIKE '/blog%' THEN 'Blog'
            ELSE SUBSTRING_INDEX(page_visited, '/', -1)
        END AS page_name,
        COUNT(*) AS visit_count
    FROM visitors
    WHERE visited_at >= $timeRange
    GROUP BY page_name
    ORDER BY visit_count DESC
    LIMIT 5
");
$pagesStmt->execute();
$pagesData = $pagesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get device breakdown
$devicesStmt = $conn->prepare("
    SELECT 
        CASE
            WHEN user_agent LIKE '%Android%' THEN 'Mobile'
            WHEN user_agent LIKE '%iPhone%' OR user_agent LIKE '%iPad%' OR user_agent LIKE '%iPod%' THEN 'Mobile'
            WHEN user_agent LIKE '%Windows Phone%' THEN 'Mobile'
            WHEN user_agent LIKE '%Windows%' THEN 'Desktop'
            WHEN user_agent LIKE '%Macintosh%' OR user_agent LIKE '%Mac OS%' THEN 'Desktop'
            WHEN user_agent LIKE '%Linux%' THEN 'Desktop'
            ELSE 'Other'
        END AS device_type,
        COUNT(DISTINCT ip_address) AS visitor_count
    FROM visitors
    WHERE visited_at >= $timeRange
    GROUP BY device_type
    ORDER BY visitor_count DESC
");
$devicesStmt->execute();
$devicesData = $devicesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent visitors
$recentStmt = $conn->prepare("
    SELECT ip_address, page_visited, user_agent, visited_at
    FROM visitors
    ORDER BY visited_at DESC
    LIMIT 10
");
$recentStmt->execute();
$recentVisitors = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// Format data for charts
$trafficLabels = [];
$trafficVisitors = [];
$trafficPageViews = [];

foreach ($trafficData as $data) {
    $trafficLabels[] = $data['date_label'];
    $trafficVisitors[] = (int) $data['unique_visitors'];
    $trafficPageViews[] = (int) $data['page_views'];
}

$pageLabels = [];
$pageValues = [];

foreach ($pagesData as $data) {
    $pageLabels[] = $data['page_name'];
    $pageValues[] = (int) $data['visit_count'];
}

$deviceLabels = [];
$deviceValues = [];

foreach ($devicesData as $data) {
    $deviceLabels[] = $data['device_type'];
    $deviceValues[] = (int) $data['visitor_count'];
}

// Prepare response
$response = [
    'traffic' => [
        'labels' => $trafficLabels,
        'visitors' => $trafficVisitors,
        'pageViews' => $trafficPageViews
    ],
    'pages' => [
        'labels' => $pageLabels,
        'values' => $pageValues
    ],
    'devices' => [
        'labels' => $deviceLabels,
        'values' => $deviceValues
    ],
    'recentVisitors' => $recentVisitors
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>