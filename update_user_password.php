<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'], $data['password'])) {
    $userId = $data['user_id'];
    $password = $data['password'];

    // Validate password length
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        exit;
    }

    try {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$password_hash, $userId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
}
