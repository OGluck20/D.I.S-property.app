<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $posts_per_page = 5;
    $offset = ($page - 1) * $posts_per_page;

    // Fix: Use LIMIT with parameters directly in the query
    $stmt = $conn->query("
        SELECT *,
               DATEDIFF(NOW(), created_at) as days_old
        FROM blog_posts 
        ORDER BY created_at DESC 
        LIMIT $posts_per_page OFFSET $offset
    ");

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = '';
    foreach ($posts as $post) {
        $excerpt = substr(strip_tags($post['content']), 0, 150) . '...';
        $isNew = $post['days_old'] <= 7;
        $postDate = new DateTime($post['created_at']);

        ob_start();
        ?>
        <div class="blog-card">
            <?php if ($isNew): ?>
                <span class="new-badge">NEW</span>
            <?php endif; ?>
            
            <div class="media-container">
                <?php if ($post['media_type'] === 'youtube'): 
                    preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $post['media_url'], $matches);
                    $video_id = $matches[1] ?? '';
                    if ($video_id):
                ?>
                    <div class="video-wrapper">
                        <iframe 
                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen
                        ></iframe>
                    </div>
                <?php endif; ?>
                
                <?php elseif ($post['media_type'] === 'video'): ?>
                    <div class="video-wrapper">
                        <video controls>
                            <source src="uploads/blog/<?php echo htmlspecialchars($post['media_url']); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                <?php elseif ($post['media_type'] === 'image'): ?>
                    <img src="uploads/blog/<?php echo htmlspecialchars($post['media_url']); ?>" 
                         alt="<?php echo htmlspecialchars($post['title']); ?>" 
                         class="blog-image">
                <?php endif; ?>
            </div>
            <div class="blog-content">
                <h3 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                <p class="blog-excerpt"><?php echo $excerpt; ?></p>
                <div class="blog-meta">
                    <div class="blog-date">
                        <i class="far fa-calendar-alt"></i>
                        <span><?php echo $postDate->format('M d, Y'); ?></span>
                    </div>
                    <a href="<?php echo htmlspecialchars($post['external_link']); ?>" 
                       class="read-more" 
                       target="_blank" 
                       rel="noopener noreferrer">
                        Read More <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php
        $html .= ob_get_clean();
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'has_more' => count($posts) == $posts_per_page
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}