<?php

$stmt = $conn->prepare("
    INSERT INTO applications 
    (user_id, property_id, application_type, message, applied_at)
    VALUES (?, ?, ?, ?, NOW())
");

$applicationType = $data['application_type'];
$message = "I want to apply for $applicationType for property ID: " . $data['property_id'];

$stmt->execute([
    $_SESSION['user_id'],
    $data['property_id'],
    $applicationType,
    $message
]); 