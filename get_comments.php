<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

$image_id = $_GET['image_id'] ?? 0;
if ($image_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.id, c.comment_text, c.image_url, c.created_at, 
                   u.username, u.email
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id AND u.deleted_at IS NULL
            WHERE c.image_id = ? AND c.deleted_at IS NULL
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$image_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($comments);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error']);
    }
} else {
    echo json_encode([]);
}
