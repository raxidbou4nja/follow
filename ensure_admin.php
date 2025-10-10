<?php
require_once 'includes/connection.php';

echo "Ensuring admin user exists with proper email...\n\n";

// Check if admin user exists
$stmt = $pdo->query("SELECT id, username, email FROM users WHERE email = 'admin@gmail.com'");
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "Creating admin user...\n";
    $password_hash = password_hash('123456789', PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute(['admin', 'admin@gmail.com', $password_hash]);
        echo "✓ Admin user created: admin@gmail.com / 123456789\n";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "⚠ Admin user exists but needs email update\n";
            // Try to update existing admin
            $pdo->exec("UPDATE users SET email = 'admin@gmail.com' WHERE username = 'admin' AND email IS NULL");
            echo "✓ Updated admin user email\n";
        } else {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "✓ Admin user exists: {$admin['username']} ({$admin['email']})\n";
}

echo "\nAll users:\n";
$users = $pdo->query("SELECT id, username, email FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    $email = $user['email'] ?? 'NO EMAIL';
    echo "  - {$user['username']} → {$email}\n";
}

echo "\n✅ Done!\n";
