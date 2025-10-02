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
            // Delete the file if it exists
            $filePath = 'uploads/' . basename($image['image_url']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete the record
            $stmt = $pdo->prepare("DELETE FROM test_images WHERE id = ?");
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
