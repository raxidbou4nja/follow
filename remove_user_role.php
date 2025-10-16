<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';
require_once 'includes/auth.php';

// Check admin access
requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'], $data['role_id'])) {
    try {
        $userId = (int) $data['user_id'];
        $roleId = (int) $data['role_id'];

        // Soft delete the user role assignment
        $stmt = $pdo->prepare("UPDATE user_roles SET deleted_at = NOW() WHERE user_id = ? AND role_id = ? AND deleted_at IS NULL");
        $stmt->execute([$userId, $roleId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Role removed successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Role assignment not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'User ID and Role ID are required']);
}
