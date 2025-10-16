<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['image_id']) && isset($data['is_solved'])) {
    try {
        // Cast to proper types
        $imageId = (int) $data['image_id'];
        $isSolved = (bool) $data['is_solved'];

        // Debug logging
        error_log("Updating image ID: $imageId, is_solved: " . ($isSolved ? 'true' : 'false'));

        $stmt = $pdo->prepare("UPDATE test_images SET is_solved = ? WHERE id = ? AND deleted_at IS NULL");
        $result = $stmt->execute([$isSolved ? 1 : 0, $imageId]);

        $rowsAffected = $stmt->rowCount();

        // Debug logging
        error_log("Rows affected: $rowsAffected");

        if ($result && $rowsAffected > 0) {
            echo json_encode(['success' => true, 'rows_affected' => $rowsAffected]);
        } else {
            // Check if image exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM test_images WHERE id = ? AND deleted_at IS NULL");
            $checkStmt->execute([$imageId]);
            $exists = $checkStmt->fetchColumn() > 0;

            echo json_encode([
                'success' => false,
                'error' => 'No rows updated - image may not exist',
                'image_id' => $imageId,
                'image_exists' => $exists,
                'is_solved_value' => $isSolved ? 1 : 0
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing parameters', 'received' => $data]);
}
