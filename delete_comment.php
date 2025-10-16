<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'] ?? 0;

    if ($comment_id) {
        try {
            // Soft delete: set deleted_at instead of hard delete
            $stmt = $pdo->prepare("UPDATE comments SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$comment_id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Comment not found']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid comment ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
