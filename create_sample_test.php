<?php

/**
 * Create a sample test with tagged users to demonstrate the feature
 */

require_once 'includes/connection.php';

echo "Creating sample test with tagged users...\n\n";

// Get first service
$service = $pdo->query("SELECT id FROM services LIMIT 1")->fetch();
if (!$service) {
    echo "Error: No services found. Please run seed.php first.\n";
    exit;
}

$service_id = $service['id'];

// Get some user IDs
$users = $pdo->query("SELECT id, username FROM users WHERE username != 'admin@gmail.com' LIMIT 3")->fetchAll();
if (count($users) < 2) {
    echo "Error: Not enough users found. Please run seed.php first.\n";
    exit;
}

$user_ids = array_column($users, 'id');
$user_names = array_column($users, 'username');

// Create test with tagged users
$test_name = "Sample Test with User Tags";
$test_description = "This is a demonstration test showing user tagging functionality. The tagged users will appear below this description.";
$tagged_users_json = json_encode($user_ids);

$stmt = $pdo->prepare("INSERT INTO tests (service_id, name, description, tagged_users) VALUES (?, ?, ?, ?)");
$stmt->execute([$service_id, $test_name, $test_description, $tagged_users_json]);

echo "✓ Created test: '$test_name'\n";
echo "✓ Tagged users: " . implode(', ', array_map(function ($name) {
    return "@$name";
}, $user_names)) . "\n";
echo "✓ User IDs stored: " . implode(', ', $user_ids) . "\n\n";

echo "Now visit http://localhost/php/follow/ and select the first service to see the tagged users!\n";
echo "The tagged users will appear as blue badges below the test description.\n";
