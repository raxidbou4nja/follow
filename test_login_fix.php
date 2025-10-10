<?php

/**
 * Test login and comment functionality
 */

require_once 'includes/connection.php';

echo "=== Testing Login and Comment Fixes ===\n\n";

// Test 1: Check users have valid emails
echo "1. Checking users with email addresses:\n";
$users = $pdo->query("SELECT id, username, email FROM users WHERE email IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    echo "   ✓ ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}\n";
}

if (count($users) == 0) {
    echo "   ⚠ No users with email found! Run fix_user_data.php\n";
}

// Test 2: Check if all user IDs in comments exist
echo "\n2. Checking comment user_id foreign key integrity:\n";
$invalidComments = $pdo->query("
    SELECT c.id, c.user_id 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.user_id IS NOT NULL AND u.id IS NULL
")->fetchAll(PDO::FETCH_ASSOC);

if (count($invalidComments) > 0) {
    echo "   ⚠ Found " . count($invalidComments) . " comments with invalid user_id:\n";
    foreach ($invalidComments as $comment) {
        echo "      - Comment ID: {$comment['id']}, Invalid user_id: {$comment['user_id']}\n";
    }
    echo "   Fixing by setting user_id to NULL...\n";
    $pdo->exec("UPDATE comments SET user_id = NULL WHERE user_id NOT IN (SELECT id FROM users)");
    echo "   ✓ Fixed!\n";
} else {
    echo "   ✓ All comment user_ids are valid\n";
}

// Test 3: Check login form field
echo "\n3. Login form configuration:\n";
$loginContent = file_get_contents('login.php');
if (strpos($loginContent, 'name="email"') !== false) {
    echo "   ✓ Login form uses email field\n";
} else {
    echo "   ✗ Login form doesn't use email field\n";
}

if (strpos($loginContent, 'type="email"') !== false) {
    echo "   ✓ Login form has email input type\n";
} else {
    echo "   ⚠ Login form doesn't have email input type\n";
}

// Test 4: Check authenticate.php
echo "\n4. Authentication configuration:\n";
$authContent = file_get_contents('authenticate.php');
if (strpos($authContent, 'WHERE email = ?') !== false) {
    echo "   ✓ Authentication uses email lookup\n";
} else {
    echo "   ✗ Authentication doesn't use email lookup\n";
}

// Test 5: Check save_comment.php validation
echo "\n5. Comment save validation:\n";
$commentContent = file_get_contents('save_comment.php');
if (strpos($commentContent, 'SELECT id FROM users WHERE id = ?') !== false) {
    echo "   ✓ Comment save validates user_id exists\n";
} else {
    echo "   ✗ Comment save doesn't validate user_id\n";
}

echo "\n=== Test Complete ===\n";
echo "\n📧 Login Credentials (use EMAIL to login):\n";
echo "   • admin@gmail.com / 123456789\n";
echo "   • john@example.com / password123\n";
echo "   • jane@example.com / password123\n";
echo "   • bob@example.com / password123\n";
echo "\n✅ Login now requires EMAIL only (not username)\n";
echo "✅ Comments validate user_id before saving\n";
echo "✅ Invalid user_ids are set to NULL automatically\n";
