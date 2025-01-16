<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Handle image upload
    $target_dir = "uploads/solutions/";
    $file_name = time() . '_' . $_FILES["image"]["name"];
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name);
    
    $sql = "INSERT INTO solutions (title, type, description, price, media) 
            VALUES (:title, :type, :description, :price, :media)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'title' => $title,
        'type' => $type,
        'description' => $description,
        'price' => $price,
        'media' => $file_name
    ]);
    
    header('Location: admin.php?success=solution_added');
    exit();
}