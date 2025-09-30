<?php
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['name'], $data['service_id'])) {
    if (isset($data['id']) && $data['id']) {
        // Update
        $stmt = $pdo->prepare("UPDATE tests SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['description'], $data['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO tests (service_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$data['service_id'], $data['name'], $data['description']]);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
