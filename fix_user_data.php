<?php
require_once 'includes/connection.php';

echo "Fixing user data...\n\n";

// Update existing users with proper email addresses
$updates = [
    ['id' => 1, 'username' => 'admin', 'email' => 'admin@admin.com'],
    ['id' => 2, 'username' => 'admin', 'email' => 'admin@gmail.com'],
    ['id' => 3, 'username' => 'john_doe', 'email' => 'john@example.com'],
    ['id' => 4, 'username' => 'jane_smith', 'email' => 'jane@example.com'],
    ['id' => 5, 'username' => 'bob_wilson', 'email' => 'bob@example.com']
];

foreach ($updates as $update) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$update['username'], $update['email'], $update['id']]);
        echo "✓ Updated user ID {$update['id']}: @{$update['username']} ({$update['email']})\n";
    } catch (PDOException $e) {
        echo "✗ Error updating user ID {$update['id']}: " . $e->getMessage() . "\n";
    }
}

echo "\n✓ User data fixed!\n";
echo "\nYou can now login with:\n";
echo "  - Username: admin or Email: admin@gmail.com / Password: 123456789\n";
echo "  - Username: john_doe or Email: john@example.com / Password: password123\n";
echo "  - Username: jane_smith or Email: jane@example.com / Password: password123\n";
echo "  - Username: bob_wilson or Email: bob@example.com / Password: password123\n";
