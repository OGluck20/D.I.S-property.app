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
    .container {
        padding: 30px 20px;
        height: 82vh;
        margin-top: 2%;
        margin-bottom: 5%;
        overflow-y: scroll;
    }

    @media (max-width: 480px) {
        .container {
            margin-top: 2%;
            margin-bottom: 5%;
            overflow-y: scroll;
            padding: 30px 20px;
            height: 82vh;
        }
    }

    /* Responsive media styling */
    img, video {
        max-width: 100%; /* Ensure the media doesn't exceed the container width */
        height: auto; /* Maintain the aspect ratio */
        object-fit: cover; /* Ensure the media fits nicely */
    }

    /* Full-screen modal styling */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
    }

    .modal-content {
        margin: 15% auto;
        width: 80%;
        max-width: 700px;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 25px;
        color: white;
        font-size: 35px;
        font-weight: bold;
    }

    .close:hover {
        color: #999;
    }
</style>

<div class="container">
    <h2>Purchase Property</h2>
    <h4><?php echo htmlspecialchars($property['title']); ?></h4>

    <!-- Display image or video -->
    <?php
    $media_ext = strtolower(pathinfo($property['media'], PATHINFO_EXTENSION));
    $is_image = in_array($media_ext, ['jpg', 'jpeg', 'png', 'gif']);
    $is_video = in_array($media_ext, ['mp4', 'webm', 'ogg']);
    ?>

    <div style="cursor: pointer;" onclick="openModal('uploads/<?php echo htmlspecialchars($property['media']); ?>', '<?php echo $media_ext; ?>')">
        <?php if ($is_image): ?>
            <img src="uploads/<?php echo htmlspecialchars($property['media']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
        <?php elseif ($is_video): ?>
            <video controls>
                <source src="uploads/<?php echo htmlspecialchars($property['media']); ?>" type="video/<?php echo $media_ext; ?>">
                Your browser does not support the video tag.
            </video>
        <?php else: ?>
            <img src="https://via.placeholder.com/350x200" alt="No Media Available">
        <?php endif; ?>
    </div>

    <p><strong>Price:</strong> ₦<?php echo number_format($property['price'], 2); ?></p>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($property['description']); ?></p>

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
        <button type="submit" class="btn btn-primary">Confirm Purchase</button>

        <!-- WhatsApp Button -->
        <a href="<?php echo $whatsapp_url; ?>" class="btn btn-success">Get purchase code</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

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
