<?php
include 'includes/db.php';
include 'includes/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    --gradient: linear-gradient(135deg, var(--primary), var(--accent));
}
/* Hero Section Styles */
.hero {
    width: 100%;
    height: 100vh;
    background: var(--background);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('path/to/pattern.svg');
    opacity: 0.1;
    z-index: 1;
}

/* Update Hero Content text color */
.hero-content {
    text-align: center;
    color: var(--primary-dark); /* Changed from white to primary-dark */
    margin-bottom: 3rem;
    z-index: 2;
}

/* Update title color */
.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px var(--shadow);
    color: var(--primary); /* Added primary color */
}

/* Update subtitle color */
.hero-subtitle {
    font-size: 1.5rem;
    opacity: 0.9;
    margin-bottom: 2rem;
    color: var(--primary-dark); /* Added primary-dark color */
}

/* Navigation Tabs Styles */
.nav-tabs {
    background: var(--background);
    padding: 1rem;
    border-radius: 15px;
    border: none;
    display: flex;
    gap: 1rem;
    box-shadow: 0 4px 6px var(--shadow);
}

.nav-tabs .nav-item {
    margin: 0;
}

.nav-tabs .nav-link {
    color: var(--text);
    padding: 1rem 2rem;
    border: 2px solid var(--primary);
    border-radius: 10px;
    transition: all 0.3s ease;
    font-weight: 500;
    background: transparent;
}

.nav-tabs .nav-link:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}

.nav-tabs .nav-link.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.nav-tabs .nav-link i {
    margin-right: 8px;
}

/* Floating Elements */
.floating-element {
    position: absolute;
    z-index: 1;
}

.floating-1 {
    top: 10%;
    left: 5%;
    animation: float 20s infinite;
}

.floating-2 {
    bottom: 15%;
    right: 10%;
    animation: float 16s infinite reverse;
}

.floating-3 {
    top: 40%;
    right: 15%;
    animation: float 18s infinite;
}

.floating-4 {
    bottom: 30%;
    left: 10%;
    animation: float 25s infinite reverse;
}

.floating-5 {
    top: 20%;
    right: 30%;
    animation: float 22s infinite;
}

.floating-6 {
    bottom: 10%;
    left: 40%;
    animation: float 16s infinite reverse;
}

.floating-element i {
    color: var(--primary);
    opacity: 0.3;  /* Increased from 0.15 for better visibility */
}

@keyframes float {
    0% { transform: translate(0, 0) rotate(0deg); }
    50% { transform: translate(20px, -20px) rotate(180deg); }
    100% { transform: translate(0, 0) rotate(360deg); }
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
    }
    
    .nav-tabs {
        flex-direction: column;
        width: 90%;
        gap: 0.5rem;
    }
    
    .nav-tabs .nav-link {
        width: 100%;
        text-align: center;
        padding: 0.75rem;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Hero Section with Navigation -->
<section class="hero">
    <!-- Floating Elements -->
    <div class="floating-element floating-1">
        <i class="fas fa-home fa-2x"></i>
    </div>
    <div class="floating-element floating-2">
        <i class="fas fa-leaf fa-2x"></i>
    </div>
    <div class="floating-element floating-3">
        <i class="fas fa-cogs fa-2x"></i>
    </div>
    <div class="floating-element floating-4">
        <i class="fas fa-desktop fa-2x"></i>
    </div>
    <div class="floating-element floating-5">
        <i class="fas fa-network-wired fa-2x"></i>
    </div>
    <div class="floating-element floating-6">
        <i class="fas fa-server fa-2x"></i>
    </div>

    <!-- Hero Content -->
    <div class="hero-content">
        <h1 class="hero-title">Welcome to DIS Group</h1>
        <p class="hero-subtitle">Innovative Solutions for Properties, Technology, and Agriculture</p>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php?tab=solutions' : 'login.php'; ?>">
                <i class="fas fa-tools"></i> DIS Solutions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php?tab=properties' : 'login.php'; ?>">
                <i class="fas fa-home"></i> DIS Properties
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php?tab=farms' : 'login.php'; ?>">
                <i class="fas fa-leaf"></i> DIS Farms
            </a>
        </li>
    </ul>
</section>

<?php include 'includes/footer.php'; ?>