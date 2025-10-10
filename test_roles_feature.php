<?php

/**
 * Test script to verify roles and user tagging functionality
 */

require_once 'includes/connection.php';

echo "=== Testing Roles and User Tagging Feature ===\n\n";

// Test 1: Check if tables exist
echo "1. Checking if new tables exist...\n";
$tables = ['roles', 'user_roles'];
foreach ($tables as $table) {
    $result = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    echo "   - Table '$table': " . ($result ? "✓ EXISTS" : "✗ MISSING") . "\n";
}

// Test 2: Check if tagged_users column exists in tests table
echo "\n2. Checking if 'tagged_users' column exists in tests table...\n";
$result = $pdo->query("SHOW COLUMNS FROM tests LIKE 'tagged_users'")->fetch();
echo "   - Column 'tagged_users': " . ($result ? "✓ EXISTS" : "✗ MISSING") . "\n";

// Test 3: Count roles
echo "\n3. Counting roles in database...\n";
$count = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
echo "   - Total roles: $count\n";
if ($count > 0) {
    $roles = $pdo->query("SELECT name FROM roles")->fetchAll(PDO::FETCH_COLUMN);
    echo "   - Role names: " . implode(', ', $roles) . "\n";
}

// Test 4: Count users
echo "\n4. Counting users in database...\n";
$count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
echo "   - Total users: $count\n";
if ($count > 0) {
    $users = $pdo->query("SELECT username FROM users LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
    echo "   - Sample usernames: " . implode(', ', $users) . "\n";
}

// Test 5: Check user-role assignments
echo "\n5. Checking user-role assignments...\n";
$count = $pdo->query("SELECT COUNT(*) FROM user_roles")->fetchColumn();
echo "   - Total assignments: $count\n";

// Test 6: Check tests with tagged users
echo "\n6. Checking tests with tagged users...\n";
$result = $pdo->query("SELECT COUNT(*) FROM tests WHERE tagged_users IS NOT NULL AND tagged_users != ''")->fetchColumn();
echo "   - Tests with tagged users: $result\n";

// Test 7: API endpoint availability
echo "\n7. Checking if API files exist...\n";
$apis = [
    'get_roles.php',
    'save_role.php',
    'delete_role.php',
    'get_user_roles.php',
    'assign_user_role.php',
    'remove_user_role.php',
    'get_users.php'
];

foreach ($apis as $api) {
    $exists = file_exists($api);
    echo "   - $api: " . ($exists ? "✓ EXISTS" : "✗ MISSING") . "\n";
}

// Test 8: Migration files
echo "\n8. Checking migration files...\n";
$migrations = [
    '005_create_roles_table.php',
    '006_create_user_roles_table.php',
    '007_add_tagged_users_to_tests.php'
];

foreach ($migrations as $migration) {
    $exists = file_exists("migrations/$migration");
    echo "   - $migration: " . ($exists ? "✓ EXISTS" : "✗ MISSING") . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nNext steps:\n";
echo "1. Visit http://localhost/php/follow/ to access the main application\n";
echo "2. Visit http://localhost/php/follow/roles.php to manage roles and user assignments\n";
echo "3. Create or edit a test to tag users in the test description\n";
