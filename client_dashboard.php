<?php
include 'includes/header.php';
include 'includes/db.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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
}

@media (min-width: 769px) {
    .dashboard-wrapper {
        flex-direction: row;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Place this RIGHT AFTER THE HEADER -->
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
        </section>
    </div>
</div>

<div class="modal fade" id="applicationTypeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply for Services</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <button type="button" 
                            class="list-group-item list-group-item-action application-type"
                            data-type="C of O">
                        Certificate of Occupancy (C of O)
                    </button>
                    <button type="button" 
                            class="list-group-item list-group-item-action application-type"
                            data-type="Survey">
                        Land Survey
                    </button>
                    <button type="button" 
                            class="list-group-item list-group-item-action application-type"
                            data-type="BOQ">
                        Bill of Quantities (BOQ)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    const menuToggle = document.querySelector('.menu-toggle');
    btn.addEventListener('click', function() {

        menuToggle.style.display = 'none';
        const propertyId = this.dataset.propertyId;
        const propertyTitle = this.dataset.propertyTitle;
        const typeModal = new bootstrap.Modal(document.getElementById('applicationTypeModal'));
        
        // Store property info in modal
        typeModal._element.dataset.propertyId = propertyId;
        typeModal._element.dataset.propertyTitle = propertyTitle;
        typeModal.show();
    });
});

document.querySelectorAll('.application-type').forEach(btn => {
    btn.addEventListener('click', function() {
        const typeModal = bootstrap.Modal.getInstance(document.getElementById('applicationTypeModal'));
        const applicationType = this.dataset.type;
        const propertyId = typeModal._element.dataset.propertyId;
        const propertyTitle = typeModal._element.dataset.propertyTitle;
        
        Swal.fire({
            title: `Apply for ${applicationType}?`,
            html: `You're applying for <b>${applicationType}</b> for property:<br>
                  <i>"${propertyTitle}"</i>`,
            showCancelButton: true,
            confirmButtonText: 'Confirm Apply'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('handler/submit_application.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        property_id: propertyId,
                        application_type: applicationType
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Applied!', data.message, 'success');
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                });
            }
        });
        
        typeModal.hide();
    });
});
</script>

<?php include 'includes/footer.php'; ?> 