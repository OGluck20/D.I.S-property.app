<?php
session_start();
require_once 'includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// Get dashboard stats
try {
    // Total properties
    $propertyCount = $conn->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    
    // Total devices
    $deviceCount = $conn->query("SELECT COUNT(*) FROM devices")->fetchColumn();
    
    // Total users
    $userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

} catch(PDOException $e) {
    $propertyCount = $deviceCount = $userCount = $solutionCount = 0;
}

$stmt = $conn->query("SELECT id, name, brand, price FROM devices ORDER BY created_at DESC");
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - D.I.S</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        {
        "type": "setting",
        "settings": {
            "mobile-warning": {
            "display": "none",
            "position": "fixed",
            "top": "0",
            "left": "0",
            "width": "100%",
            "height": "100%",
            "background": "#fff",
            "z-index": "9999",
            "padding": "20px",
            "text-align": "center",
            "display": "flex",
            "align-items": "center",
            "justify-content": "center",
            "flex-direction": "column"
            }
        }
        }
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --background: #f9fafb;
            --text: #2c3e50;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: white;
            padding: 2rem;
            box-shadow: 2px 0 5px var(--shadow);
        }

        .content {
            flex: 1;
            padding: 2rem;
            background: var(--background);
        }

        .nav-link {
            color: var(--text);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px var(--shadow);
        }

        .action-buttons {
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }
        .table-responsive {
            margin: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            margin: 0 2px;
        }

        .mobile-warning {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            z-index: 9999;
            padding: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        {
        "type": "setting",
        "settings": {
            "@media (max-width: 768px)": {
            ".admin-container": {
                "display": "none"
            },
            ".mobile-warning": {
                "display": "flex"
            }
            }
        }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h4 class="mb-4">Admin Panel</h4>
            <nav>
                <a href="#" class="nav-link active">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
                <a href="#applications" class="nav-link" data-page="applications">
                    <i class="fas fa-file-contract me-2"></i> Applications
                    <span class="badge bg-danger ms-2" id="pendingApplicationsCount">0</span>
                </a>
                <a href="#notifications" class="nav-link" data-page="notifications">
                    <i class="fas fa-bell me-2"></i> Notifications
                    <span class="badge bg-primary ms-2" id="unreadNotificationsCount">0</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Properties</h3>
                    <p class="h2"><?php echo $propertyCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Devices</h3>
                    <p class="h2"><?php echo $deviceCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Users</h3>
                    <p class="h2"><?php echo $userCount; ?></p>
                </div>
            </div>

            <!-- Content Sections -->
            <!-- Update the dashboard-content section in admin.php -->
            <div id="dashboard-content" class="content-section">
                <div class="dashboard-overview">
                    <!-- Welcome Section -->
                    <div class="welcome-section mb-4">
                        <h2>Welcome, Admin</h2>
                        <p class="text-muted">Here's what's happening today</p>
                    </div>

                    <!-- Recent Activities -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Recent Activities</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Action</th>
                                                    <th>User</th>
                                                    <th>Time</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Fetch recent activities
                                                $stmt = $conn->query("
                                                    SELECT a.*, u.username 
                                                    FROM activities a 
                                                    LEFT JOIN users u ON a.user_id = u.id 
                                                    ORDER BY a.created_at DESC 
                                                    LIMIT 5
                                                ");
                                                while ($activity = $stmt->fetch(PDO::FETCH_ASSOC)):
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                                    <td><?php echo htmlspecialchars($activity['username']); ?></td>
                                                    <td><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $activity['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                            <?php echo ucfirst($activity['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

            <div class="table-responsive">
                 <h2 class="mb-4">Manage Devices</h2>
                 <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devices as $device): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($device['name']); ?></td>
                                <td><?php echo htmlspecialchars($device['brand']); ?></td>
                                <td>₦<?php echo number_format($device['price'], 2); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-device" data-id="<?php echo $device['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-device" data-id="<?php echo $device['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                 </table>
            </div>

            <!-- Add Properties Table -->
            <div class="table-responsive properties-table">
                <h2 class="mb-4">Manage Properties</h2>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Updated query to use correct column names
                        $stmt = $conn->query("SELECT id, title, address, city, state, price, status FROM properties ORDER BY created_at DESC");
                        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($properties as $property): 
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($property['title']); ?></td>
                            <td>
                                <?php 
                                echo htmlspecialchars($property['address'] . ', ' . 
                                    $property['city'] . ', ' . 
                                    $property['state']); 
                                ?>
                            </td>
                            <td>₦<?php echo number_format($property['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($property['status'] ?? 'available'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-property" data-id="<?php echo $property['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-property" data-id="<?php echo $property['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4">
                <h2 class="mb-4">Manage Users</h2>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $users = $conn->query("SELECT id, username, email, role FROM users")->fetchAll();
                        foreach ($users as $user): 
                        ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-user" 
                                            data-id="<?php echo $user['id']; ?>"
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                            data-role="<?php echo htmlspecialchars($user['role']); ?>">
                                        <i class="fas fa-edit"></i>
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

            <div id="devices-content" class="content-section" style="display: none;">
                <!-- Devices management content -->
                <h2>Manage Devices</h2>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devices as $device): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($device['name']); ?></td>
                                <td><?php echo htmlspecialchars($device['brand']); ?></td>
                                <td>₦<?php echo number_format($device['price'], 2); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-device" data-id="<?php echo $device['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-device" data-id="<?php echo $device['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                 </table>
            </div>

            <div id="notifications-content" class="content-section" style="display: none;">
                <!-- Notifications content -->
                <h2>Notifications</h2>
                <div class="notifications-list">
                    <!-- Notifications will be loaded here -->
                </div>
            </div>

            <!-- Replace action buttons with modal triggers -->
            <div class="action-buttons">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#propertyModal">
                    <i class="fas fa-plus"></i> Add Property
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deviceModal">
                    <i class="fas fa-plus"></i> Add Device
                </button>
            </div>


            <!-- Update Property Modal -->
            <div class="modal fade" id="propertyModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Property</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="propertyForm" action="handler/add_property.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price (₦)</label>
                                    <input type="number" name="price" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Zip Code</label>
                                    <input type="text" name="zip_code" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Property Media</label>
                                    <input type="file" name="media" class="form-control" accept="image/*,video/*" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Property</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Device Modal -->
            <div class="modal fade" id="deviceModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Device</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="deviceForm" action="handler/add_device.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Brand</label>
                                    <select name="brand" class="form-control" required>
                                        <option value="Apple">Apple</option>
                                        <option value="Samsung">Samsung</option>
                                        <option value="Google">Google</option>
                                        <option value="OnePlus">OnePlus</option>
                                        <option value="Xiaomi">Xiaomi</option>
                                        <option value="HP">HP</option>
                                        <option value="Dell">Dell</option>
                                        <option value="Asus">Asus</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">RAM</label>
                                    <input type="text" name="ram" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Storage</label>
                                    <input type="text" name="storage" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" name="price" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Image</label>
                                    <input type="file" name="image" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Device</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Solution Modal -->
            <div class="modal fade" id="solutionModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Solution</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="solutionForm" action="handler/add_solutions.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Type</label>
                                    <select name="type" class="form-control" required>
                                        <option value="solar">Solar Installation</option>
                                        <option value="cctv">CCTV Installation</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" name="price" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Image</label>
                                    <input type="file" name="image" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Solution</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Edit Property Modal -->
            <div class="modal fade" id="editPropertyModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Property</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="editPropertyForm">
                            <div class="modal-body">
                                <input type="hidden" id="edit_property_id" name="id">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" id="edit_property_title" name="title" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" id="edit_property_location" name="location" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" id="edit_property_price" name="price" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select id="edit_property_status" name="status" class="form-control" required>
                                        <option value="available">Available</option>
                                        <option value="sold">Sold</option>
                                        <option value="rented">Rented</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Edit Device Modal -->
            <div class="modal fade" id="editDeviceModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Device</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="editDeviceForm">
                            <div class="modal-body">
                                <input type="hidden" id="edit_device_id" name="id">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" id="edit_device_name" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Brand</label>
                                    <select id="edit_device_brand" name="brand" class="form-control" required>
                                        <option value="Apple">Apple</option>
                                        <option value="Samsung">Samsung</option>
                                        <option value="Google">Google</option>
                                        <option value="OnePlus">OnePlus</option>
                                        <option value="Xiaomi">Xiaomi</option>
                                        <option value="HP">HP</option>
                                        <option value="Dell">Dell</option>
                                        <option value="Asus">Asus</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" id="edit_device_price" name="price" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit User Modal -->
            <div class="modal fade" id="editUserModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="editUserForm" action="handler/update_user.php" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="id" id="editUserId">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" id="editUsername" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="editEmail" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select name="role" id="editRole" class="form-control" required>
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Property Form Submit
            document.querySelector('#propertyForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                let formData = new FormData(this);
                
                fetch('handler/add_property.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Property has been added successfully.',
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!'
                        });
                    }
                });
            });

            // Device Form Submit
            document.querySelector('#deviceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                let formData = new FormData(this);
                
                fetch('handler/add_device.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Device has been added successfully.',
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!'
                        });
                    }
                });
            });

            // Solution Form Submit
            document.querySelector('#solutionForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                let formData = new FormData(this);
                
                fetch('handler/add_solutions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Solution has been added successfully.',
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!'
                        });
                    }
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Load devices content when clicking devices link
            document.querySelector('a[href="devices.php"]').addEventListener('click', function(e) {
                e.preventDefault();
                loadDevicesContent();
            });

            // Function to load devices content
            function loadDevicesContent() {
                fetch('handler/get_devices.php')
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('.content').innerHTML = html;
                        initializeActions();
                    });
            }

            // Initialize action buttons
            function initializeActions() {
                // Delete action
                document.querySelectorAll('.delete-device').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const deviceId = this.dataset.id;
                        deleteDevice(deviceId);
                    });
                });

                // Edit action
                document.querySelectorAll('.edit-device').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const deviceId = this.dataset.id;
                        editDevice(deviceId);
                    });
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Delete device handler
            document.querySelectorAll('.delete-device').forEach(button => {
                button.addEventListener('click', function() {
                    const deviceId = this.dataset.id;
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('handler/delete_device.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ id: deviceId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        'Device has been deleted.',
                                        'success'
                                    ).then(() => location.reload());
                                } else {
                                    throw new Error(data.message);
                                }
                            })
                            .catch(error => {
                                Swal.fire(
                                    'Error!',
                                    error.message,
                                    'error'
                                );
                            });
                        }
                    });
                });
            });

            // Delete property handler
            document.querySelectorAll('.delete-property').forEach(button => {
                button.addEventListener('click', function() {
                    const propertyId = this.dataset.id;
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('handler/delete_property.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ id: propertyId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        'Property has been deleted.',
                                        'success'
                                    ).then(() => location.reload());
                                } else {
                                    throw new Error(data.message);
                                }
                            })
                            .catch(error => {
                                Swal.fire(
                                    'Error!',
                                    error.message,
                                    'error'
                                );
                            });
                        }
                    });
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation handling
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.dataset.page) {
                        e.preventDefault();
                        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Hide all content
                        document.querySelector('.stats-grid').style.display = 'none';
                        document.querySelector('.table-responsive').style.display = 'none';
                        document.querySelector('.properties-table').style.display = 'none';
                        
                        // Show selected content
                        if (this.dataset.page === 'dashboard') {
                            document.querySelector('.stats-grid').style.display = 'grid';
                        } else if (this.dataset.page === 'devices') {
                            document.querySelector('.table-responsive').style.display = 'block';
                        } else if (this.dataset.page === 'properties') {
                            document.querySelector('.properties-table').style.display = 'block';
                        }
                    }
                });
            });

            // Property edit button handling
            document.querySelectorAll('.edit-property').forEach(button => {
                button.addEventListener('click', function() {
                    const propertyId = this.dataset.id;
                    const row = this.closest('tr');
                    
                    document.getElementById('edit_property_id').value = propertyId;
                    document.getElementById('edit_property_title').value = row.cells[0].textContent;
                    document.getElementById('edit_property_location').value = row.cells[1].textContent;
                    document.getElementById('edit_property_price').value = row.cells[2].textContent.replace('₦', '').replace(',', '');
                    document.getElementById('edit_property_status').value = row.cells[3].textContent.toLowerCase();
                    
                    new bootstrap.Modal(document.getElementById('editPropertyModal')).show();
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize edit buttons
            document.querySelectorAll('.edit-device').forEach(button => {
                button.addEventListener('click', function() {
                    const deviceId = this.dataset.id;
                    const row = this.closest('tr');
                    const name = row.cells[0].textContent;
                    const brand = row.cells[1].textContent;
                    const price = row.cells[2].textContent.replace('₦', '').replace(',', '');

                    // Populate modal fields
                    document.getElementById('edit_device_id').value = deviceId;
                    document.getElementById('edit_device_name').value = name;
                    document.getElementById('edit_device_brand').value = brand;
                    document.getElementById('edit_device_price').value = price;

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('editDeviceModal'));
                    modal.show();
                });
            });

            // Handle form submission
            document.getElementById('editDeviceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('handler/update_device.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Device updated successfully',
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.message
                    });
                });
            });
        });

        // User edit modal handler
        document.querySelectorAll('.edit-user').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                document.getElementById('editUserId').value = this.dataset.id;
                document.getElementById('editUsername').value = this.dataset.username;
                document.getElementById('editEmail').value = this.dataset.email;
                document.getElementById('editRole').value = this.dataset.role;
                modal.show();
            });
        });

        // User edit form submission
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                id: document.getElementById('editUserId').value,
                username: document.getElementById('editUsername').value,
                email: document.getElementById('editEmail').value,
                role: document.getElementById('editRole').value
            };

            fetch('handler/update_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'User updated successfully',
                        timer: 1500
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        });

        // Delete user handler (existing code with error handling)
        document.querySelectorAll('.delete-user').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.id;
                Swal.fire({
                    title: 'Delete User?',
                    text: "This cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('handler/delete_user.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: userId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Deleted!', 'User removed', 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        });
                    }
                });
            });
        });

    document.addEventListener('DOMContentLoaded', function() {
    // Show dashboard content by default
    showContent('dashboard');

        // Handle navigation clicks
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                
                // Update active state
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding content
                showContent(page);
            });
        });
    });

    function showContent(page) {
        // Hide all content sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
        });
        
        // Always hide stats grid first
        const statsGrid = document.querySelector('.stats-grid');
        if (statsGrid) {
            statsGrid.style.display = 'none';
        }
        
        // Show selected content
        const contentElement = document.getElementById(`${page}-content`);
        if (contentElement) {
            contentElement.style.display = 'block';
            
            // Show stats grid only for dashboard
            if (page === 'dashboard' && statsGrid) {
                statsGrid.style.display = 'grid';
            }
        }
    }

    // Content loading functions
    function loadProperties() {
        const propertiesContent = document.getElementById('properties-content');
        if (propertiesContent) {
            propertiesContent.style.display = 'block';
        }
    }

    function loadUsers() {
        const usersContent = document.getElementById('users-content');
        if (usersContent) {
            usersContent.style.display = 'block';
        }
    }

    function loadDevices() {
        const devicesContent = document.getElementById('devices-content');
        if (devicesContent) {
            devicesContent.style.display = 'block';
        }
    }

    function loadNotifications() {
        const notificationsContent = document.getElementById('notifications-content');
        if (notificationsContent) {
            notificationsContent.style.display = 'block';
        }
    }

        // Add this to your existing script section in admin.php
    document.addEventListener('DOMContentLoaded', function() {
        // Function to update unread count
        function updateUnreadCount() {
            fetch('handler/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_unread_count'
            })
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('unreadNotificationsCount');
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'inline' : 'none';
            });
        }

        // Update count every 30 seconds
        setInterval(updateUnreadCount, 30000);
        updateUnreadCount(); // Initial count

        // Mark single notification as read
        document.querySelectorAll('.notification-item.unread').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                fetch('handler/notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=mark_read&id=${notificationId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.remove('unread');
                        updateUnreadCount();
                    }
                });
            });
        });

        // Mark all as read
        document.querySelector('.mark-all-read').addEventListener('click', function() {
            fetch('handler/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                    });
                    updateUnreadCount();
                }
            });
        });
    });

    // Add this to your existing script section
document.addEventListener('DOMContentLoaded', function() {
    // Prepare the data
    const weeklyData = <?php echo json_encode($weeklyRevenue); ?>;
    const monthlyData = <?php echo json_encode($monthlyRevenue); ?>;
    const yearlyData = <?php echo json_encode($yearlyRevenue); ?>;
    
    // Initialize the chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    let revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Revenue (₦)',
                data: [],
                borderColor: '#2ecc71',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₦' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Function to update chart data
    function updateChart(period) {
        let data;
        switch(period) {
            case 'week':
                data = weeklyData;
                break;
            case 'month':
                data = monthlyData;
                break;
            case 'year':
                data = yearlyData;
                break;
        }

        revenueChart.data.labels = data.map(item => item.date);
        revenueChart.data.datasets[0].data = data.map(item => item.total);
        revenueChart.update();
    }

    // Initialize with weekly data
    updateChart('week');

    // Handle period button clicks
    document.querySelectorAll('[data-period]').forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('[data-period]').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Update chart
            updateChart(this.dataset.period);
        });
    });
});
// Add this to your existing script section
document.addEventListener('DOMContentLoaded', function() {
    // Set application ID when upload button is clicked
    document.querySelectorAll('.upload-document').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('application_id').value = this.dataset.id;
        });
    });

    // Handle document upload
    document.getElementById('uploadDocumentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        
        fetch('handler/upload_document.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Document uploaded successfully',
                    timer: 2000
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message
            });
        });
    });
});
    </script>
</body>
</html>