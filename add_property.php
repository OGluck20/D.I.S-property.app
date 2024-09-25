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

$title = $description = $price = $address = $city = $state = $zip_code = "";
$new_filename = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip_code = trim($_POST['zip_code']);

    // Generate a unique purchase code for the property
    $purchase_code = uniqid('purchase_');

    // Handle image upload logic here...
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $image_name = $image['name'];
        $image_tmp = $image['tmp_name'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        // Generate a new unique filename to avoid collisions
        $new_filename = uniqid('property_', true) . '.' . $image_ext;

        // Specify the directory to save the image
        $upload_dir = 'uploads/';

        // Move the file to the destination directory
        if (!move_uploaded_file($image_tmp, $upload_dir . $new_filename)) {
            $errors[] = "Failed to upload image.";
        }
    } else {
        $errors[] = "No image uploaded or upload failed.";
    }

    if (empty($errors)) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO properties (user_id, title, description, price, address, city, state, zip_code, image, purchase_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bindParam(1, $_SESSION['user_id']);
        $stmt->bindParam(2, $title);
        $stmt->bindParam(3, $description);
        $stmt->bindParam(4, $price);
        $stmt->bindParam(5, $address);
        $stmt->bindParam(6, $city);
        $stmt->bindParam(7, $state);
        $stmt->bindParam(8, $zip_code);
        $stmt->bindParam(9, $new_filename);
        $stmt->bindParam(10, $purchase_code); // Include the purchase code

        // Execute the statement
        if ($stmt->execute()) {
            // Prepare WhatsApp message
            $admin_phone = '+2349046741088'; // Replace with the actual admin phone number
            $whatsapp_message = "A new property has been added.\n\n"
                . "Title: $title\n"
                . "Price: ₦$price\n"
                . "Address: $address\n"
                . "Purchase Code: $purchase_code";

            // URL encode the message
            $whatsapp_message_encoded = urlencode($whatsapp_message);

            // Redirect to WhatsApp with the message
            $whatsapp_url = "https://wa.me/$admin_phone?text=$whatsapp_message_encoded";
            header("Location: $whatsapp_url");
            exit();
        } else {
            $errors[] = "Failed to add property. Please try again.";
        }
    }
}
?>

<!-- style -->
<style>
    .container {
        padding: 30px 20px;
    }
</style>

<div class="container">
    <h2>Add Property</h2>
    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>
    <form action="add_property.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Property Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Property Description</label>
            <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price (₦)</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>">
        </div>
        <div class="mb-3">
            <label for="city" class="form-label">City</label>
            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>">
        </div>
        <div class="mb-3">
            <label for="state" class="form-label">State</label>
            <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($state); ?>">
        </div>
        <div class="mb-3">
            <label for="zip_code" class="form-label">Zip Code</label>
            <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($zip_code); ?>">
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Property Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Add Property</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
