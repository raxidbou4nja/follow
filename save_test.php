<?php
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['name'], $data['service_id'])) {
    $tagged_users = isset($data['tagged_users']) ? json_encode($data['tagged_users']) : null;

    if (isset($data['id']) && $data['id']) {
        // Update
        $stmt = $pdo->prepare("UPDATE tests SET name = ?, description = ?, tagged_users = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['description'], $tagged_users, $data['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO tests (service_id, name, description, tagged_users) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['service_id'], $data['name'], $data['description'], $tagged_users]);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
