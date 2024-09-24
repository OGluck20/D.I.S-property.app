<?php
include 'includes/db.php';
include 'includes/header.php';

// Fetch properties from the database
$query = "SELECT * FROM properties WHERE status='available'";
$stmt = $conn->prepare($query);
$stmt->execute();
?>
<style>
    .timestamp {
    position: absolute;
    top: 10px; /* Adjust as needed */
    right: 10px; /* Adjust as needed */
    font-size: 12px; /* Smaller font size */
    color: gray; /* Change color as desired */
    background: rgba(255, 255, 255, 0.8); /* Optional: Background to make it readable */
    padding: 5px; /* Optional: Padding for better appearance */
    border-radius: 5px; /* Optional: Rounded corners */
}
.property-card {
    position: relative; /* Needed for absolute positioning of timestamp */
}

    .container{
        height: 81vh;
        font-size: 20px;
        overflow-y: scroll;
        margin: 5% auto;
    }

    .empty-property{
        margin-top: 25%;
        text-align: center;
    }

    .card-img-top{
        width: inherit;
        height: 200px; 
        object-fit: cover;
    }

    @media (max-width: 480px) {
        .card-img-top{
            width: inherit;
            height: 200px; 
            object-fit: cover;
        }
    }
</style>
<div class="container">
    <h1 class="my-4">Available Properties</h1>
    <div class="row">
        <?php if($stmt->rowCount() > 0): ?>
            <?php while($property = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                <div class="card property-card">
                <?php if($property['image']): ?>
                <img src="uploads/<?php echo htmlspecialchars($property['image']); ?>" 
                    class="card-img-top" 
                    alt="<?php echo htmlspecialchars($property['title']); ?>">
                <?php else: ?>
                <img src="https://via.placeholder.com/350x200" class="card-img-top" alt="No Image">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($property['description']); ?></p>
                        <p class="card-text"><strong>Price:</strong> ₦<?php echo number_format($property['price'], 2); ?></p>
                        <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                        <a href="purchase.php?id=<?php echo $property['id']; ?>" class="btn btn-success">Purchase</a> <!-- Purchase Button -->

                        <!-- Display the timestamp when the property was added -->
                        <div class="timestamp">
                            Added on: <?php echo date('Y-m-d H:i:s', strtotime($property['created_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class='empty-property'>No properties available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
