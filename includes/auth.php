<?php

/**
 * Check if current user has admin role
 */
require_once 'includes/connection.php';

function isAdmin()
{
    $user_id = $_COOKIE['user_id'] ?? null;

    if (!$user_id) {
        return false;
    }

    global $pdo;

    // Check if user has Admin role (exclude soft-deleted records)
    $stmt = $pdo->prepare("
        SELECT r.name 
        FROM user_roles ur
        JOIN roles r ON ur.role_id = r.id AND r.deleted_at IS NULL
        WHERE ur.user_id = ? AND r.name = 'Admin' AND ur.deleted_at IS NULL
    ");
    $stmt->execute([$user_id]);

    return $stmt->fetch() !== false;
}

function requireAdmin()
{
    if (!isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Access denied. Admin role required.']);
        exit;
    }
}
