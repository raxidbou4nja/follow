<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';
require_once 'includes/auth.php';

// Check admin access
requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['name'])) {
    try {
        if (isset($data['id']) && $data['id']) {
            // Update (only non-deleted roles)
            $stmt = $pdo->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$data['name'], $data['description'] ?? '', $data['id']]);
            echo json_encode(['success' => true, 'message' => 'Role updated successfully']);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
            $stmt->execute([$data['name'], $data['description'] ?? '']);
            echo json_encode(['success' => true, 'message' => 'Role created successfully', 'id' => $pdo->lastInsertId()]);
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'error' => 'Role name already exists']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Role name is required']);
}
