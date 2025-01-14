<?php
include 'includes/db.php';
include 'includes/header.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$property_id = intval($_GET['id']);
$errors = [];

// Fetch property and user data
$stmt = $conn->prepare("SELECT properties.*, users.username FROM properties JOIN users ON properties.user_id = users.id WHERE properties.id = ?");
$stmt->bindParam(1, $property_id, PDO::PARAM_INT);
$stmt->execute();
$property = $stmt->fetch(PDO::FETCH_ASSOC);

// Determine media type
$media_path = $property['media'];
$media_ext = strtolower(pathinfo($media_path, PATHINFO_EXTENSION));
$is_image = in_array($media_ext, ['jpg', 'jpeg', 'png', 'gif']);
$is_video = in_array($media_ext, ['mp4', 'webm', 'ogg']);

if (!$property) {
    echo "<div class='container' style='height: 75vh; bottom: 10px; right: 10px;'><h2>Property not found.</h2></div>";
    include 'includes/footer.php';
    exit();
}

if ($property['status'] !== 'available') {
    echo "<div class='container' style='height: 75vh; bottom: 10px; right: 10px;'><h2>This property has already been sold.</h2></div>";
    include 'includes/footer.php';
    exit();
}

// WhatsApp redirection
$admin_phone = '+2349035286982'; // Ensure the admin phone is set correctly

$whatsapp_message = "Property Inquiry:\n\n"
    . "Title: " . htmlspecialchars($property['title']) . "\n"
    . "Price: ₦" . number_format($property['price'], 2) . "\n"
    . "Description: " . htmlspecialchars($property['description']);

$whatsapp_message_encoded = urlencode($whatsapp_message);
$whatsapp_url = "https://wa.me/$admin_phone?text=$whatsapp_message_encoded";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = trim($_POST['purchase_code']);

    if ($entered_code === $property['purchase_code']) {
        $buyer_id = $_SESSION['user_id'];
        $seller_id = $property['user_id'];
        $amount = $property['price'];

        $stmt = $conn->prepare("INSERT INTO transactions (property_id, buyer_id, seller_id, amount) VALUES (?, ?, ?, ?)");
        $stmt->bindParam(1, $property_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $buyer_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $seller_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $amount, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Update property status to sold
            $update_stmt = $conn->prepare("UPDATE properties SET status='sold' WHERE id=?");
            $update_stmt->bindParam(1, $property_id, PDO::PARAM_INT);
            $update_stmt->execute();

            echo "<div class='container'><div class='alert alert-success'><h3>Purchase Successful!</h3><p>You have successfully purchased the property.</p></div></div>";
        } else {
            $errors[] = "Failed to process the purchase. Please try again.";
        }
    } else {
        $errors[] = "Invalid purchase code. Please try again.";
    }
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
}
    .container {
        padding: 30px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .purchase-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-top: 20px;
    }

    /* Product Details Section */
    .product-details {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .product-title {
        font-size: 1.8rem;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .media-container {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .media-container img,
    .media-container video {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 12px;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .media-container:hover img,
    .media-container:hover video {
        transform: scale(1.02);
    }

    /* Purchase Form Section */
    .purchase-form {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 20px;
    }

    .price-tag {
        font-size: 2rem;
        color: #2c3e50;
        font-weight: bold;
        margin-bottom: 20px;
        display: block;
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #4CAF50;
        box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
    }

    .btn {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-bottom: 10px;
    }

    .btn-success {
        background-color: #4CAF50;
        border: none;
    }

    .btn-success:hover {
        background-color: #45a049;
        transform: translateY(-2px);
    }

    .btn-whatsapp {
        background-color: #25D366;
        color: white;
        border: none;
    }

    .btn-whatsapp:hover {
        background-color: #128C7E;
        color: white;
        transform: translateY(-2px);
    }

    /* Property Description */
    .property-description {
        margin-top: 20px;
        color: #666;
        line-height: 1.6;
    }

    /* Error Messages */
    .alert {
        border-radius: 8px;
        margin-bottom: 20px;
    }

    /* Modal Styles */
    .modal {
        background-color: rgba(0, 0, 0, 0.9);
    }

    .modal-content {
        background: none;
        border: none;
    }

    .close {
        position: absolute;
        right: 20px;
        top: 20px;
        color: white;
        font-size: 30px;
        cursor: pointer;
        z-index: 1000;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .purchase-grid {
            grid-template-columns: 1fr;
        }

        .media-container img,
        .media-container video {
            height: 300px;
        }

        .purchase-form {
            position: relative;
            top: 0;
        }
    }

    .property-address {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .property-address h4 {
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .property-address p {
        color: #666;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .property-address i {
        color: #4CAF50;
        font-size: 1.2em;
    }

    .address-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .address-container p {
        flex: 1;
        margin: 0;
    }

    .map-link {
        color: #4CAF50;
        font-size: 1.2em;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .map-link:hover {
        background-color: rgba(76, 175, 80, 0.1);
        color: #45a049;
        transform: scale(1.1);
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<div class="container">
    <div class="purchase-grid">
        <!-- Product Details Section -->
        <div class="product-details">
            <h2 class="product-title"><?php echo htmlspecialchars($property['title']); ?></h2>
            
            <div class="media-container" onclick="openModal('uploads/<?php echo htmlspecialchars($property['media']); ?>', '<?php echo $media_ext; ?>')">
                <?php if ($is_image): ?>
                    <img src="uploads/<?php echo htmlspecialchars($property['media']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                <?php elseif ($is_video): ?>
                    <video controls>
                        <source src="uploads/<?php echo htmlspecialchars($property['media']); ?>" type="video/<?php echo $media_ext; ?>">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            </div>

            <div class="property-description">
                <h4>Description</h4>
                <p><?php echo htmlspecialchars($property['description']); ?></p>
                
                <div class="property-address">
                    <h4>Location Details</h4>
                    <div class="address-container">
                        <p>
                            <i class="fas fa-map-marker-alt"></i> 
                            <?php 
                                $location = array_filter([
                                    $property['address'],
                                    $property['city'],
                                    $property['state']
                                ]);
                                echo htmlspecialchars(implode(', ', $location)); 
                            ?>
                        </p>
                        <?php
                            $map_query = urlencode(implode(', ', $location));
                            $maps_url = "https://www.google.com/maps/search/?api=1&query=" . $map_query;
                        ?>
                        <a href="<?php echo $maps_url; ?>" target="_blank" class="map-link" title="View on Google Maps">
                            <i class="fas fa-map"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Form Section -->
        <div class="purchase-form">
            <span class="price-tag">₦<?php echo number_format($property['price'], 2); ?></span>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="purchase.php?id=<?php echo $property['id']; ?>" method="POST">
                <div class="mb-3">
                    <label for="purchase_code" class="form-label">Enter Purchase Code</label>
                    <input type="text" class="form-control" id="purchase_code" name="purchase_code" required>
                </div>
                <button type="submit" class="btn btn-success">Confirm Purchase</button>
            </form>

            <!-- WhatsApp Button -->
            <a href="<?php echo $whatsapp_url; ?>" class="btn btn-whatsapp">
                <i class="fab fa-whatsapp"></i> Get purchase code
            </a>
        </div>
    </div>
</div>

<!-- Modal for previewing media -->
<div id="mediaModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <div class="modal-content" id="modalMediaContent"></div>
</div>

<script>
    function openModal(mediaSrc, mediaType) {
        var modal = document.getElementById("mediaModal");
        var mediaContent = document.getElementById("modalMediaContent");

        if (['jpg', 'jpeg', 'png', 'gif'].includes(mediaType)) {
            mediaContent.innerHTML = '<img src="' + mediaSrc + '" style="width: 100%;">';
        } else if (['mp4', 'webm', 'ogg'].includes(mediaType)) {
            mediaContent.innerHTML = '<video controls style="width: 100%;"><source src="' + mediaSrc + '" type="video/' + mediaType + '"></video>';
        }

        modal.style.display = "block";
    }

    function closeModal() {
        document.getElementById("mediaModal").style.display = "none";
    }
</script>
<?php include 'includes/footer.php'; ?>
