<?php
require_once 'includes/connection.php';

// Seed admin user
$admin_email = 'admin@gmail.com';
$admin_username = 'admin';
$password = '123456789'; // Change this in production
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Check if email column exists, if not, just use username field
$columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
$hasEmail = in_array('email', $columns);

if ($hasEmail) {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
    $stmt->execute([$admin_username, $admin_email, $password_hash]);
} else {
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
    $stmt->execute([$admin_email, $password_hash]);
}

// Seed additional users
$additional_users = [
    ['username' => 'john_doe', 'email' => 'john@example.com', 'password' => 'password123'],
    ['username' => 'jane_smith', 'email' => 'jane@example.com', 'password' => 'password123'],
    ['username' => 'bob_wilson', 'email' => 'bob@example.com', 'password' => 'password123']
];

foreach ($additional_users as $user) {
    $hash = password_hash($user['password'], PASSWORD_DEFAULT);
    if ($hasEmail) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
        $stmt->execute([$user['username'], $user['email'], $hash]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
        $stmt->execute([$user['username'], $hash]);
    }
}

// Seed roles
$roles = [
    ['name' => 'Admin', 'description' => 'Full system access'],
    ['name' => 'Developer', 'description' => 'Can create and modify tests'],
    ['name' => 'Tester', 'description' => 'Can run and report tests'],
    ['name' => 'Viewer', 'description' => 'Read-only access']
];

foreach ($roles as $role) {
    $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = ?");
    $stmt->execute([$role['name'], $role['description'], $role['description']]);
}

$services = [
    ['name' => 'API Testing', 'description' => 'Testing API endpoints'],
    ['name' => 'UI Testing', 'description' => 'Testing user interface components'],
    ['name' => 'Performance Testing', 'description' => 'Testing system performance']
];

foreach ($services as $service) {
    $stmt = $pdo->prepare("INSERT INTO services (name, description) VALUES (?, ?)");
    $stmt->execute([$service['name'], $service['description']]);
    $service_id = $pdo->lastInsertId();

    // Add some tests
    $tests = [
        ['name' => 'Test 1 for ' . $service['name'], 'description' => 'Description for test 1'],
        ['name' => 'Test 2 for ' . $service['name'], 'description' => 'Description for test 2'],
        ['name' => 'Test 3 for ' . $service['name'], 'description' => 'Description for test 3']
    ];

    foreach ($tests as $test) {
        $stmt = $pdo->prepare("INSERT INTO tests (service_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$service_id, $test['name'], $test['description']]);
    }
}

echo "Sample data inserted.\n";
