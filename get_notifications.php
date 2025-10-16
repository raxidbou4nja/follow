<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

$user_id = $_COOKIE['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

try {
    // Get unread count (exclude deleted notifications)
    $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0 AND deleted_at IS NULL");
    $countStmt->execute([$user_id]);
    $unreadCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get recent notifications (last 50, unread first, exclude deleted)
    $stmt = $pdo->prepare("
        SELECT n.*, t.name as test_name, s.name as service_name, s.id as service_id
        FROM notifications n
        JOIN tests t ON n.test_id = t.id AND t.deleted_at IS NULL
        JOIN services s ON t.service_id = s.id AND s.deleted_at IS NULL
        WHERE n.user_id = ? AND n.deleted_at IS NULL
        ORDER BY n.is_read ASC, n.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'unread_count' => $unreadCount,
        'notifications' => $notifications
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
