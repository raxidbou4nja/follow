<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

$image_id = $_GET['image_id'] ?? 0;
if ($image_id) {
    try {
        $stmt = $pdo->prepare("SELECT comment_text, image_url, created_at FROM comments WHERE image_id = ? ORDER BY created_at DESC");
        $stmt->execute([$image_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($comments);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error']);
    }
} else {
    echo json_encode([]);
}
