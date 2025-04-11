<?php
include 'includes/header.php';
include 'includes/db.php';
// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, created_at, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<style>
:root {
--primary: #2ecc71;
--primary-dark: #27ae60;
--secondary: #34495e;
--accent: #3498db;
--background: #f9fafb;
--text: #2c3e50;
--shadow: rgba(0, 0, 0, 0.1);
}

.dashboard-wrapper {
    display: flex;
    min-height: calc(110vh - 130px);
}

.dashboard-sidebar {
    width: 280px;
    background: #ffffff;
    border-right: 1px solid #e9ecef;
    padding: 25px 20px;
    box-shadow: 4px 0 15px rgba(0, 0, 0, 0.03);
    top: 80px;
    height: calc(100vh - 120px);
}

.dashboard-main {
    flex: 1;
    padding: 30px;
    background: #f8f9fa;
}


.sidebar-nav .nav-link {
    color: #495057;
    padding: 14px 20px;
    border-radius: 8px;
    margin: 8px 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    font-weight: 500;
    background: #f9fafb;
    border-left: 4px solid transparent;
}

.sidebar-nav .nav-link:hover {
    background: #f8f9fa;
    color: #2c3e50;
    transform: translateX(5px);
    border-left-color: var(--primary);
    box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.05);
}

.sidebar-nav .nav-link.active {
    background: var(--primary);
    color: white !important;
    border-left-color: var(--primary-dark);
}

.sidebar-nav .nav-link i {
    width: 25px;
    font-size: 1.1rem;
    transition: transform 0.3s ease;
}

.sidebar-nav .nav-link:hover i {
    transform: scale(1.1);
}

.user-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary);
    padding: 3px;
    margin-bottom: 15px;
}

.greeting-header {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

/* Mobile First Styles */
.dashboard-wrapper {
    flex-direction: column;
}

.dashboard-sidebar {
    width: 20%;
    height: auto;
    position: relative;
    top: 0;
    padding: 15px;
    box-shadow: none;
    border-right: 0;
    border-bottom: 1px solid #e9ecef;
}

.dashboard-main {
    padding: 20px;
}

/* Mobile Menu Toggle */
.menu-toggle {
    display: none;
    background: var(--primary);
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    width: 50%;
}

.close-menu{
    color: var(--primary);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    padding: 10px auto;
    border: 2px solid var(--primary);
}

/* Media Queries */
@media (max-width: 991px) {  /* Changed to 991px breakpoint */
    .menu-toggle {
        display: block;
    }
    
    .dashboard-sidebar {
        width: 100%;
        left: -100%;
        top: 0;
        height: 100vh;
        padding-top: 15px;
        display: none;
    }
    
    .dashboard-sidebar.active {
        left: 0;
    }
    
    .close-menu {
        top: 5px;
        right: 5px;
    }
    
    #applicationModal .modal-dialog {
        margin-top: 160px !important;
    }
}

@media (min-width: 769px) {
    .dashboard-wrapper {
        flex-direction: row;
    }
}
</style>
<!-- Update CSS link -->
<link href="/css/styles.css" rel="stylesheet">

<!-- Update JS references -->
<script src="/js/main.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<button class="menu-toggle d-lg-none fixed-top" style="left: 60%; top: 100px; z-index: 2000;">
    <i class="fas fa-bars me-2"></i> Menu
</button>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
        <button class="close-menu d-lg-none">
            <i class="fas fa-times"></i>
        </button>
        <div class="text-center mb-4">
            <?php
            $avatar_file = !empty($user['avatar']) ? $user['avatar'] : 'default-avatar.jpg';
            $avatar_path = "uploads/avatars/{$avatar_file}";
            ?>
            <img src="<?php echo $avatar_path; ?>" 
                 class="user-avatar" 
                 alt="<?php echo htmlspecialchars($user['username']); ?>"
                 onerror="this.src='uploads/avatars/default-avatar.jpg'">
            <h4 class="mt-2"><?php echo htmlspecialchars($user['username']); ?></h4>
        </div>
        
        <nav class="sidebar-nav">
            <a href="client_dashboard.php" class="nav-link active">
                <i class="fas fa-home me-2"></i> Home
            </a>
            <a href="#purchases" class="nav-link">
                <i class="fas fa-shopping-bag me-2"></i> Purchases
            </a>
            <a href="#applications" class="nav-link">
                <i class="fas fa-file-alt me-2"></i> Applications
            </a>
            <a href="#documents" class="nav-link">
                <i class="fas fa-folder-open me-2"></i> Documents
            </a>
            <div class="sidebar-divider"></div>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user me-2"></i> Profile
            </a>
            <a href="index.php" class="nav-link">
                <i class="fas fa-arrow-left me-2"></i> Return Home
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="dashboard-main">
        <div class="greeting-header">
            <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <p class="lead text-muted">Member since <?php 
                echo date('F Y', strtotime($user['created_at']));
            ?></p>
        </div>

        <!-- Purchases Section -->
        <section id="purchases">
            <h3 class="mb-4"><i class="fas fa-home me-2"></i>Your Purchased Properties</h3>
            <div class="row">
                <?php
                $stmt = $conn->prepare("
                    SELECT * FROM properties 
                    WHERE purchaser_id = :user_id 
                    AND status = 'sold'
                ");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($purchases)) {
                    echo '<div class="col-12">
                            <div class="alert alert-info">No purchased properties found.</div>
                          </div>';
                } else {
                    foreach ($purchases as $property) {
                        echo '
                        <div class="col-md-4 mb-4">
                            <div class="card property-card">
                                <img src="uploads/properties/'.$property['media'].'" 
                                     class="card-img-top" 
                                     alt="'.$property['title'].'">
                                <div class="card-body">
                                    <h5 class="card-title">'.$property['title'].'</h5>
                                    <p class="text-success">Purchased on: '.date('M d, Y', strtotime($property['updated_at'])).'</p>
                                    <p>Transaction ID: '.$property['purchase_code'].'</p>
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between">
                                            <a href="property.php?id='.$property['id'].'" class="btn btn-primary">
                                                View Details
                                            </a>
                                            <button class="btn btn-success apply-btn" 
                                                    data-property-id="'.$property['id'] .'"
                                                    data-property-title="'.htmlspecialchars($property['title']).'">
                                                Apply For...
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                }
                ?>
            </div>
        </div>

        <!-- Profile content section -->
        <div id="profile-content" class="content-section" style="display: none;">
            <div class="profile-wrapper">
                <div class="row">
                    <!-- Profile Header -->
                    <div class="col-12 mb-4">
                        <div class="profile-header-card">
                            <div class="profile-cover"></div>
                            <div class="profile-info">
                                <div class="profile-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="profile-details">
                                    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                                    <p class="text-muted">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Content -->
                    <div class="col-md-6 mb-4">
                        <div class="profile-card">
                            <div class="card-header">
                                <h3><i class="fas fa-user-edit me-2"></i>Account Information</h3>
                            </div>
                            <div class="card-body">
                                <form id="updateProfileForm">
                                    <div class="form-group mb-3">
                                        <label for="username">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="email">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="profile-card">
                            <div class="card-header">
                                <h3><i class="fas fa-lock me-2"></i>Security</h3>
                            </div>
                            <div class="card-body">
                                <form id="changePasswordForm">
                                    <div class="form-group mb-3">
                                        <label for="current_password">Current Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            <input type="password" class="form-control" id="current_password" 
                                                name="current_password" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="new_password">New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="new_password" 
                                                name="new_password" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="confirm_password">Confirm New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="confirm_password" 
                                                name="confirm_password" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications content section -->
        <div id="applications-content" class="content-section" style="display: none;">
            <div class="applications-wrapper">
                <div class="row">
                    <!-- Applications Header -->
                    <div class="col-12 mb-4">
                        <div class="applications-header-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3><i class="fas fa-file-alt me-2"></i>Your Applications</h3>
                                    <p class="text-muted mb-0">Track and manage your service applications</p>
                                </div>
                                <div class="application-stats">
                                    <?php
                                    // Get application statistics
                                    $statsStmt = $conn->prepare("
                                        SELECT 
                                            COUNT(*) as total,
                                            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                                        FROM service_applications 
                                        WHERE user_id = ?
                                    ");
                                    $statsStmt->execute([$_SESSION['user_id']]);
                                    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <div class="stats-card">
                                        <span class="stats-number"><?php echo $stats['total']; ?></span>
                                        <span class="stats-label">Total</span>
                                    </div>
                                    <div class="stats-card text-success">
                                        <span class="stats-number"><?php echo $stats['completed']; ?></span>
                                        <span class="stats-label">Completed</span>
                                    </div>
                                    <div class="stats-card text-warning">
                                        <span class="stats-number"><?php echo $stats['pending']; ?></span>
                                        <span class="stats-label">Pending</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Applications Table -->
                    <div class="col-12">
                        <div class="applications-card">
                            <div class="table-responsive">
                                <table class="table table-hover application-table">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Service Type</th>
                                            <th>Property</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT sa.*, p.title as property_title 
                                            FROM service_applications sa
                                            LEFT JOIN properties p ON sa.property_id = p.id
                                            WHERE sa.user_id = ? 
                                            ORDER BY sa.created_at DESC
                                        ");
                                        $stmt->execute([$_SESSION['user_id']]);
                                        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($applications as $app):
                                            $statusClass = $app['status'] == 'completed' ? 'success' : 
                                                        ($app['status'] == 'pending' ? 'warning' : 'danger');
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="reference-number">
                                                    <?php echo htmlspecialchars($app['reference']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="service-type">
                                                    <i class="fas fa-file-signature me-1"></i>
                                                    <?php echo htmlspecialchars($app['service_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="property-title">
                                                    <?php echo htmlspecialchars($app['property_title'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="amount">
                                                    ₦<?php echo number_format($app['amount'], 2); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="date">
                                                    <?php echo date('M d, Y', strtotime($app['created_at'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $statusClass; ?> status-badge">
                                                    <?php echo ucfirst($app['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary view-details" 
                                                        data-application-id="<?php echo $app['id']; ?>">
                                                    <i class="fas fa-eye"></i> View
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
            </div>
        </div>

    <!-- Update the messages content section HTML structure -->
    <div id="messages-content" class="content-section" style="display: none;">
        <div class="messages-header">
            <h3>
                <i class="fas fa-envelope"></i>
                Your Messages
            </h3>
        </div>
        <div class="messages-container">
            <!-- Messages will be loaded here dynamically -->
            <div class="spinner-container">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Updated Application Modal -->
<div class="modal fade" id="applicationModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply for Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="applicationForm">
                <div class="modal-body">
                    <input type="hidden" id="propertyId" name="property_id">
                    
                    <!-- Service Selection -->
                    <div class="mb-3">
                        <label class="form-label">Select Service</label>
                        <select class="form-select" id="serviceType" required>
                            <option value="">Choose a service...</option>
                            <option value="C of O">Certificate of Occupancy (C of O)</option>
                            <option value="Survey">Land Survey</option>
                            <option value="BOQ">Bill of Quantities (BOQ)</option>
                        </select>
                    </div>

                    <!-- Display Area -->
                    <div class="calculation-results" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Plot Size</label>
                            <input type="text" class="form-control" id="plotSizeDisplay" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Fee</label>
                            <input type="text" class="form-control" id="serviceFee" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="proceedToPay" disabled>Proceed to Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Correct loading order -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="your-custom-scripts.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.dashboard-sidebar');
    const closeBtn = document.querySelector('.close-menu');

    if(menuToggle && sidebar) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
            menuToggle.style.display = 'none';
            sidebar.style.display = 'block';
        });
        
        closeBtn.addEventListener('click', function() {
            sidebar.classList.remove('active');
            menuToggle.style.display = 'block';
            sidebar.style.display = 'none';
        });
        
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !e.target.closest('.menu-toggle')) {
                sidebar.classList.remove('active');
            }
        });
    } else {
        console.error('Mobile menu elements not found');
    }
});

document.querySelectorAll('.apply-btn').forEach(btn => {
    
    btn.addEventListener('click', function() {
        const propertyId = this.dataset.propertyId;
        const modal = new bootstrap.Modal(document.getElementById('applicationModal'));
        document.getElementById('propertyId').value = propertyId;
        modal.show();
    });
});

document.getElementById('serviceType').addEventListener('change', async function() {
    try {
        const serviceType = this.value;
        const propertyId = document.getElementById('propertyId').value;
        
        if (!serviceType) {
            document.querySelector('.calculation-results').style.display = 'none';
            document.getElementById('proceedToPay').disabled = true;
            return;
        }

        const propertyResponse = await fetch(`handler/get_property_details.php?id=${propertyId}`);
        const propertyText = await propertyResponse.text();
        
        if (propertyText.startsWith('<') || propertyText.includes('<br')) {
            throw new Error('Server returned invalid response');
        }
        
        const property = JSON.parse(propertyText);

        const serviceResponse = await fetch(`handler/get_service_fee.php?service=${serviceType}`);
        const serviceText = await serviceResponse.text();
        
        if (serviceText.startsWith('<') || serviceText.includes('<br')) {
            throw new Error('Service data format invalid');
        }
        
        const service = JSON.parse(serviceText);

        const fee = service.fixed_fee > 0 
            ? service.fixed_fee 
            : property.plot_size * service.price_per_sqm;

        document.querySelector('.calculation-results').style.display = 'block';
        document.getElementById('plotSizeDisplay').value = `${property.plot_size} sqm`;
        document.getElementById('serviceFee').value = `₦${fee.toLocaleString()}`;
        document.getElementById('proceedToPay').disabled = false;

    } catch (error) {
        Swal.fire('Error', `Application failed: ${error.message}`, 'error');
        console.error('Fee calculation error:', error);
    }
});

document.getElementById('proceedToPay').addEventListener('click', function() {
    const propertyId = document.getElementById('propertyId').value;
    const serviceType = document.getElementById('serviceType').value;
    const amount = document.getElementById('serviceFee').value.replace('₦', '').replace(/,/g, '');
    
    // Initialize Paystack payment
    let handler = PaystackPop.setup({
        key: 'pk_test_e3d42791d57cebebbfae8dad035350fef02c0f8e', // Replace with your Paystack public key
        email: '<?php echo $user["email"]; ?>', // Gets email from PHP user data
        amount: parseFloat(amount) * 100, // Convert to kobo
        currency: 'NGN',
        ref: 'APP_'+Math.floor((Math.random() * 1000000000) + 1),
        callback: function(response) {
            // Handle successful payment
            submitApplication({
                propertyId: propertyId,
                serviceType: serviceType,
                amount: amount,
                reference: response.reference
            });
        },
        onClose: function() {
            // Handle popup closure
            alert('Transaction was not completed, window closed.');
        }
    });
    handler.openIframe();
});

function submitApplication(data) {
    fetch('handler/submit_application.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Create notification for admin
            createAdminNotification({
                serviceType: data.serviceType,
                reference: data.reference
            });
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your application has been submitted successfully.',
                timer: 2000
            }).then(() => {
                location.reload();
            });
        } else {
            throw new Error(result.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message
        });
    });
}

function createAdminNotification(data) {
    fetch('handler/create_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    });
}

</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update Profile Form Handler
    document.getElementById('updateProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('handler/update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000
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

    // Change Password Form Handler
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('handler/change_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000
                }).then(() => {
                    this.reset();
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

function loadMessages() {
    const messagesContainer = document.querySelector('.messages-container');
    // Modify the message rendering template in loadMessages() function
    messagesContainer.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    // Set a timeout of 10 seconds
    const timeoutDuration = 10000; // 10 seconds
    const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Request timed out')), timeoutDuration);
    });

    // Fetch messages with timeout
    Promise.race([
        fetch('handler/fetch_messages.php', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        }),
        timeoutPromise
    ])
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Raw response:', text);
                throw new Error('Invalid JSON response');
            }
        });
    })
    .then(data => {
        if (data.success) {
            if (data.messages.length === 0) {
                messagesContainer.innerHTML = '<div class="alert alert-info">No messages found.</div>';
                return;
            }

            // Modify the message rendering template in loadMessages() function
            messagesContainer.innerHTML = data.messages.map(message => `
                <div class="message-card mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="sender-avatar">
                                        ${message.sender_name.charAt(0)}
                                    </div>
                                    <div>
                                        <h6 class="card-subtitle">
                                            ${message.sender_name}
                                        </h6>
                                        <small class="text-muted">
                                            ${message.created_at}
                                        </small>
                                    </div>
                                </div>
                                ${message.application_reference ? `
                                    <div class="application-info">
                                        <small class="text-primary">
                                            <i class="fas fa-file-alt me-1"></i>
                                            Application ${message.application_reference}
                                            <span class="ms-1 fw-bold">${message.service_type}</span>
                                        </small>
                                    </div>
                                ` : ''}
                            </div>
                            <p class="card-text">${message.message}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            throw new Error(data.message || 'Failed to load messages');
        }
    })
    .catch(error => {
        console.error('Error loading messages:', error);
        messagesContainer.innerHTML = `
            <div class="alert alert-danger">
                <p>${error.message === 'Request timed out' ? 
                    'Request took too long. Please try again.' : 
                    'Failed to load messages.'}</p>
                <button class="btn btn-primary mt-2" onclick="loadMessages()">
                    <i class="fas fa-sync-alt me-2"></i> Retry
                </button>
            </div>
        `;
    });
}

// Add event listener for messages tab
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function() {
        if (this.getAttribute('data-content') === 'messages-content') {
            loadMessages();
        }
    });
});

<?php include 'includes/footer.php'; ?>