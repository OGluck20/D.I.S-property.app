<?php
include 'includes/db.php';
include 'includes/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch properties, gadgets, and solar installations from the database
$queryProperties = "SELECT * FROM properties WHERE status='available'";
$stmtProperties = $conn->prepare($queryProperties);
$stmtProperties->execute();

$queryGadgets = "SELECT * FROM devices";
$stmtGadgets = $conn->prepare($queryGadgets);
$stmtGadgets->execute();

$querySolar = "SELECT * FROM solar_installations";
$stmtSolar = $conn->prepare($querySolar);
$stmtSolar->execute();

// Fetch farms from the database
$queryFarms = "SELECT * FROM farms"; // Adjust the table name as necessary
$stmtFarms = $conn->prepare($queryFarms);
$stmtFarms->execute();

// Get the selected tab from the URL, default to 'gadgets'
$selected_tab = isset($_GET['tab']) ? $_GET['tab'] : 'gadgets';
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
    .container {
        padding: 30px 20px;
        height: 85vh; /* Fixed height */
        overflow-y: auto; /* Enable vertical scrolling */
        scrollbar-width: none; /* Hide scrollbar for Firefox */
    }

    /* Hide scrollbar for WebKit browsers */
    .container::-webkit-scrollbar {
        display: none;
    }

    .nav-tabs {
        margin-bottom: 20px;
        flex-wrap: wrap; /* Allow tabs to wrap on smaller screens */
        justify-content: center; /* Center tabs */
    }

    .nav-tabs .nav-link {
        color: #4CAF50; /* Green color */
        padding: 10px 15px; /* Increased padding for better touch targets */
        margin: 5px; /* Margin between tabs */
        border-radius: 5px; /* Rounded corners */
        transition: background-color 0.3s; /* Smooth background transition */
    }

    .nav-tabs .nav-link.active {
        background-color: #4CAF50; /* Active tab color */
        color: white; /* Active tab text color */
    }

    .nav-tabs .nav-link:hover {
        background-color: rgba(76, 175, 80, 0.7); /* Lighten on hover */
        color: white; /* White text on hover */
    }

    .property-card {
        border: 1px solid #ddd;
        border-radius: 5px;
        overflow: hidden;
        transition: transform 0.2s;
        position: relative; /* For positioning price tag */
    }

    .property-card:hover {
        transform: scale(1.02); /* Slight scale effect on hover */
    }

    .media-preview {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.2s; /* Smooth transition for image */
    }

    .property-card:hover .media-preview {
        transform: scale(1.05); /* Pinch effect on image hover */
    }

    .card-body {
        padding: 15px;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: bold;
    }

    .card-text {
        margin: 10px 0;
    }

    .price-tag {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #4CAF50; /* Green background */
        color: white; /* White text */
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
    }

    .timestamp, .location {
        font-size: 0.9rem; /* Smaller font size */
        color: gray; /* Color for timestamp and location */
    }

    .btn {
        margin-right: 5px;
        transition: background-color 0.3s, transform 0.2s; /* Smooth transition for buttons */
    }

    .btn-primary {
        background-color: #007bff; /* Bootstrap primary color */
        border: none; /* Remove border */
    }

    .btn-primary:hover {
        background-color: #0056b3; /* Darker blue on hover */
        transform: scale(1.05); /* Slightly enlarge on hover */
    }

    .btn-success {
        background-color: #28a745; /* Bootstrap success color */
        border: none; /* Remove border */
    }

    .btn-success:hover {
        background-color: #218838; /* Darker green on hover */
        transform: scale(1.05); /* Slightly enlarge on hover */
    }

    /* Property Section Styles */
    .section-title {
        font-size: 2rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1.5rem;
    }

    /* Search Filter Styles */
    .search-filter {
        position: relative;
    }

    .search-filter input {
        padding: 12px 20px;
        padding-right: 40px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }

    .search-filter input:focus {
        border-color: #4CAF50;
        box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
    }

    .search-filter .search-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    /* Property Card Styles */
    .property-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .property-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .media-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .property-card:hover .media-preview {
        transform: scale(1.1);
    }

    /* Property Tags Styles */
    .property-tags {
        position: absolute;
        top: 15px;
        width: 100%;
        padding: 0 15px;
        display: flex;
        justify-content: space-between; /* Spreads the tags apart */
        align-items: flex-start;
    }

    .status-tag, .price-tag {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .status-tag {
        background-color: #4CAF50;
        color: white;
        margin-right: auto; /* Pushes the tag to the left */
    }

    .price-tag {
        background-color: rgba(255, 255, 255, 0.9);
        color: #2c3e50;
        margin-left: auto; /* Pushes the tag to the right */
    }

    .property-details {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .property-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .property-info {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 0.875rem;
        color: #6c757d;
    }

    .property-info i {
        margin-right: 5px;
    }

    .property-description {
        color: #6c757d;
        margin-bottom: 20px;
        flex-grow: 1;
    }

    .property-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .property-description {
        color: #666;
        margin: 10px 0;
        line-height: 1.4;
    }

    .btn i {
        margin-right: 5px;
    }

    .property-actions .btn {
        flex: 1;
        padding: 8px 15px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-outline-primary {
        color: #4CAF50;
        border-color: #4CAF50;
    }

    .btn-outline-primary:hover {
        background-color: #4CAF50;
        border-color: #4CAF50;
        color: white;
    }

    .btn-success {
        background-color: #4CAF50;
        border-color: #4CAF50;
    }

    .btn-success:hover {
        background-color: #45a049;
        border-color: #45a049;
    }

    /* No Properties Message */
    .no-properties {
        text-align: center;
        padding: 40px;
        background-color: #f8f9fa;
        border-radius: 12px;
        color: #6c757d;
    }

    .no-properties i {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    .no-properties p {
        font-size: 1.1rem;
        margin: 0;
    }

    .gadgets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        padding: 1.5rem 0;
    }

    .no-results {
    text-align: center;
    padding: 1rem;
    margin-top: -40vh;
    margin-bottom: 40vh;
    font-size: 1.2rem;
    color: #6c757d;
    width: 100%;
    }

    .gadget-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid var(--shadow);
    }


    .gadget-card.hidden {
        display: none;
    }

    .gadget-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .gadget-image {
        height: 200px;
        background: #f8f9fa;
        position: relative;
        overflow: hidden;
    }

    .gadget-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .gadget-category {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(255,255,255,0.9);
        padding: 0.5rem;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
    }

    .gadget-content {
        padding: 1.5rem;
    }

    .gadget-name {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--text);
    }

    .gadget-specs {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: #666;
    }

    .gadget-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 1rem;
    }

    .gadget-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        flex: 1;
        padding: 0.75rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-details {
        background: var(--primary);
        color: white;
    }

    .btn-details:hover {
        background: var(--primary-dark);
    }

    .btn-cart {
        background: var(--background);
        color: var(--text);
        border: 2px solid var(--primary);
    }

    .btn-cart:hover {
        background: var(--primary);
        color: white;
    }

    .brand-filter {
        overflow-x: auto;
        white-space: nowrap;
        padding: 1rem 0;
        margin-bottom: 2rem;
        -webkit-overflow-scrolling: touch;
    }

    .brand-list {
        display: inline-flex;
        gap: 1rem;
        padding: 0.5rem;
    }

    .brand-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        border: 2px solid var(--shadow);
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .brand-item:hover, 
    .brand-item.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .brand-item i {
        font-size: 1.2rem;
    }

    /* Hide scrollbar */
    .brand-filter::-webkit-scrollbar {
        display: none;
    }

    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .main-container {
        flex: 1;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .tab-content {
        height: 100%;
    }

    .gadgets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        padding: 1.5rem 0;
        min-height: calc(100vh - 300px); /* Adjust based on header/footer height */
    }

    /* Ensure footer stays at bottom */
    footer {
        margin-top: auto;
    }

    .solutions-container {
        margin-top: 3rem;
    }

    .solution-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 6px var(--shadow);
        transition: transform 0.3s ease;
    }

    .solution-card:hover {
        transform: translateY(-5px);
    }

    .solution-image {
        height: 200px;
        overflow: hidden;
    }

    .solution-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .solution-content {
        padding: 1.5rem;
    }

    .solution-type {
        background: var(--primary);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        display: inline-block;
        margin-bottom: 1rem;
    }

    .solution-title {
        font-size: 1.25rem;
        margin-bottom: 1rem;
        color: var(--text);
    }

    .solution-price {
        font-size: 1.5rem;
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .btn-whatsapp {
        background: #25D366;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-whatsapp:hover {
        background: #128C7E;
        color: white;
    }

    .btn-whatsapp-buy {
        background: #25D366;
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-whatsapp-buy:hover {
        background: #128C7E;
        color: white;
        transform: translateY(-2px);
    }

    .book-visit-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #25D366;
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    text-decoration: none;
    transition: background 0.3s ease;
    }

    .book-visit-btn:hover {
        background: #128C7E;
        color: white;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="main-container">
    <div class="tab-content">
        <?php if ($selected_tab === 'solutions'): ?>
            <div class="tab-pane fade show active" id="gadgets">
                <h2>Available Gadgets</h2>
                
                <!-- Brand Filter -->
                <div class="brand-filter">
                    <div class="brand-list">
                    <div class="brand-item active">
                            <span>All</span>
                        </div>
                        <div class="brand-item" data-brand="Apple">
                            <i class="fab fa-apple"></i>
                            <span>Apple</span>
                        </div>
                        <div class="brand-item" data-brand="Samsung">
                            <i class="fab fa-samsung"></i>
                            <span>Samsung</span>
                        </div>
                        <div class="brand-item" data-brand="Google">
                            <i class="fab fa-android"></i>
                            <span>Google</span>
                        </div>
                        <div class="brand-item" data-brand="OnePlus">
                            <i class="fas fa-mobile-alt"></i>
                            <span>OnePlus</span>
                        </div>
                        <div class="brand-item" data-brand="Xiaomi">
                            <i class="fas fa-mobile"></i>
                            <span>Xiaomi</span>
                        </div>
                        <div class="brand-item" data-brand="Huawei">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Huawei</span>
                        </div>
                        <div class="brand-item" data-brand="Tecno">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Tecno</span>
                        </div>
                        <div class="brand-item" data-brand="HP">
                            <i class="fas fa-laptop"></i>
                            <span>HP</span>
                        </div>
                        <div class="brand-item" data-brand="Dell">
                            <i class="fas fa-laptop"></i>
                            <span>Dell</span>
                        </div>
                        <div class="brand-item" data-brand="Asus">
                            <i class="fas fa-laptop"></i>
                            <span>Asus</span>
                        </div>
                    </div>
                </div>

                <!-- Existing gadgets grid -->
                <div class="gadgets-grid">
                    <?php if ($stmtGadgets->rowCount() > 0): ?>
                        <?php while ($gadget = $stmtGadgets->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="gadget-card">
                                <div class="gadget-image">
                                    <img src="uploads/devices/<?php echo htmlspecialchars($gadget['media']); ?>" 
                                         alt="<?php echo htmlspecialchars($gadget['name']); ?>">
                                </div>
                                <div class="gadget-content">
                                    <h3 class="gadget-name"><?php echo htmlspecialchars($gadget['name']); ?></h3>
                                    <div class="gadget-specs">
                                    <span>
                                        <?php 
                                        $brandIcon = 'fa-mobile-alt';
                                        switch(strtolower($gadget['brand'])) {
                                            case 'apple':
                                                $brandIcon = 'fab fa-apple';
                                                break;
                                            case 'samsung':
                                                $brandIcon = 'fa fa-mobile-alt';
                                                break;
                                            case 'google':
                                                $brandIcon = 'fab fa-google';
                                                break;
                                            case 'oneplus':
                                                $brandIcon = 'fa fa-mobile-alt';
                                                break;
                                            case 'xiaomi':
                                                $brandIcon = 'fa fa-mobile-alt';
                                                break;
                                            case 'huawei':
                                                $brandIcon = 'fa fa-mobile-alt';
                                                break;
                                            case 'HP':
                                                $brandIcon = 'fas fa-laptop';
                                                break;
                                            case 'dell':
                                                $brandIcon = 'fas fa-laptop';
                                                break;
                                            case 'asus':
                                                $brandIcon = 'fas fa-laptop';
                                                break;
                                        }
                                        ?>
                                        <i class="<?php echo $brandIcon; ?>"></i>
                                        <?php echo htmlspecialchars($gadget['brand'] ?? 'N/A'); ?>
                                    </span>                                        <span><i class="fas fa-memory"></i> <?php echo htmlspecialchars($gadget['ram'] ?? 'N/A'); ?> RAM</span>
                                        <span><i class="fas fa-hdd"></i> <?php echo htmlspecialchars($gadget['storage'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="gadget-price">
                                        ₦<?php echo number_format($gadget['price'], 2); ?>
                                    </div>
                                    <div class="gadget-actions">
                                        <!-- <button class="btn-action btn-details">View Details</button> -->
                                        <a href="https://wa.me/+2347060592446?text=I'm%20interested%20in%20buying%20<?php echo urlencode($gadget['name']); ?>%20for%20₦<?php echo number_format($gadget['price'], 2); ?>" 
                                           class="btn-whatsapp-buy" 
                                           target="_blank">
                                            <i class="fab fa-whatsapp"></i> Buy Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-gadgets">
                            <p>No gadgets available at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="no-results" style="display: none;">
                    <p>No gadgets available for <span class="selected-brand"></span></p>
                </div>

                <h2 style="margin-top: 2rem; text-align: center;">Available Solutions</h2>

                <!-- Solutions Section -->
                <div class="solutions-container">
                    <div class="row">
                        <!-- Solar Solutions -->
                        <div class="col-lg-6 mb-4">
                            <div class="solution-card">
                                <div class="solution-image">
                                    <img src="assets\images\ajao_480x480.jpg" alt="Solar Installation">
                                </div>
                                <div class="solution-content">
                                    <span class="solution-type">Solar Installation</span>
                                    <h3 class="solution-title">Professional Solar System Installation</h3>
                                    <p>Complete solar power solutions for homes and businesses. Includes panels, inverters, and batteries.</p>
                                    <div class="solution-price">Starting from ₦500,000</div>
                                    <a href="https://wa.me/+2347060592446?text=I'm%20interested%20in%20solar%20installation" 
                                       class="btn-whatsapp" target="_blank">
                                        <i class="fab fa-whatsapp"></i> Consult on WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- CCTV Solutions -->
                        <div class="col-lg-6 mb-4">
                            <div class="solution-card">
                                <div class="solution-image">
                                    <img src="assets\images\CCTV-Camera-Installation-4cd77782f3a8decda2dc4b64a6390333.jpeg" alt="CCTV Installation">
                                </div>
                                <div class="solution-content">
                                    <span class="solution-type">CCTV Installation</span>
                                    <h3 class="solution-title">Security Camera Systems</h3>
                                    <p>Advanced CCTV surveillance systems for maximum security. Includes cameras, DVR, and mobile monitoring.</p>
                                    <div class="solution-price">Starting from ₦250,000</div>
                                    <a href="https://wa.me/+2347060592446?text=I'm%20interested%20in%20CCTV%20installation" 
                                       class="btn-whatsapp" target="_blank">
                                        <i class="fab fa-whatsapp"></i> Consult on WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($selected_tab === 'properties'): ?>
            <div class="tab-pane fade show active" id="properties">
                <!-- Property Filters -->
                <div class="property-filters mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h2 class="section-title">Available Properties</h2>
                        </div>
                        <div class="col-md-6">
                            <div class="search-filter">
                                <input type="text" class="form-control" placeholder="Search properties...">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Grid -->
                <div class="row">
                    <?php if ($stmtProperties->rowCount() > 0): ?>
                        <?php while ($property = $stmtProperties->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="property-card">
                                    <div class="property-image">
                                        <?php if ($property['media']): ?>
                                            <img src="uploads/properties/<?php echo htmlspecialchars($property['media']); ?>" 
                                                 class="media-preview" 
                                                 alt="<?php echo htmlspecialchars($property['title']); ?>">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/350x200" class="media-preview" alt="No Media">
                                        <?php endif; ?>
                                        <div class="property-tags">
                                            <span class="status-tag">Available</span>
                                            <span class="price-tag">₦<?php echo number_format($property['price'], 2); ?></span>
                                        </div>
                                    </div>
                                    <div class="property-details">
                                        <h3 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h3>
                                        <div class="property-info">
                                            <span class="location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?>
                                            </span>
                                            <span class="timestamp">
                                                <i class="fas fa-clock"></i>
                                                <?php echo date('M d, Y', strtotime($property['created_at'])); ?>
                                            </span>
                                        </div>
                                        <p class="property-description">
                                            <?php 
                                            $description = htmlspecialchars($property['description']);
                                            echo (strlen($description) > 60) ? substr($description, 0, 60) . '...' : $description;
                                            ?>
                                        </p>
                                        <div class="property-actions">
                                            <a href="purchase.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                            <a href="purchase.php?id=<?php echo $property['id']; ?>" class="btn btn-success">
                                                <i class="fas fa-shopping-cart"></i> Purchase
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="no-properties">
                                <i class="fas fa-home"></i>
                                <p>No properties available at the moment.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($selected_tab === 'farms'): ?>
            <div class="tab-pane fade show active" id="farms">
                <h2>Available Livestock</h2>
                <div class="row">
                    <?php if ($stmtFarms->rowCount() > 0): ?>
                        <?php while ($farm = $stmtFarms->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card property-card">
                                    <img src="uploads/<?php echo htmlspecialchars($farm['media']); ?>" class="media-preview" alt="<?php echo htmlspecialchars($farm['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($farm['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($farm['description']); ?></p>
                                        <p class="card-text"><strong>Price:</strong> ₦<?php echo number_format($farm['price'], 2); ?></p>
                                        <?php
                                        $whatsapp_message = "Hi, I'm interested in booking a visit for the " . $farm['name'] . " listed at ₦" . number_format($farm['price'], 2);
                                        $whatsapp_url = "https://wa.me/+2348078123476?text=" . urlencode($whatsapp_message);
                                        ?>
                                        <a href="<?php echo $whatsapp_url; ?>" class="book-visit-btn" target="_blank">
                                            <i class="fab fa-whatsapp"></i> Book Visit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No livestock available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.brand-item');
    const gadgetCards = document.querySelectorAll('.gadget-card');
    const noResults = document.querySelector('.no-results');
    const selectedBrandSpan = document.querySelector('.selected-brand');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            const selectedBrand = button.getAttribute('data-brand') || 'all';
            let visibleCards = 0;
            
            gadgetCards.forEach(card => {
                if (selectedBrand === 'all') {
                    card.style.display = 'block';
                    visibleCards++;
                } else {
                    const cardBrand = card.querySelector('.gadget-specs span:first-child').textContent.trim();
                    if (cardBrand.includes(selectedBrand)) {
                        card.style.display = 'block';
                        visibleCards++;
                    } else {
                        card.style.display = 'none';
                    }
                }
            });

            // Show/hide no results message
            if (visibleCards === 0) {
                selectedBrandSpan.textContent = selectedBrand === 'all' ? 'any brand' : selectedBrand;
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        });
    });

    // Initial filter
    document.querySelector('.brand-item.active').click();
});
</script>

<?php include 'includes/footer.php'; ?>
