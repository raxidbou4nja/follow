<?php
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    try {
        // Get the image URL first
        $stmt = $pdo->prepare("SELECT image_url FROM test_images WHERE id = ?");
        $stmt->execute([$data['id']]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($image) {
            // Note: We keep the physical file but mark the record as deleted
            // If you want to delete the file, uncomment the code below:
            // $filePath = 'uploads/' . basename($image['image_url']);
            // if (file_exists($filePath)) {
            //     unlink($filePath);
            // }

            // Soft delete the record
            $stmt = $pdo->prepare("UPDATE test_images SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Image not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting image']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
