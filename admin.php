<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// Get initial stats
$stats = [
    'properties' => $conn->query("SELECT COUNT(*) FROM properties")->fetchColumn(),
    'users' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'devices' => $conn->query("SELECT COUNT(*) FROM devices")->fetchColumn(),
    'pending_applications' => $conn->query("SELECT COUNT(*) FROM service_applications WHERE status = 'pending'")->fetchColumn(),
    'visitors' => $conn->query("
        SELECT 
            COUNT(DISTINCT ip_address) as total,
            COUNT(DISTINCT CASE WHEN visited_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN ip_address END) as today,
            COUNT(DISTINCT CASE WHEN visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN ip_address END) as weekly,
            COUNT(DISTINCT CASE WHEN visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN ip_address END) as monthly
        FROM visitors
    ")->fetch(PDO::FETCH_ASSOC)
];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="assets/css/admin.css" rel="stylesheet">
        <link href="assets/css/visitor.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    </head>
    <body>

        <div class="admin-container">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-header">
                    <h4>
                        Admin Panel
                        <div class="notification-wrapper">
                            <div class="dropdown">
                                <button class="btn btn-link notification-bell" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-bell"></i>
                                    <?php
                                    $unreadCount = $conn->query("SELECT COUNT(*) FROM notifications WHERE read_at IS NULL")->fetchColumn();
                                    if ($unreadCount > 0): 
                                    ?>
                                    <span class="notification-badge"><?php echo $unreadCount; ?></span>
                                    <?php endif; ?>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                                    <div class="notification-header">
                                        <h6 class="dropdown-header">Notifications</h6>
                                        <?php if ($unreadCount > 0): ?>
                                        <button class="btn btn-link btn-sm mark-all-read">Mark all as read</button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notification-list">
                                        <?php
                                        $notifications = $conn->query("
                                            SELECT n.*, CONCAT(u.firstname, ' ', u.lastname) as user_name 
                                            FROM notifications n 
                                            LEFT JOIN users u ON n.user_id = u.id 
                                            ORDER BY n.created_at DESC 
                                            LIMIT 10
                                        ")->fetchAll(PDO::FETCH_ASSOC);

                                        if (empty($notifications)):
                                        ?>
                                        <div class="dropdown-item no-notifications">No notifications</div>
                                        <?php else: 
                                            foreach($notifications as $notif):
                                            $isUnread = is_null($notif['read_at']);
                                        ?>
                                        <div class="dropdown-item notification-item <?php echo $isUnread ? 'unread' : ''; ?>" 
                                             data-id="<?php echo $notif['id']; ?>">
                                            <div class="notification-content">
                                                <div class="notification-type">
                                                    <i class="fas fa-<?php echo getNotificationIcon($notif['type']); ?>"></i>
                                                </div>
                                                <div class="notification-details">
                                                    <p class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></p>
                                                    <small class="notification-meta">
                                                        <?php echo getTimeAgo($notif['created_at']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; endif; ?>
                                    </div>
                                    <?php if (count($notifications) == 10): ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </h4>
                </div>
                <nav class="sidebar-nav">
                    <a href="#" class="sidebar-link active" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="#" class="sidebar-link" data-section="properties">
                        <i class="fas fa-building"></i> Properties
                    </a>
                    <a href="#" class="sidebar-link" data-section="users">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a href="#" class="sidebar-link" data-section="devices">
                        <i class="fas fa-mobile-alt"></i> Devices
                    </a>
                    <a href="#" class="sidebar-link" data-section="applications">
                        <i class="fas fa-file-alt"></i> Applications
                    </a>
                    <a href="#" class="sidebar-link" data-section="blog">
                        <i class="fas fa-newspaper"></i> Blog Posts
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <div class="stats-grid">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="stat-details">
                                    <h3><?php echo $stats['properties']; ?></h3>
                                    <p>Total Properties</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-details">
                                    <h3><?php echo $stats['users']; ?></h3>
                                    <p>Total Users</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div class="stat-details">
                                    <h3><?php echo $stats['devices']; ?></h3>
                                    <p>Registered Devices</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="stat-details">
                                    <h3><?php echo $stats['pending_applications']; ?></h3>
                                    <p>Pending Applications</p>
                                </div>
                            </div>

                            <div class="stat-card visitor-stats-card">
                                <div class="stat-header">
                                    <div class="stat-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="stat-title">
                                        <h3>Visitor Analytics</h3>
                                        <p class="total-count"><?php echo number_format($stats['visitors']['total']); ?> Total Visitors</p>
                                    </div>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-row">
                                        <div class="stat-col">
                                            <div class="metric-card">
                                                <div class="metric-icon">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div class="metric-details">
                                                    <h4><?php echo number_format($stats['visitors']['today']); ?></h4>
                                                    <p>Today's Visitors</p>
                                                    <small class="trend">
                                                        <?php 
                                                        $yesterdayCount = $conn->query("
                                                            SELECT COUNT(DISTINCT ip_address) 
                                                            FROM visitors 
                                                            WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR)
                                                            AND visited_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                                                        ")->fetchColumn();
                                                        $trend = $stats['visitors']['today'] - $yesterdayCount;
                                                        $trendClass = $trend >= 0 ? 'up' : 'down';
                                                        $trendIcon = $trend >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                                        echo "<span class='$trendClass'><i class='fas $trendIcon'></i> " . abs($trend) . "</span> vs yesterday";
                                                        ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="stat-col">
                                            <div class="metric-card">
                                                <div class="metric-icon">
                                                    <i class="fas fa-calendar-week"></i>
                                                </div>
                                                <div class="metric-details">
                                                    <h4><?php echo number_format($stats['visitors']['weekly']); ?></h4>
                                                    <p>This Week</p>
                                                    <div class="metric-progress">
                                                        <?php
                                                        $weeklyGoal = 1000; // Set your weekly goal
                                                        $progress = min(($stats['visitors']['weekly'] / $weeklyGoal) * 100, 100);
                                                        ?>
                                                        <div class="progress">
                                                            <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                                                        </div>
                                                        <small><?php echo number_format($progress, 1); ?>% of goal</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="stat-col">
                                            <div class="metric-card">
                                                <div class="metric-icon">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                                <div class="metric-details">
                                                    <h4><?php echo number_format($stats['visitors']['monthly']); ?></h4>
                                                    <p>This Month</p>
                                                    <small class="avg-daily">
                                                        <?php
                                                        $avgDaily = round($stats['visitors']['monthly'] / 30);
                                                        echo "~" . number_format($avgDaily) . " visitors/day";
                                                        ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add this after your stats grid -->
                    <div class="revenue-section">
                        <div class="section-header">
                            <div class="revenue-overview">
                                <h2>Revenue Overview</h2>
                                <?php
                                // Get total revenue
                                $totalRevenue = $conn->query("
                                    SELECT SUM(amount) as total 
                                    FROM service_applications 
                                    WHERE status = 'completed'
                                ")->fetchColumn();
                                ?>
                                <div class="total-revenue">
                                    <span>Total Revenue:</span>
                                    <h3>₦<?php echo number_format($totalRevenue ?? 0, 2); ?></h3>
                                </div>
                            </div>
                            <div class="period-selector btn-group">
                                <button class="btn btn-outline-primary active" data-period="weekly">
                                    <i class="fas fa-calendar-week"></i> Weekly
                                </button>
                                <button class="btn btn-outline-primary" data-period="monthly">
                                    <i class="fas fa-calendar-alt"></i> Monthly
                                </button>
                                <button class="btn btn-outline-primary" data-period="yearly">
                                    <i class="fas fa-calendar"></i> Yearly
                                </button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Properties Section -->
                <div id="properties-section" class="content-section" style="display: none;">
                    <div class="section-header">
                        <h2>Manage Properties</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                            <i class="fas fa-plus"></i> Add Property
                        </button>
                    </div>

                    <div class="properties-grid">
                        <?php
                        $properties = $conn->query("SELECT * FROM properties ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                        foreach($properties as $property):
                        ?>
                        <div class="property-card" data-id="<?php echo $property['id']; ?>">
                            <div class="property-image">
                                <img src="uploads/properties/<?php echo $property['media'] ?: 'default-property.jpg'; ?>" 
                                    alt="<?php echo htmlspecialchars($property['title']); ?>">
                            </div>
                            <div class="property-info">
                                <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                                <p class="price">₦<?php echo number_format($property['price'], 2); ?></p>
                                <p class="plot-size"><i class="fas fa-ruler-combined"></i> <?php echo $property['plot_size']; ?> sqm</p>
                            </div>
                            <div class="property-actions">
                                <button class="btn btn-sm btn-primary edit-property" 
                                        data-id="<?php echo $property['id']; ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editPropertyModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-property" 
                                        data-id="<?php echo $property['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- blog section -->
                <div id="blog-section" class="content-section" style="display: none;">
                    <div class="section-header">
                        <h2>Manage Blog Posts</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBlogModal">
                            <i class="fas fa-plus"></i> Add Post
                        </button>
                    </div>

                    <div class="blog-posts-grid">
                        <!-- Posts will be loaded dynamically -->
                    </div>
                </div>
                <!-- Users Section -->
                <div id="users-section" class="content-section" style="display: none;">
                    <div class="section-header">
                        <h2>Manage Users</h2>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $conn->prepare("
                                    SELECT id, firstname, lastname, email, phone, created_at 
                                    FROM users 
                                    WHERE role = 'user' 
                                    ORDER BY created_at DESC
                                ");
                                $stmt->execute();
                                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach($users as $user):
                                ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <div class="phone-number">
                                            <?php echo htmlspecialchars($user['phone']); ?>
                                            <button class="btn btn-sm btn-link copy-phone" 
                                                    data-phone="<?php echo htmlspecialchars($user['phone']); ?>">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info view-user" 
                                                data-id="<?php echo $user['id']; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewUserModal">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-user" 
                                                data-id="<?php echo $user['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="devices-section" class="content-section" style="display: none;">
                    <div class="section-header">
                        <h2>Manage Devices</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                            <i class="fas fa-plus"></i> Add Device
                        </button>
                    </div>

                    <div class="devices-grid">
                        <?php
                        $devices = $conn->query("SELECT * FROM devices ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                        foreach($devices as $device):
                        ?>
                        <div class="device-card" data-id="<?php echo $device['id']; ?>">
                            <div class="device-image">
                                <img src="uploads/devices/<?php echo $device['media'] ?: 'default-device.jpg'; ?>" 
                                    alt="<?php echo htmlspecialchars($device['name']); ?>">
                            </div>
                            <div class="device-info">
                                <h3><?php echo htmlspecialchars($device['name']); ?></h3>
                                <p class="brand">Brand: <?php echo htmlspecialchars($device['brand']); ?></p>
                                <p class="specs">
                                    <span class="ram">RAM: <?php echo htmlspecialchars($device['ram']); ?></span> | 
                                    <span class="storage">Storage: <?php echo htmlspecialchars($device['storage']); ?></span>
                                </p>
                                <p class="price">₦<?php echo number_format($device['price'], 2); ?></p>
                            </div>
                            <div class="device-actions">
                                <button class="btn btn-sm btn-primary edit-device" 
                                        data-id="<?php echo $device['id']; ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editDeviceModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-device" 
                                        data-id="<?php echo $device['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- applications section -->
                <div id="applications-section" class="content-section" style="display: none;">
                    <div class="section-header">
                        <h2>Manage Applications</h2>
                        <div class="filter-buttons">
                            <button class="btn btn-outline-primary active" data-filter="all">All</button>
                            <button class="btn btn-outline-warning" data-filter="pending">Pending</button>
                            <button class="btn btn-outline-success" data-filter="completed">Completed</button>
                        </div>
                    </div>
                    
                    <div class="table-responsive mt-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Applicant</th>
                                    <th>Contact</th>
                                    <th>Service Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="applicationsTableBody">
                                <?php
                                $applications = $conn->query("
                                    SELECT a.*, CONCAT(u.firstname, ' ', u.lastname) as user_name, p.title as property_title 
                                    FROM service_applications a 
                                    LEFT JOIN users u ON a.user_id = u.id 
                                    LEFT JOIN properties p ON a.property_id = p.id 
                                    ORDER BY a.created_at DESC
                                ")->fetchAll(PDO::FETCH_ASSOC);

                                foreach($applications as $app):
                                    $statusClass = $app['status'] === 'pending' ? 'warning' : 'success';
                                ?>
                                <tr data-status="<?php echo htmlspecialchars($app['status']); ?>">
                                    <td><?php echo htmlspecialchars($app['reference']); ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($app['recipient_name']); ?></div>
                                        <small class="text-muted">User: <?php echo htmlspecialchars($app['user_name']); ?></small>
                                    </td>
                                    <td>
                                        <div class="phone-number">
                                            <?php echo htmlspecialchars($app['recipient_phone']); ?>
                                            <button class="btn btn-sm btn-link copy-phone" 
                                                    data-phone="<?php echo htmlspecialchars($app['recipient_phone']); ?>">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($app['service_type']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['property_title']); ?></small>
                                    </td>
                                    <td>₦<?php echo number_format($app['amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                    <td>
                                        <?php if($app['status'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-success complete-application" 
                                                data-id="<?php echo $app['id']; ?>"
                                                title="Mark as Completed">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-danger delete-application" 
                                                data-id="<?php echo $app['id']; ?>"
                                                title="Delete Application">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="assets/js/admin.js"></script>
    </body>
    <!-- User Details Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <div class="user-modal-header">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-title">
                            <h5 class="modal-title" id="viewUserModalLabel">User Profile</h5>
                            <span class="user-role badge bg-primary">User</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="user-details-container">
                        <div class="user-info-section basic-info">
                            <h6 class="section-title"><i class="fas fa-user me-2"></i>Basic Information</h6>
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Full Name</label>
                                    <p id="userDetailName" class="info-value">-</p>
                                </div>
                                <div class="info-item">
                                    <label>Email Address</label>
                                    <p id="userDetailEmail" class="info-value">-</p>
                                </div>
                                <div class="info-item">
                                    <label>Phone Number</label>
                                    <p id="userDetailPhone" class="info-value">-</p>
                                </div>
                                <div class="info-item">
                                    <label>Gender</label>
                                    <p id="userDetailGender" class="info-value">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="user-info-section activity">
                            <h6 class="section-title"><i class="fas fa-chart-line me-2"></i>Activity Overview</h6>
                            <div class="activity-stats">
                                <div class="stat-card">
                                    <div class="stat-icon bg-success-subtle">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div class="stat-info">
                                        <label>Properties Added</label>
                                        <h4 id="userDetailPropertiesAdded">0</h4>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon bg-primary-subtle">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="stat-info">
                                        <label>Properties Purchased</label>
                                        <h4 id="userDetailPropertiesPurchased">0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="user-info-section account">
                            <h6 class="section-title"><i class="fas fa-clock me-2"></i>Account Information</h6>
                            <div class="info-group">
                                <div class="info-item">
                                    <label>Member Since</label>
                                    <p id="userDetailJoined" class="info-value">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>                                       

    <!-- Add Property Modal -->
    <div class="modal fade" id="addPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPropertyForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (₦)</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Plot Size (sqm)</label>
                                <input type="number" class="form-control" name="plot_size" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Property Image</label>
                                <input type="file" class="form-control" name="media" accept="image/*" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="state" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" name="zip_code" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePropertyBtn">Save Property</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Property Modal -->
    <div class="modal fade" id="editPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editPropertyForm" enctype="multipart/form-data">
                        <input type="hidden" name="property_id" id="edit_property_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="edit_title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (₦)</label>
                                <input type="number" class="form-control" name="price" id="edit_price" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Plot Size (sqm)</label>
                                <input type="number" class="form-control" name="plot_size" id="edit_plot_size" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Property Image</label>
                                <input type="file" class="form-control" name="media" accept="image/*">
                                <small class="text-muted">Leave empty to keep existing image</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" id="edit_address" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" id="edit_city" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="state" id="edit_state" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" name="zip_code" id="edit_zip_code" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updatePropertyBtn">Update Property</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Device Modal -->
    <div class="modal fade" id="addDeviceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Device</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addDeviceForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Device Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" name="brand" required>
                            <small class="text-muted">Start with capital letter</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RAM</label>
                                <input type="text" class="form-control" name="ram" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Storage</label>
                                <input type="text" class="form-control" name="storage" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price (₦)</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Device Image</label>
                            <input type="file" class="form-control" name="media" accept="image/*" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveDeviceBtn">Save Device</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this before closing body tag -->
    <div class="modal fade" id="editDeviceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Device</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editDeviceForm" enctype="multipart/form-data">
                        <input type="hidden" name="id">
                        <div class="mb-3">
                            <label class="form-label">Device Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" name="brand" required>
                            <small class="text-muted">Start with capital letter</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RAM</label>
                                <input type="text" class="form-control" name="ram" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Storage</label>
                                <input type="text" class="form-control" name="storage" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price (₦)</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Device Image</label>
                            <input type="file" class="form-control" name="media" accept="image/*">
                            <small class="text-muted">Leave empty to keep existing image</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateDeviceBtn">Update Device</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Blog Post Modal -->
    <div class="modal fade" id="addBlogModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Blog Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addBlogForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">External Link</label>
                            <input type="url" class="form-control" name="external_link" required>
                            <small class="text-muted">Link to the full article</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Media Type</label>
                            <select class="form-select" name="media_type" required>
                                <option value="">Select media type</option>
                                <option value="youtube">YouTube Video</option>
                                <option value="video">Upload Video</option>
                                <option value="image">Image</option>
                            </select>
                        </div>

                        <div class="mb-3 media-input youtube-input" style="display: none;">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" class="form-control" name="youtube_url">
                            <small class="text-muted">Enter the full YouTube video URL</small>
                        </div>

                        <div class="mb-3 media-input file-input" style="display: none;">
                            <label class="form-label">Upload Media</label>
                            <input type="file" class="form-control" name="media_file">
                            <small class="text-muted">Select image or video file</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveBlogBtn">Save Post</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this before the closing body tag -->
    <div class="modal fade" id="editBlogModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Blog Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editBlogForm" enctype="multipart/form-data">
                        <input type="hidden" name="post_id">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">External Link</label>
                            <input type="url" class="form-control" name="external_link" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Media Type</label>
                            <select class="form-select" name="media_type" required>
                                <option value="">Select media type</option>
                                <option value="youtube">YouTube Video</option>
                                <option value="video">Upload Video</option>
                                <option value="image">Image</option>
                            </select>
                        </div>

                        <div class="mb-3 media-input youtube-input" style="display: none;">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" class="form-control" name="youtube_url">
                        </div>

                        <div class="mb-3 media-input file-input" style="display: none;">
                            <label class="form-label">Upload Media</label>
                            <input type="file" class="form-control" name="media_file">
                            <small class="text-muted">Leave empty to keep existing media</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateBlogBtn">Update Post</button>
                </div>
            </div>
        </div>
    </div>
</html>