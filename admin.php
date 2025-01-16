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
    $propertyCount = $conn->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    $deviceCount = $conn->query("SELECT COUNT(*) FROM devices")->fetchColumn();
    $solutionCount = $conn->query("SELECT COUNT(*) FROM solutions")->fetchColumn();
} catch(PDOException $e) {
    $propertyCount = $deviceCount = $solutionCount = 0;
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
    </script>
</body>
</html>