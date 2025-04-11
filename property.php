<?php
require_once 'includes/db.php';

// Get property ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch property details
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

// If property doesn't exist, redirect to home
if (!$property) {
    header("Location: index.php");
    exit();
}

include 'includes/header.php';
?>

<style>
/* Enhanced Property Details Page Styling */
:root {
    --primary: #2ecc71;
    --primary-dark: #27ae60;
    --primary-light: rgba(46, 204, 113, 0.1);
    --secondary: #34495e;
    --accent: #3498db;
    --accent-dark: #2980b9;
    --background: #f9fafb;
    --card-bg: #ffffff;
    --text: #2c3e50;
    --text-light: #7f8c8d;
    --shadow: rgba(0, 0, 0, 0.1);
    --border-radius: 16px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

/* Property Details Card */
.property-details-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: 0 10px 30px var(--shadow);
    margin-bottom: 3rem;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.property-image {
    position: relative;
    height: 500px;
    overflow: hidden;
    background: var(--background);
}

.property-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s ease;
}

.property-details-card:hover .property-image img {
    transform: scale(1.03);
}

.property-info {
    padding: 2.5rem;
}

/* Property Info Content */
.property-info h1 {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 1rem;
    line-height: 1.3;
}

.property-info .price {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary);
    margin: 1rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.property-info .price::before {
    content: "";
    display: inline-block;
    width: 6px;
    height: 24px;
    background-color: var(--primary);
    border-radius: 3px;
    margin-right: 10px;
}

.property-info .location {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.1rem;
    color: var(--text-light);
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.property-info .location i {
    color: var(--accent);
    font-size: 1.2rem;
}

.property-info .description {
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--text);
    margin: 1.5rem 0;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

/* CTA Buttons */
.cta-buttons {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 1rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition: var(--transition);
    cursor: pointer;
    text-decoration: none;
}

.btn-primary {
    background: var(--accent);
    color: white;
    border: none;
}

.btn-primary:hover {
    background: var(--accent-dark);
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
}

.btn-outline-primary {
    background: transparent;
    color: var(--accent);
    border: 2px solid var(--accent);
}

.btn-outline-primary:hover {
    background: var(--accent);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
}

/* Property Actions */
.property-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.property-actions .btn {
    flex: 1;
}

.share-property {
    transition: var(--transition);
}

.share-property:hover {
    transform: translateY(-3px);
}

/* Responsive Design */
@media (max-width: 992px) {
    .property-image {
        height: 400px;
    }
    
    .property-info h1 {
        font-size: 1.8rem;
    }
    
    .property-info .price {
        font-size: 1.6rem;
    }
}

@media (max-width: 768px) {
    .property-info {
        padding: 1.75rem;
    }
    
    .property-image {
        height: 350px;
    }
    
    .property-info h1 {
        font-size: 1.6rem;
    }
    
    .property-info .price {
        font-size: 1.4rem;
    }
    
    .cta-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .property-image {
        height: 250px;
    }
    
    .property-info {
        padding: 1.25rem;
    }
    
    .property-info h1 {
        font-size: 1.4rem;
    }
    
    .property-info .description {
        font-size: 1rem;
        line-height: 1.6;
    }
}

/* Add a badge for property status */
.property-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: var(--primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    z-index: 1;
}

/* Image gallery navigation (for future enhancement) */
.gallery-nav {
    position: absolute;
    bottom: 20px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.gallery-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: var(--transition);
}

.gallery-dot.active {
    background: white;
    transform: scale(1.2);
}
</style>

<div class="container mt-4 mb-4">
    <div class="property-details-card">
        <div class="property-image">
            <?php if ($property['media']): ?>
                <img src="uploads/properties/<?php echo htmlspecialchars($property['media']); ?>" 
                     alt="<?php echo htmlspecialchars($property['title']); ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/800x400" alt="No Image Available">
            <?php endif; ?>
        </div>
        <div class="property-info">
            <h1><?php echo htmlspecialchars($property['title']); ?></h1>
            <div class="price">₦<?php echo number_format($property['price'], 2); ?></div>
            <div class="location">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?>
            </div>
            <p class="description"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="cta-buttons">
                    <a href="login.php" class="btn btn-primary">Login to View More Details</a>
                    <a href="register.php" class="btn btn-outline-primary">Register Now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<?php include 'includes/whatsapp_float.php'; ?>
<?php include 'includes/footer.php'; ?>