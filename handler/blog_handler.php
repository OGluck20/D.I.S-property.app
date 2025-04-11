<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';

    switch($action) {
        case 'fetch':
            $stmt = $conn->query("
                SELECT * FROM blog_posts 
                ORDER BY created_at DESC
            ");
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'posts' => $posts]);
            break;

        case 'get':
            $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'post' => $post]);
            break;

        case 'add':
            $mediaUrl = '';
            $mediaType = $_POST['media_type'];

            if ($mediaType === 'youtube') {
                $mediaUrl = $_POST['youtube_url'];
            } else if (isset($_FILES['media_file'])) {
                $file = $_FILES['media_file'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                
                if (!is_dir('../uploads/blog')) {
                    mkdir('../uploads/blog', 0777, true);
                }
                
                move_uploaded_file($file['tmp_name'], "../uploads/blog/$filename");
                $mediaUrl = $filename;
            }

            $stmt = $conn->prepare("
                INSERT INTO blog_posts (
                    title, content, media_type, media_url, external_link, created_at
                ) VALUES (
                    :title, :content, :media_type, :media_url, :external_link, NOW()
                )
            ");

            $stmt->execute([
                ':title' => $_POST['title'],
                ':content' => $_POST['content'],
                ':media_type' => $mediaType,
                ':media_url' => $mediaUrl,
                ':external_link' => $_POST['external_link']
            ]);

            echo json_encode(['success' => true]);
            break;

        case 'update':
            $postId = $_POST['post_id'];
            $mediaUrl = null;
            $mediaType = $_POST['media_type'];

            // Handle media upload
            if ($mediaType === 'youtube') {
                $mediaUrl = $_POST['youtube_url'];
            } else if (isset($_FILES['media_file']) && $_FILES['media_file']['size'] > 0) {
                $file = $_FILES['media_file'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                
                if (!is_dir('../uploads/blog')) {
                    mkdir('../uploads/blog', 0777, true);
                }
                
                move_uploaded_file($file['tmp_name'], "../uploads/blog/$filename");
                $mediaUrl = $filename;
            }

            // Update the blog post
            $stmt = $conn->prepare("
                UPDATE blog_posts 
                SET title = ?, 
                    content = ?, 
                    media_type = ?,
                    media_url = COALESCE(?, media_url),
                    external_link = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $_POST['title'],
                $_POST['content'],
                $mediaType,
                $mediaUrl,
                $_POST['external_link'],
                $postId
            ]);

            echo json_encode(['success' => true]);
            break;

        case 'delete':
            $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log('Blog Handler Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}