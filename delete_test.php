<?php
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $stmt = $pdo->prepare("DELETE FROM tests WHERE id = ?");
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
