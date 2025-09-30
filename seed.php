<?php
require_once 'includes/connection.php';

// Seed user
$username = 'admin@gmail.com';
$password = '123456789'; // Change this in production
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?) ON DUPLICATE KEY UPDATE password_hash = ?");
$stmt->execute([$username, $password_hash, $password_hash]);

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
