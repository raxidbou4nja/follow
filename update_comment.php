<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'] ?? 0;
    $comment_text = $_POST['comment_text'] ?? '';

    if ($comment_id && $comment_text) {
        try {
            $stmt = $pdo->prepare("UPDATE comments SET comment_text = ? WHERE id = ?");
            $stmt->execute([$comment_text, $comment_id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Comment not found or no changes made']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid comment ID or text']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
