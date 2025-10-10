<?php
header('Content-Type: application/json');
require_once 'includes/connection.php';
require_once 'includes/auth.php';

// Check admin access
requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['username'], $data['email'], $data['password'])) {
    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = $data['password'];
    $userId = $data['id'] ?? null;

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }

    // Validate username (alphanumeric and underscore only)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(['success' => false, 'error' => 'Username can only contain letters, numbers, and underscores']);
        exit;
    }

    // Validate password length
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        exit;
    }

    try {
        if ($userId) {
            // Update existing user
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $checkStmt->execute([$username, $email, $userId]);
            if ($checkStmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
                exit;
            }

            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
                $stmt->execute([$username, $email, $password_hash, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->execute([$username, $email, $userId]);
            }
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            // Create new user
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);
            if ($checkStmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
                exit;
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash]);
            echo json_encode(['success' => true, 'message' => 'User created successfully', 'id' => $pdo->lastInsertId()]);
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
}
