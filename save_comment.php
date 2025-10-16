<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

$image_id = $_POST['image_id'] ?? '';
$comment_text = trim($_POST['comment_text'] ?? '');
$user_id = $_COOKIE['user_id'] ?? null;

if (!empty($image_id) && !empty($comment_text)) {
    // Validate that user_id exists if provided (only non-deleted users)
    if ($user_id) {
        $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = ? AND deleted_at IS NULL");
        $checkUser->execute([$user_id]);
        if (!$checkUser->fetch()) {
            // User doesn't exist or is deleted, set to null
            $user_id = null;
        }
    }

    $image_url = null;
    if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $filename = uniqid() . '_' . basename($_FILES['comment_image']['name']);
        $filepath = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['comment_image']['tmp_name'], $filepath)) {
            $image_url = $filepath;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO comments (image_id, user_id, comment_text, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$image_id, $user_id, $comment_text, $image_url]);
        echo json_encode(['success' => true, 'image_url' => $image_url ?: null]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
