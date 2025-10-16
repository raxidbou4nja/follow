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

        // Check if user exists and is not deleted
        $userCheck = $pdo->prepare("SELECT id FROM users WHERE id = ? AND deleted_at IS NULL");
        $userCheck->execute([$userId]);
        if (!$userCheck->fetch()) {
            echo json_encode(['success' => false, 'error' => 'User not found or has been deleted']);
            exit;
        }

        // Check if role exists and is not deleted
        $roleCheck = $pdo->prepare("SELECT id FROM roles WHERE id = ? AND deleted_at IS NULL");
        $roleCheck->execute([$roleId]);
        if (!$roleCheck->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Role not found or has been deleted']);
            exit;
        }

        // Check if assignment already exists (including soft-deleted)
        $existCheck = $pdo->prepare("SELECT id, deleted_at FROM user_roles WHERE user_id = ? AND role_id = ?");
        $existCheck->execute([$userId, $roleId]);
        $existing = $existCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if ($existing['deleted_at'] !== null) {
                // Restore soft-deleted assignment
                $stmt = $pdo->prepare("UPDATE user_roles SET deleted_at = NULL WHERE id = ?");
                $stmt->execute([$existing['id']]);
                echo json_encode(['success' => true, 'message' => 'Role assignment restored']);
            } else {
                // Already assigned
                echo json_encode(['success' => true, 'message' => 'Role already assigned']);
            }
        } else {
            // Create new assignment
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$userId, $roleId]);
            echo json_encode(['success' => true, 'message' => 'Role assigned successfully']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'User ID and Role ID are required']);
}
