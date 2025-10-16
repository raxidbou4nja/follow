<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

try {
    $stmt = $pdo->query("SELECT id, username FROM users WHERE deleted_at IS NULL ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'users' => $users]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
