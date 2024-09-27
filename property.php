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
        // Determine if media is an image or video by checking the extension
        $media_ext = strtolower(pathinfo($property['media'], PATHINFO_EXTENSION));
        $is_image = in_array($media_ext, ['jpg', 'jpeg', 'png', 'gif']);
        $is_video = in_array($media_ext, ['mp4', 'webm', 'ogg']);
        
        echo "
        <div class='property-detail' style='padding: 20px; height:80vh;'>
            <div style='height: 40vh; width: 100%; cursor: pointer;' onclick=\"openModal('uploads/{$property['media']}', '{$media_ext}')\">
        ";

        if ($is_image) {
            echo "<img src='uploads/{$property['media']}' alt='{$property['title']}' style='height: 100%; width: 100%; object-fit: cover;'>";
        } elseif ($is_video) {
            echo "<video controls style='height: 100%; width: 100%; object-fit: cover;'>
                    <source src='uploads/{$property['media']}' type='video/{$media_ext}'>
                    Your browser does not support the video tag.
                  </video>";
        } else {
            echo "<img src='https://via.placeholder.com/350x200' alt='No Media Available' style='height: 100%; width: 100%; object-fit: cover;'>";
        }

        echo "</div>";
        
        echo "
            <h2>{$property['title']}</h2>
            <p><strong>Description:</strong> {$property['description']}</p>
            <p><strong>Price:</strong> ₦" . number_format($property['price'], 2) . "</p>
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

<!-- Modal for previewing media -->
<div id="mediaModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <div class="modal-content" id="modalMediaContent">
        <!-- Media content will be inserted here dynamically -->
    </div>
</div>

<style>
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
    @media (max-width: 480px){
        .modal-content {
            margin-top: 30%;
      }
    }
</style>

<script>
    // Function to open the modal and preview the media
    function openModal(mediaSrc, mediaType) {
        var modal = document.getElementById("mediaModal");
        var mediaContent = document.getElementById("modalMediaContent");

        if (mediaType === 'jpg' || mediaType === 'jpeg' || mediaType === 'png' || mediaType === 'gif') {
            mediaContent.innerHTML = '<img src="' + mediaSrc + '">';
        } else if (mediaType === 'mp4' || mediaType === 'webm' || mediaType === 'ogg') {
            mediaContent.innerHTML = '<video controls><source src="' + mediaSrc + '" type="video/' + mediaType + '">Your browser does not support the video tag.</video>';
        }

        modal.style.display = "block";
    }

    // Function to close the modal
    function closeModal() {
        var modal = document.getElementById("mediaModal");
        modal.style.display = "none";
    }
</script>
