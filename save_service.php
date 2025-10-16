<?php
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['name'])) {
    if (isset($data['id']) && $data['id']) {
        // Update (only non-deleted services)
        $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ? WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$data['name'], $data['description'], $data['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO services (name, description) VALUES (?, ?)");
        $stmt->execute([$data['name'], $data['description']]);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
