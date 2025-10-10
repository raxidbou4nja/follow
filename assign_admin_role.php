<?php

/**
 * Assign Admin role to the admin user
 */

require_once 'includes/connection.php';

echo "=== Assigning Admin Role ===\n\n";

// Get admin user
$adminUser = $pdo->query("SELECT id, username, email FROM users WHERE email = 'admin@gmail.com'")->fetch(PDO::FETCH_ASSOC);

if (!$adminUser) {
    echo "✗ Admin user not found. Please run ensure_admin.php first.\n";
    exit;
}

echo "Found admin user:\n";
echo "  ID: {$adminUser['id']}\n";
echo "  Username: {$adminUser['username']}\n";
echo "  Email: {$adminUser['email']}\n\n";

// Get Admin role
$adminRole = $pdo->query("SELECT id, name FROM roles WHERE name = 'Admin'")->fetch(PDO::FETCH_ASSOC);

if (!$adminRole) {
    echo "Creating Admin role...\n";
    $pdo->exec("INSERT INTO roles (name, description) VALUES ('Admin', 'Full system access including user and role management')");
    $adminRole = $pdo->query("SELECT id, name FROM roles WHERE name = 'Admin'")->fetch(PDO::FETCH_ASSOC);
    echo "✓ Admin role created (ID: {$adminRole['id']})\n\n";
} else {
    echo "Admin role exists (ID: {$adminRole['id']})\n\n";
}

// Check if admin already has the role
$existing = $pdo->prepare("SELECT id FROM user_roles WHERE user_id = ? AND role_id = ?");
$existing->execute([$adminUser['id'], $adminRole['id']]);

if ($existing->fetch()) {
    echo "✓ Admin user already has Admin role\n";
} else {
    echo "Assigning Admin role to admin user...\n";
    $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
    $stmt->execute([$adminUser['id'], $adminRole['id']]);
    echo "✓ Admin role assigned successfully!\n";
}

echo "\n=== Complete ===\n";
echo "\nAdmin user can now:\n";
echo "  ✓ Access Roles management page\n";
echo "  ✓ Access Users management page\n";
echo "  ✓ Create, edit, and delete roles\n";
echo "  ✓ Create, edit, and delete users\n";
echo "  ✓ Assign roles to users\n";
echo "\nLogin with:\n";
echo "  Email: admin@gmail.com\n";
echo "  Password: 123456789\n";
