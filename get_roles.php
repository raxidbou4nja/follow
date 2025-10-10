<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

try {
    $stmt = $pdo->query("SELECT * FROM roles ORDER BY name");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'roles' => $roles]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
