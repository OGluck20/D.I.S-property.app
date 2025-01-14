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

$queryGadgets = "SELECT * FROM gadgets";
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
        margin-top: auto;
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
</style>


<div class="container">

    <!-- Tab Content -->
    <div class="tab-content">
        <?php if ($selected_tab === 'solutions'): ?>
            <div class="tab-pane fade show active" id="gadgets">
                <h2>Available Gadgets</h2>
                <div class="row">
                    <?php if ($stmtGadgets->rowCount() > 0): ?>
                        <?php while ($gadget = $stmtGadgets->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card property-card">
                                    <img src="uploads/<?php echo htmlspecialchars($gadget['media']); ?>" class="media-preview" alt="<?php echo htmlspecialchars($gadget['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($gadget['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($gadget['description']); ?></p>
                                        <p class="card-text"><strong>Price:</strong> ₦<?php echo number_format($gadget['price'], 2); ?></p>
                                        <a href="buy.php?id=<?php echo $gadget['id']; ?>" class="btn btn-success">Buy</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No gadgets available at the moment.</p>
                    <?php endif; ?>
                </div>

                <h2>Available Solar Installations</h2>
                <div class="row">
                    <?php if ($stmtSolar->rowCount() > 0): ?>
                        <?php while ($solar = $stmtSolar->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card property-card">
                                    <img src="uploads/<?php echo htmlspecialchars($solar['media']); ?>" class="media-preview" alt="<?php echo htmlspecialchars($solar['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($solar['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($solar['description']); ?></p>
                                        <p class="card-text"><strong>Price:</strong> ₦<?php echo number_format($solar['price'], 2); ?></p>
                                        <a href="book_consultation.php?id=<?php echo $solar['id']; ?>" class="btn btn-primary">Book Consultation</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No solar installations available at the moment.</p>
                    <?php endif; ?>
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
                                            <img src="uploads/<?php echo htmlspecialchars($property['media']); ?>" 
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
                                        <p class="property-description"><?php echo htmlspecialchars($property['description']); ?></p>
                                        <div class="property-actions">
                                            <a href="purchase.php?id=<?php echo $property['id']; ?>" class="btn btn-success">Purchase</a>
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
                                        <a href="book_farm_visit.php?id=<?php echo $farm['id']; ?>" class="btn btn-primary">Book Visit</a>
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
    const categoryTags = document.querySelectorAll('.category-tag');
    const gadgetItems = document.querySelectorAll('.gadget-item');

    categoryTags.forEach(tag => {
        tag.addEventListener('click', () => {
            // Remove active class from all tags
            categoryTags.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tag
            tag.classList.add('active');

            const selectedCategory = tag.dataset.category;

            gadgetItems.forEach(item => {
                if (selectedCategory === 'all' || item.dataset.category === selectedCategory) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
