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
    ['username' => 'john_doe', 'email' => 'john@example.com', 'password' => '123456789'],
    ['username' => 'jane_smith', 'email' => 'jane@example.com', 'password' => '123456789'],
    ['username' => 'bob_wilson', 'email' => 'bob@example.com', 'password' => '123456789']
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
    ['name' => 'Admin', 'description' => 'Full system access including user and role management'],
    ['name' => 'Developer', 'description' => 'Can create and modify tests'],
    ['name' => 'Tester', 'description' => 'Can run and report tests'],
    ['name' => 'Viewer', 'description' => 'Read-only access']
];

$roleIds = [];
foreach ($roles as $role) {
    $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = ?");
    $stmt->execute([$role['name'], $role['description'], $role['description']]);

    // Get the role ID
    $roleId = $pdo->lastInsertId();
    if (!$roleId) {
        // If no insert (duplicate), get existing ID
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute([$role['name']]);
        $roleId = $stmt->fetchColumn();
    }
    $roleIds[$role['name']] = $roleId;
}

// Assign Admin role to admin user
$adminUserStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$adminUserStmt->execute([$admin_email]);
$adminUserId = $adminUserStmt->fetchColumn();

if ($adminUserId && isset($roleIds['Admin'])) {
    // Check if admin already has the role
    $checkStmt = $pdo->prepare("SELECT id FROM user_roles WHERE user_id = ? AND role_id = ?");
    $checkStmt->execute([$adminUserId, $roleIds['Admin']]);

    if (!$checkStmt->fetch()) {
        // Assign Admin role
        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$adminUserId, $roleIds['Admin']]);
        echo "✓ Admin role assigned to admin user\n";
    }
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

echo "\n=== Seeding Complete ===\n";
echo "✓ Users created\n";
echo "✓ Roles created\n";
echo "✓ Admin role assigned to admin user\n";
echo "✓ Services and tests created\n";
echo "\nAdmin Login:\n";
echo "  Email: admin@gmail.com\n";
echo "  Password: 123456789\n";
