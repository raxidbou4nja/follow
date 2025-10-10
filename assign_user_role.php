<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';
require_once 'includes/auth.php';

// Check admin access
requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'], $data['role_id'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$data['user_id'], $data['role_id']]);
        echo json_encode(['success' => true, 'message' => 'Role assigned successfully']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'error' => 'User already has this role']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    }
} else {
    echo json_encode(['success' => false, 'error' => 'User ID and Role ID are required']);
}
