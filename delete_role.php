<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';
require_once 'includes/auth.php';

// Check admin access
requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true, 'message' => 'Role deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Role ID is required']);
}
