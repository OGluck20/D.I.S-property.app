<?php
include 'includes/db.php';
include 'includes/header.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $property_id = intval($_GET['id']);

    // Fetch the property details by ID
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bindParam(1, $property_id, PDO::PARAM_INT);
    $stmt->execute();
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($property) {
        echo "
        <div class='property-detail' style='padding: 20px; height:80vh;'>
            <img src='uploads/{$property['image']}' alt='{$property['title']}' style='height: 40vh; width: 100%;'>
            <h2>{$property['title']}</h2>
            <p><strong>Description:</strong> {$property['description']}</p>
            <p><strong>Price:</strong> $".number_format($property['price'], 2)."</p>
            <p><strong>Address:</strong> {$property['address']}, {$property['city']}, {$property['state']}, {$property['zip_code']}</p>
        </div>";
    } else {
        echo "<p>Property not found.</p>";
    }
} else {
    echo "<p>Invalid property ID.</p>";
}

include 'includes/footer.php';
?>
