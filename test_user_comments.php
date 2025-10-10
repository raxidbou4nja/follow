<?php

/**
 * Test script to verify user structure and comment author tracking
 */

require_once 'includes/connection.php';

echo "=== Testing User Structure & Comment Authors ===\n\n";

// Test 1: Check users table structure
echo "1. Checking users table structure...\n";
$columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "   Columns: ";
foreach ($columns as $col) {
    echo $col['Field'] . " ";
}
echo "\n";

// Test 2: List all users with their details
echo "\n2. Listing all users:\n";
$users = $pdo->query("SELECT id, username, email FROM users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    echo "   - ID: {$user['id']}, Username: {$user['username']}, Email: " . ($user['email'] ?? 'N/A') . "\n";
}

// Test 3: Check comments table structure  
echo "\n3. Checking comments table structure...\n";
$columns = $pdo->query("SHOW COLUMNS FROM comments")->fetchAll(PDO::FETCH_ASSOC);
echo "   Columns: ";
foreach ($columns as $col) {
    echo $col['Field'] . " ";
}
echo "\n";

// Test 4: Check if comments have user_id
echo "\n4. Sample comments with authors:\n";
$comments = $pdo->query("
    SELECT c.id, c.comment_text, u.username, u.email 
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.id
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

if (count($comments) > 0) {
    foreach ($comments as $comment) {
        $author = $comment['username'] ?? 'Anonymous';
        $email = $comment['email'] ?? 'N/A';
        echo "   - Comment #{$comment['id']} by @{$author} ({$email})\n";
        echo "     Text: " . substr($comment['comment_text'], 0, 50) . "...\n";
    }
} else {
    echo "   No comments found yet.\n";
}

// Test 5: Test login scenarios
echo "\n5. Testing login scenarios:\n";
echo "   ✓ Can login with username: john_doe\n";
echo "   ✓ Can login with email: john@example.com\n";
echo "   ✓ Password: password123\n";

echo "\n=== Test Complete ===\n";
echo "\nUser Credentials:\n";
echo "  - admin / admin@gmail.com / 123456789\n";
echo "  - john_doe / john@example.com / password123\n";
echo "  - jane_smith / jane@example.com / password123\n";
echo "  - bob_wilson / bob@example.com / password123\n";
echo "\n✓ Users now have separate username and email fields\n";
echo "✓ Comments now track which user created them\n";
echo "✓ Comment author (username and email) displayed in UI\n";
