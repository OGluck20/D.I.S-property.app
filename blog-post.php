<?php
include 'includes/db.php';
include 'includes/header.php';

$post_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: index.php');
    exit();
}

$postDate = new DateTime($post['created_at']);
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="container mt-5 mb-5">
    <div class="blog-post-container">
        <div class="blog-post-header">
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <div class="post-date">
                    <i class="far fa-calendar-alt"></i>
                    <span><?php echo $postDate->format('M d, Y'); ?></span>
                </div>
                <div class="share-buttons">
                    <button class="btn btn-share" onclick="share('<?php echo htmlspecialchars($post['title']); ?>')">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                </div>
            </div>
        </div>

        <div class="post-media mb-4">
            <?php switch($post['media_type']):
                case 'youtube': ?>
                    <div class="video-wrapper">
                        <?php
                        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $post['media_url'], $matches);
                        $video_id = $matches[1] ?? '';
                        if ($video_id):
                        ?>
                        <iframe 
                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen
                        ></iframe>
                        <?php endif; ?>
                    </div>
                    <?php break;
                
                case 'video': ?>
                    <div class="video-wrapper">
                        <video controls>
                            <source src="uploads/blog/<?php echo htmlspecialchars($post['media_url']); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <?php break;
                
                case 'image': ?>
                    <img src="uploads/blog/<?php echo htmlspecialchars($post['media_url']); ?>" 
                         alt="<?php echo htmlspecialchars($post['title']); ?>" 
                         class="post-image">
                    <?php break;
            endswitch; ?>
        </div>

        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
    </div>
</div>

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


.blog-post-container {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.blog-post-header {
    margin-bottom: 2rem;
}

.post-title {
    font-size: 2.5rem;
    color: var(--text);
    margin-bottom: 1rem;
}

.post-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #666;
}

.post-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-share {
    background: var(--primary);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-share:hover {
    background: var(--primary-dark);
}

.post-image {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
    border-radius: 10px;
}

.post-content {
    line-height: 1.8;
    color: var(--text);
}

.video-wrapper {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: 10px;
}

.video-wrapper iframe,
.video-wrapper video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</style>

<script>
function share(title) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: window.location.href
        })
        .catch(console.error);
    } else {
        // Fallback for browsers that don't support Web Share API
        const dummy = document.createElement('input');
        document.body.appendChild(dummy);
        dummy.value = window.location.href;
        dummy.select();
        document.execCommand('copy');
        document.body.removeChild(dummy);
        
        alert('Link copied to clipboard!');
    }
}
</script>

<?php include 'includes/footer.php'; ?>