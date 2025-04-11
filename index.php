<?php
include 'includes/db.php';
include 'includes/header.php';
require_once 'includes/track_visitor.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

trackVisitor($conn);
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
        --gradient: linear-gradient(135deg, var(--primary), var(--accent));
    }
    /* Hero Section Styles */
    .hero {
        width: 100%;
        height: 100vh;
        background:  url('assets/images/DIS-index-background.png'); /* Replace with your image name */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }


    /* Update Hero Content text color */
    .hero-content {
        text-align: center;
        color: #fff;
        margin-bottom: 3rem;
        z-index: 2;
    }

    /* Update title color */
    .hero-title {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        color: #fff;
    }

    /* Update subtitle color */
    .hero-subtitle {
        font-size: 1.5rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        color: #fff;
    }

    /* Navigation Tabs Styles */
    .nav-tabs {
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem;
        border-radius: 15px;
        border: none;
        display: flex;
        gap: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(10px);
    }

    .nav-tabs .nav-item {
        margin: 0;
        position: relative;
        z-index: 3;
    }

    .nav-tabs .nav-link {
        display: inline-block;
        text-decoration: none;
        color: white;
        padding: 1rem 2rem;
        border: 2px solid var(--primary);
        border-radius: 10px;
        transition: all 0.3s ease;
        font-weight: 500;
        background: transparent;
        cursor: pointer;
        pointer-events: auto;
    }

    .nav-tabs .nav-link:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
        text-decoration: none;
    }

    .nav-tabs .nav-link.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        text-decoration: none;
    }

    .nav-tabs .nav-link i {
        margin-right: 8px;
        pointer-events: none;
    }

    /* Floating Elements */
    .floating-element {
        position: absolute;
        z-index: 1;
    }

    .floating-1 {
        top: 10%;
        left: 5%;
        animation: float 20s infinite;
    }

    .floating-2 {
        bottom: 15%;
        right: 10%;
        animation: float 16s infinite reverse;
    }

    .floating-3 {
        top: 40%;
        right: 15%;
        animation: float 18s infinite;
    }

    .floating-4 {
        bottom: 30%;
        left: 10%;
        animation: float 25s infinite reverse;
    }

    .floating-5 {
        top: 20%;
        right: 30%;
        animation: float 22s infinite;
    }

    .floating-6 {
        bottom: 10%;
        left: 40%;
        animation: float 16s infinite reverse;
    }

    .floating-element i {
        color: var(--primary);
        opacity: 0.5;  /* Increased opacity for better visibility on image */
        filter: drop-shadow(0 0 5px rgba(0,0,0,0.3));
    }

    @keyframes float {
        0% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(20px, -20px) rotate(180deg); }
        100% { transform: translate(0, 0) rotate(360deg); }
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
        }
        
        .nav-tabs {
            flex-direction: column;
            width: 90%;
            gap: 0.5rem;
        }
        
        .nav-tabs .nav-link {
            width: 100%;
            text-align: center;
            padding: 0.75rem;
        }
    }

    /* Blog Section Styles */
    .blog-section {
        padding: 5rem 0;
        background: var(--background);
    }

    .blog-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .section-title {
        text-align: center;
        margin-bottom: 3rem;
        color: var(--text);
    }

    .blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .blog-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px var(--shadow);
        transition: transform 0.3s ease;
        position: relative;
    }

    .blog-card:hover {
        transform: translateY(-5px);
    }

    .blog-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .blog-content {
        padding: 1.5rem;
    }

    .blog-title {
        font-size: 1.25rem;
        color: var(--text);
        margin-bottom: 0.5rem;
    }

    .blog-excerpt {
        color: #666;
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    .blog-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #888;
        font-size: 0.9rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }

    .read-more {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }

    /* Add to your existing styles in index.php */
    .media-container {
        position: relative;
        width: 100%;
        background: #f8f9fa;
    }

    .video-wrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
        height: 0;
        overflow: hidden;
    }

    .video-wrapper iframe,
    .video-wrapper video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .blog-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .read-more {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .read-more:hover {
        color: var(--primary-dark);
    }

    .read-more i {
        font-size: 0.8em;
        transition: transform 0.3s ease;
    }

    .read-more:hover i {
        transform: translateX(3px);
    }

    @media (max-width: 768px) {
        .blog-grid {
            grid-template-columns: 1fr;
        }
    }

    .no-posts {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px var(--shadow);
    }

    .no-posts i {
        color: var(--primary);
        margin-bottom: 1rem;
        opacity: 0.7;
    }

    .no-posts h3 {
        color: var(--text);
        margin-bottom: 0.5rem;
    }

    .no-posts p {
        color: #666;
    }

    .new-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--primary);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 2;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .blog-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .blog-date i {
        color: var(--primary);
    }
        /* Add to your existing styles */
    .load-more-container {
        text-align: center;
        margin-top: 2rem;
    }

    .load-more-btn {
        background: var(--primary);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 30px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .load-more-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .load-more-btn.loading {
        opacity: 0.7;
        cursor: wait;
    }

    .load-more-btn.disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    .post-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    }

    .btn-share {
        background: none;
        border: none;
        color: var(--primary);
        cursor: pointer;
        padding: 0;
        font-size: 1.1rem;
        transition: color 0.3s ease;
    }

    .btn-share:hover {
        color: var(--primary-dark);
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Hero Section with Navigation -->
<section class="hero">
    <!-- Floating Elements -->
    <div class="floating-element floating-1">
        <i class="fas fa-home fa-2x"></i>
    </div>
    <div class="floating-element floating-2">
        <i class="fas fa-leaf fa-2x"></i>
    </div>
    <div class="floating-element floating-3">
        <i class="fas fa-cogs fa-2x"></i>
    </div>
    <div class="floating-element floating-4">
        <i class="fas fa-desktop fa-2x"></i>
    </div>
    <div class="floating-element floating-5">
        <i class="fas fa-network-wired fa-2x"></i>
    </div>
    <div class="floating-element floating-6">
        <i class="fas fa-server fa-2x"></i>
    </div>

    <!-- Hero Content -->
    <div class="hero-content">
        <h1 class="hero-title">Welcome to DIS Groups</h1>
        <p class="hero-subtitle">Innovative Solutions for Properties, Technology, and Agriculture</p>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs">
         <li class="nav-item">
            <a class="nav-link <?php echo ($_GET['tab'] ?? '') === 'properties' ? 'active' : ''; ?>" 
               href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php?tab=properties' : 'login.php'; ?>">
                <i class="fas fa-home"></i> DIS Realty
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($_GET['tab'] ?? '') === 'solutions' ? 'active' : ''; ?>" 
               href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php?tab=solutions' : 'login.php'; ?>">
                <i class="fas fa-tools"></i> DIS Solutions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($_GET['tab'] ?? '') === 'farms' ? 'active' : ''; ?>" 
               href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php?tab=farms' : 'login.php'; ?>">
                <i class="fas fa-leaf"></i> DIS Farms
            </a>
        </li>
    </ul>
</section>
<!-- Blog Section -->
<section class="blog-section">
    <div class="blog-container">
        <h2 class="section-title">Latest Updates</h2>
        <div class="blog-grid" id="blogGrid">
            <?php
            $posts_per_page = 5;
            $stmt = $conn->query("
                SELECT *,
                       DATEDIFF(NOW(), created_at) as days_old
                FROM blog_posts 
                ORDER BY created_at DESC 
                LIMIT $posts_per_page
            ");
            
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total posts count
            $total_posts = $conn->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
            
            if (empty($posts)): ?>
                <div class="no-posts">
                    <i class="fas fa-newspaper fa-3x"></i>
                    <h3>No Recent Posts</h3>
                    <p>Check back later for updates!</p>
                </div>
            <?php else:
                foreach ($posts as $post): 
                    $excerpt = substr(strip_tags($post['content']), 0, 150) . '...';
                    $isNew = $post['days_old'] <= 7; // Posts less than 7 days old
                    $postDate = new DateTime($post['created_at']);
            ?>
            <div class="blog-card">
                <?php if ($isNew): ?>
                    <span class="new-badge">NEW</span>
                <?php endif; ?>
                
                <div class="media-container">
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
                                 class="blog-image">
                            <?php break;
                    endswitch; ?>
                </div>
                <div class="blog-content">
                    <h3 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p class="blog-excerpt"><?php echo $excerpt; ?></p>
                    <div class="blog-meta">
                        <div class="blog-date">
                            <i class="far fa-calendar-alt"></i>
                            <span><?php echo $postDate->format('M d, Y'); ?></span>
                        </div>
                        <?php if ($post['external_link']): ?>
                            <a href="<?php echo htmlspecialchars($post['external_link']); ?>" 
                            class="read-more" 
                            target="_blank" 
                            rel="noopener noreferrer">
                                Read More <i class="fas fa-external-link-alt"></i>
                            </a>
                        <?php else: ?>
                            <div class="post-actions">
                                <?php if (!$post['external_link']): ?>
                                <?php endif; ?>
                                <a href="blog-post.php?id=<?php echo $post['id']; ?>" class="read-more">
                                    Read More <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            endif; 
            ?>
        </div>
        <?php if ($total_posts > $posts_per_page): ?>
        <div class="load-more-container">
            <button id="loadMoreBtn" class="load-more-btn" data-page="1" data-total="<?php echo $total_posts; ?>">
                Load More <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>
<!-- Add before closing body tag -->
<script src="assets/js/main.js"></script>
<?php include 'includes/whatsapp_float.php'; ?>
<?php include 'includes/footer.php'; ?>