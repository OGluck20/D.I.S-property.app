<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Buffer output to prevent "headers already sent" errors
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="paystack-key" content="pk_test_e3d42791d57cebebbfae8dad035350fef02c0f8e">
    <meta name="theme-color" content="#27ae60">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="favicon_io//apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="512x512" href="favicon_io//favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <title>DIS Groups</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
    <!-- Add SweetAlert2 CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
{
        "name": "DIS Groups",
        "short_name": "DIS",
        "icons": [
            {
                "src": "favicon/android-chrome-192x192.png",
                "sizes": "192x192",
                "type": "image/png",
                "purpose": "any maskable"
            },
            {
                "src": "favicon/android-chrome-512x512.png",
                "sizes": "512x512",
                "type": "image/png",
                "purpose": "any maskable"
            },
            {
                "src": "favicon/apple-touch-icon.png",
                "sizes": "180x180",
                "type": "image/png"
            }
        ],
        "theme_color": "#27ae60",
        "background_color": "#ffffff",
        "display": "standalone",
        "start_url": "/"
    }
        :root {
            --header-bg: #e8f5e9;
        }

        .container-fluid {
            background: var(--header-bg);
            margin-top: -8px;
            margin-bottom: -8px;
            padding: 10px 5px;
        }
        
        .navbar-brand {
            font-size: 40px;
            margin-left: 10px;
            color:  #27ae60; /* White text for contrast */
        }
       
        .navbar-brand img {
            height: 200px;
            width: auto;
            transition: transform 0.3s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.05);
        }

        .navbar-nav .nav-link {
            color: #27ae60 !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #2ecc71 !important;
            background-color: rgba(46, 204, 113, 0.1);
            border-bottom: 2px solid #2ecc71;
        }

        .nav-link i {
            margin-right: 0.5rem;
        }

        @media (max-width: 991.98px) {
            .navbar-brand img {
                height: 50px;
            }
        }
        .navbar-toggler{
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <!-- Replace text with SVG -->
                <img src="favicon_io/DIS Logo.svg" alt="D.I.S Logo" style="height: 80px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php 
                        $current_tab = $_GET['tab'] ?? 'dashboard';
                        
                        // Dynamic navigation based on active tab
                        switch($current_tab) {
                            case 'solutions':
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="client_dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <?php
                                break;
                                
                            case 'properties':
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="client_dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="add_property.php">
                                        <i class="fas fa-plus-circle"></i> Add Property
                                    </a>
                                </li>
                                <?php
                                break;
                                
                            case 'farms':
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="client_dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <?php
                                break;
                                
                            default:
                                ?>
                                <?php
                        }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
<?php
// Flush output buffer at the end of the header
ob_end_flush();
?>
</body>
</html>
