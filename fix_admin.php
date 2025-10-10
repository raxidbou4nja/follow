<?php
require_once 'includes/connection.php';

// Delete duplicate admin user and keep only one
$pdo->exec("DELETE FROM users WHERE id = 1");
$pdo->exec("UPDATE users SET username='admin', email='admin@gmail.com' WHERE id = 2");

echo "✓ Fixed admin user\n";
echo "✓ Admin login: admin or admin@gmail.com / Password: 123456789\n";
