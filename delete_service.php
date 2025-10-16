<?php
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    // Soft delete: set deleted_at instead of hard delete
    $stmt = $pdo->prepare("UPDATE services SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
