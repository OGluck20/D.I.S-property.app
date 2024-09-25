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

    // Set up WhatsApp redirection
    $admin_phone = '+2349035286982'; // Fetch from environment variable correctly

    // Check if the environment variable is correctly set
    if (!$admin_phone) {
        echo "Admin phone number not found in environment variables.";
    }

    $whatsapp_message = "Property Inquiry:\n\n"
        . "Title: " . htmlspecialchars($property['title']) . "\n"
        . "Price: ₦" . number_format($property['price'], 2) . "\n"
        . "Description: " . htmlspecialchars($property['description']);

    $whatsapp_message_encoded = urlencode($whatsapp_message);
    $whatsapp_url = "https://wa.me/$admin_phone?text=$whatsapp_message_encoded";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = trim($_POST['purchase_code']);

    // Verify the entered purchase code
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

            // Redirect to success page (no WhatsApp redirect now)
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
    .container{
        padding: 30px 20px;
        height: 82vh;
        margin-top: 2%;
        margin-bottom: 5%;
        overflow-y: scroll;
    }

    @media (max-width: 480px) {
        .container{
            margin-top: 2%;
            margin-bottom: 5%;
            overflow-y: scroll;
            padding: 30px 20px;
            height: 82vh;
        }
    }

    img{
        height: 60%;
        aspect-ratio: 1;
    }
</style>
<!-- HTML Form to input the purchase code -->
<div class="container">
    <h2>Purchase Property</h2>
    <h4><?php echo htmlspecialchars($property['title']); ?></h4>
    <img src="uploads/<?php echo htmlspecialchars($property['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>" style="width: 320px; height: auto;">
    <p><strong>Price:</strong> $<?php echo number_format($property['price'], 2); ?></p>
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
