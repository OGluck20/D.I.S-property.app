<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    $plot_size = trim($_POST['plot_size']);

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

    // Validate plot size
    if (empty($_POST['plot_size']) || $_POST['plot_size'] <= 0) {
        $errors[] = "Valid plot size required";
    }

    if (empty($errors)) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("
            INSERT INTO properties 
            (user_id, title, description, price, plot_size, address, city, state, zip_code, media, purchase_code, status)
            VALUES (:user_id, :title, :description, :price, :plot_size, :address, :city, :state, :zip_code, :media, :purchase_code, 'available')
        ");

        // Bind parameters
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':plot_size', $plot_size);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':zip_code', $zip_code);
        $stmt->bindParam(':media', $new_filename);
        $stmt->bindParam(':purchase_code', $purchase_code);

        // Execute the statement
        if ($stmt->execute()) {
            // Prepare WhatsApp message
            $admin_phone = '+2349046741088';
            $whatsapp_message = "A new property has been added.\n\n"
                . "Title: $title\n"
                . "Price: ₦$price\n"
                . "Address: $address\n"
                . "Purchase Code: $purchase_code\n"
                . "Media: uploads/$new_filename";

            // URL encode the message
            $whatsapp_message_encoded = urlencode($whatsapp_message);
            $whatsapp_url = "https://wa.me/$admin_phone?text=$whatsapp_message_encoded";

            // Use JavaScript for redirection
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Property Added Successfully',
                    text: 'Redirecting to WhatsApp...',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '$whatsapp_url';
                });
            </script>";
            exit();
        } else {
            $errors[] = "Failed to add property. Please try again.";
        }
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
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 30px var(--shadow);
        transition: transform 0.3s ease;
    }

    .container:hover {
        transform: translateY(-5px);
    }

    h2 {
        color: var(--secondary);
        font-weight: 600;
        text-align: center;
        margin-bottom: 2rem;
        position: relative;
    }

    h2:after {
        content: '';
        display: block;
        width: 50px;
        height: 3px;
        background: var(--primary);
        margin: 10px auto;
        border-radius: 3px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: var(--secondary);
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 0.875rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .form-control:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 1rem 2rem;
        font-weight: 600;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
        display: block;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .alert {
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 2rem;
        border: none;
    }

    .alert-danger {
        background-color: #fff5f5;
        color: #c53030;
        border-left: 4px solid #fc8181;
    }

    .alert-success {
        background-color: #f0fff4;
        color: #2f855a;
        border-left: 4px solid #68d391;
    }

    /* Style for file input */
    input[type="file"] {
        padding: 0.5rem;
        border: 2px dashed #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
    }

    input[type="file"]:hover {
        border-color: var(--accent);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container {
            margin: 1rem;
            padding: 1.5rem;
            max-width: 90%;
            margin: 2rem auto;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .btn-primary {
            width: 100%;
            max-width: none;
        }
    }
</style>

<!-- Update the form HTML - Remove IP address field and update structure -->
<div class="container">
    <h2>Add New Property</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="add_property.php" method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="form-group">
                <label for="title" class="form-label">Property Title</label>
                <input type="text" class="form-control" id="title" name="title" 
                       value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="price" class="form-label">Price (₦)</label>
                <input type="number" step="0.01" class="form-control" id="price" 
                       name="price" value="<?php echo htmlspecialchars($price); ?>" required>
            </div>

            <div class="form-group">
                <label for="plot_size" class="form-label">Plot Size (sqm)</label>
                <input type="number" step="0.01" class="form-control" id="plot_size" 
                       name="plot_size" required>
            </div>

            <div class="form-group">
                <label for="media" class="form-label">Property Media</label>
                <input type="file" class="form-control" id="media" name="media" 
                       accept="image/*,video/*" required>
                <small class="text-muted">Upload property image or video</small>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Property Description</label>
            <textarea class="form-control" id="description" name="description" 
                      rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" 
                       value="<?php echo htmlspecialchars($address); ?>" required>
            </div>

            <div class="form-group">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control" id="city" name="city" 
                       value="<?php echo htmlspecialchars($city); ?>" required>
            </div>

            <div class="form-group">
                <label for="state" class="form-label">State</label>
                <input type="text" class="form-control" id="state" name="state" 
                       value="<?php echo htmlspecialchars($state); ?>" required>
            </div>

            <div class="form-group">
                <label for="zip_code" class="form-label">ZIP Code</label>
                <input type="text" class="form-control" id="zip_code" name="zip_code" 
                       value="<?php echo htmlspecialchars($zip_code); ?>" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Add Property</button>
    </form>
</div>
<?php include 'includes/whatsapp_float.php'; ?>
<?php include 'includes/footer.php'; ?>
