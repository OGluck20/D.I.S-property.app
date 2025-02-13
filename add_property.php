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
    $ip_address = trim($_POST['ip_address']);

    // Generate a unique purchase code for the property
    $purchase_code = uniqid('purchase_');

    // Handle file upload (either image or video)
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['media'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Check if the file is an image or a video (by MIME type)
        $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
        $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg'];
        $file_mime = mime_content_type($file_tmp);

        if (in_array($file_mime, $allowed_image_types) || in_array($file_mime, $allowed_video_types)) {
            // Generate a new unique filename
            $new_filename = uniqid('property_', true) . '.' . $file_ext;

            // Specify the directory to save the file
            $upload_dir = 'uploads/properties/';

            // Move the file to the destination directory
            if (!move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                $errors[] = "Failed to upload media file.";
            }
        } else {
            $errors[] = "Only image (jpeg, png, gif) and video (mp4, webm, ogg) formats are allowed.";
        }
    } else {
        $errors[] = "No media file uploaded or upload failed.";
    }

    if (empty($errors)) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO properties (user_id, title, description, price, address, city, state, zip_code, media, purchase_code, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
        $stmt->bindParam(11, $ip_address);

        // Execute the statement
        if ($stmt->execute()) {
            // Prepare WhatsApp message
            $admin_phone = '+2349046741088'; // Replace with the actual admin phone number
            $whatsapp_message = "A new property has been added.\n\n"
                . "Title: $title\n"
                . "Price: ₦$price\n"
                . "Address: $address\n"
                . "Purchase Code: $purchase_code\n"
                . "Media: uploads/$new_filename"; // Add the media file path

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
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px var(--shadow);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--secondary);
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .btn{
        background-color: var(--primary-dark);
        width: 90%;
        display: flex;
        margin: 0 auto;
        justify-content: center;
        transition: transform ease 1s;
    }

    .btn:hover{
        background-color: var(--primary);
        transform: scale(0.9);
    }

    @media (max-width: 768px) {
        .container {
            margin: 1rem;
            padding: 1.5rem;
        }
    }
</style>

<div class="container">
    <h2 class="mb-4">Add New Property</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>
    <form action="add_property.php" method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="form-group">
                <label for="title" class="form-label">Property Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            <div class="form-group">
                <label for="description" class="form-label">Property Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div class="form-group">
                <label for="price" class="form-label">Price (₦)</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required>
            </div>
            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
            </div>
            <div class="form-group">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" required>
            </div>
            <div class="form-group">
                <label for="state" class="form-label">State</label>
                <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($state); ?>" required>
            </div>
            <div class="form-group">
                <label for="zip_code" class="form-label">Zip Code</label>
                <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($zip_code); ?>" required>
            </div>
            <div class="form-group">
                <label for="ip_address" class="form-label">Property Location (IP Address)</label>
                <input type="text" class="form-control" id="ip_address" name="ip_address" 
                       pattern="^((\d{1,3}\.){3}\d{1,3})$" 
                       title="Enter valid IPv4 address (e.g., 192.168.0.1)"
                       required>
            </div>
            <div class="form-group">
                <label for="media" class="form-label">Property Media (Image/Video)</label>
                <input type="file" class="form-control" id="media" name="media" accept="image/*,video/*">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Property</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
