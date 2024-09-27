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

    .container {
        height: 81vh;
        font-size: 20px;
        overflow-y: scroll;
        margin: 5% auto;
        scrollbar-width: thin;
        scrollbar-color: #ffff3f #f1f1f1;
    }

    .empty-property {
        margin-top: 25%;
        text-align: center;
    }

    .media-preview {
        width: 100%;
        height: 200px;
        object-fit: cover;
        cursor: pointer;
    }

    @media (max-width: 480px) {
        .media-preview {
            width: inherit;
            height: 200px;
        }
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.9);
    }

    .modal-content {
        margin: 15% auto;
        display: block;
        width: 80%;
        max-width: 700px;
    }

    .modal-content img, .modal-content video {
        width: 100%;
        height: auto;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 25px;
        color: white;
        font-size: 35px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #999;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<div class="container">
    <h1 class="my-4">Available Properties</h1>
    <div class="row">
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($property = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card property-card">
                        <?php if ($property['media']): ?>
                            <?php
                            // Determine if media is an image or video by checking the extension
                            $media_ext = strtolower(pathinfo($property['media'], PATHINFO_EXTENSION));
                            $is_image = in_array($media_ext, ['jpg', 'jpeg', 'png', 'gif']);
                            $is_video = in_array($media_ext, ['mp4', 'webm', 'ogg']);
                            ?>
                            
                            <?php if ($is_image): ?>
                                <!-- Image preview -->
                                <img src="uploads/<?php echo htmlspecialchars($property['media']); ?>" 
                                     class="media-preview" 
                                     alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                     onclick="openModal('uploads/<?php echo htmlspecialchars($property['media']); ?>', 'image')">
                            <?php elseif ($is_video): ?>
                                <!-- Video preview -->
                                <video class="media-preview" controls 
                                        onclick="openModal('uploads/<?php echo htmlspecialchars($property['media']); ?>', 'video')">
                                    <source src="uploads/<?php echo htmlspecialchars($property['media']); ?>" type="video/<?php echo $media_ext; ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <!-- Placeholder for unsupported media types -->
                                <img src="https://via.placeholder.com/350x200" class="media-preview" alt="No Media">
                            <?php endif; ?>
                        <?php else: ?>
                            <img src="https://via.placeholder.com/350x200" class="media-preview" alt="No Media">
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

<!-- Modal for previewing media -->
<div id="mediaModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <div class="modal-content" id="modalMediaContent">
        <!-- Media content will be inserted here dynamically -->
    </div>
</div>

<script>
    // Function to open the modal and preview the media
    function openModal(mediaSrc, mediaType) {
        var modal = document.getElementById("mediaModal");
        var mediaContent = document.getElementById("modalMediaContent");
        
        if (mediaType === 'image') {
            mediaContent.innerHTML = '<img src="' + mediaSrc + '">';
        } else if (mediaType === 'video') {
            mediaContent.innerHTML = '<video controls><source src="' + mediaSrc + '" type="video/mp4">Your browser does not support the video tag.</video>';
        }

        modal.style.display = "block";
    }

    // Function to close the modal
    function closeModal() {
        var modal = document.getElementById("mediaModal");
        modal.style.display = "none";
    }
</script>

<?php include 'includes/footer.php'; ?>
