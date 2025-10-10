<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';
require_once 'includes/auth.php';

// Check admin access
requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    try {
        // Check if user has any dependencies
        $userId = $data['id'];

        // Check comments
        $commentCheck = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
        $commentCheck->execute([$userId]);
        $commentCount = $commentCheck->fetchColumn();

        // Check user_roles
        $roleCheck = $pdo->prepare("SELECT COUNT(*) FROM user_roles WHERE user_id = ?");
        $roleCheck->execute([$userId]);
        $roleCount = $roleCheck->fetchColumn();

        if ($commentCount > 0) {
            // Set user_id to NULL in comments before deleting
            $pdo->prepare("UPDATE comments SET user_id = NULL WHERE user_id = ?")->execute([$userId]);
        }

        if ($roleCount > 0) {
            // Delete user roles
            $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$userId]);
        }

        // Now delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully',
                'info' => "Removed from $commentCount comments and $roleCount role assignments"
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'User ID is required']);
}
