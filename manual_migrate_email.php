<?php
require_once 'includes/connection.php';

echo "Testing email column addition...\n";

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "Email column already exists!\n";
    } else {
        echo "Adding email column...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL AFTER username");
        echo "Email column added successfully!\n";
    }

    // Now update existing records
    echo "\nUpdating existing user records...\n";
    $stmt = $pdo->query("SELECT id, username, email FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        if (empty($user['email'])) {
            if (strpos($user['username'], '@') !== false) {
                // Username looks like email, use it as email and extract username
                $email = $user['username'];
                $username = substr($user['username'], 0, strpos($user['username'], '@'));

                $update = $pdo->prepare("UPDATE users SET email = ?, username = ? WHERE id = ?");
                $update->execute([$email, $username, $user['id']]);
                echo "Updated user ID {$user['id']}: username={$username}, email={$email}\n";
            }
        }
    }

    echo "\n✓ Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
