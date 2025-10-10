<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

$user_id = $_GET['user_id'] ?? 0;

if ($user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, ur.id as user_role_id 
            FROM roles r
            LEFT JOIN user_roles ur ON r.id = ur.role_id AND ur.user_id = ?
            ORDER BY r.name
        ");
        $stmt->execute([$user_id]);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'roles' => $roles]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'User ID is required']);
}
