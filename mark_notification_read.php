<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_COOKIE['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

try {
    if (isset($data['notification_id'])) {
        // Mark single notification as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$data['notification_id'], $user_id]);
    } elseif (isset($data['mark_all'])) {
        // Mark all notifications as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
