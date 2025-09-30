<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'], $_POST['test_id'])) {
    $test_id = $_POST['test_id'];
    $file = $_FILES['image'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            try {
                $url = $filepath; // Relative URL
                $stmt = $pdo->prepare("INSERT INTO test_images (test_id, image_url) VALUES (?, ?)");
                $stmt->execute([$test_id, $url]);
                echo json_encode(['success' => true, 'url' => $url]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to move file']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Upload error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
