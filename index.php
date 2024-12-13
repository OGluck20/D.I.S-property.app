<?php
include 'includes/db.php';
include 'includes/header.php';

// Fetch properties from the database
$queryProperties = "SELECT * FROM properties WHERE status='available'";
$stmtProperties = $conn->prepare($queryProperties);
$stmtProperties->execute();

// Fetch gadgets from the database
$queryGadgets = "SELECT * FROM gadgets";
$stmtGadgets = $conn->prepare($queryGadgets);
$stmtGadgets->execute();

// Fetch solar installations from the database
$querySolar = "SELECT * FROM solar_installations";
$stmtSolar = $conn->prepare($querySolar);
$stmtSolar->execute();
?>

<style>
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

    .search-bar {
        margin-bottom: 20px;
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
</style>

<div class="container">

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" href="#solutions" data-bs-toggle="tab">DIS Solutions</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#properties" data-bs-toggle="tab">DIS Properties</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#farms" data-bs-toggle="tab">DIS Farms</a>
        </li>
    </ul>

    <!-- Search Functionality -->
    <div class="search-bar">
        <form action="index.php" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search properties...">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="solutions">
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

        <div class="tab-pane fade" id="properties">
            <h2>Available Properties</h2>
            <div class="row">
                <?php if ($stmtProperties->rowCount() > 0): ?>
                    <?php while ($property = $stmtProperties->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card property-card">
                                <?php if ($property['media']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($property['media']); ?>" 
                                         class="media-preview" 
                                         alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/350x200" class="media-preview" alt="No Media">
                                <?php endif; ?>
                                <div class="price-tag">₦<?php echo number_format($property['price'], 2); ?></div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($property['description']); ?></p>
                                    <p class="card-text location">
                                        <i class="fas fa-map-marker-alt"></i> <!-- Font Awesome icon for location -->
                                        <?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?>
                                    </p>
                                    <p class="card-text timestamp">
                                        <i class="fas fa-clock"></i> <!-- Font Awesome icon for timestamp -->
                                        Added on: <?php echo date('Y-m-d H:i:s', strtotime($property['created_at'])); ?>
                                    </p>
                                    <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                                    <a href="purchase.php?id=<?php echo $property['id']; ?>" class="btn btn-success">Purchase</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No properties available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="farms">
            <h2>Available Livestock</h2>
            <div class="row">
                <?php
                // Fetch farms from the database
                $queryFarms = "SELECT * FROM farms"; // Adjust the table name as necessary
                $stmtFarms = $conn->prepare($queryFarms);
                $stmtFarms->execute();

                if ($stmtFarms->rowCount() > 0):
                    while ($farm = $stmtFarms->fetch(PDO::FETCH_ASSOC)): ?>
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
                    <?php endwhile; 
                else: ?>
                    <p>No livestock available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
