<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIS Groups</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <style>
        :root {
            --header-bg: #e8f5e9; /* Very light green background */
        }
        .container-fluid {
            background: var(--header-bg);
            margin-top: -8px;
            padding: 10px 5px;
        }

        .navbar-brand {
            font-size: 40px;
            margin-left: 20px;
            color:  #27ae60; /* White text for contrast */
        }

        .navbar-nav .nav-link {
            color:  #27ae60; /* White text for links */
        }

        .navbar-nav .nav-link:hover {
            color:  #2ecc71; /* Light gray on hover */
            border-bottom: 2px solid  #2ecc71; /* Green bottom border */
        }
    </style>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">D.I.S</a>
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
                                    <a class="nav-link" href="client_dashboard.php">Dashboard</a>
                                </li>
                                <?php
                                break;
                                
                            case 'properties':
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="client_dashboard.php">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="add_property.php">Add Property</a>
                                </li>
                                <?php
                                break;
                                
                            case 'farms':
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="client_dashboard.php">Dashboard</a>
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
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>
