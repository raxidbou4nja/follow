<?php
require_once 'includes/connection.php';

echo "=== Testing Notification System ===\n\n";

// 1. Get test users
echo "1. Getting users for testing...\n";
$stmt = $pdo->query("SELECT id, username, email FROM users LIMIT 3");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($users) < 2) {
    echo "   ❌ Need at least 2 users for testing\n";
    exit;
}

echo "   Found " . count($users) . " users:\n";
foreach ($users as $user) {
    echo "   - {$user['username']} (ID: {$user['id']}, Email: {$user['email']})\n";
}

$tagger = $users[0]; // User who will tag
$tagged = array_slice($users, 1); // Users who will be tagged

// 2. Get or create a test service
echo "\n2. Getting/Creating test service...\n";
$stmt = $pdo->query("SELECT id FROM services LIMIT 1");
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    $stmt = $pdo->prepare("INSERT INTO services (name, description) VALUES (?, ?)");
    $stmt->execute(['Test Service', 'Service for notification testing']);
    $service_id = $pdo->lastInsertId();
    echo "   ✓ Created service ID: $service_id\n";
} else {
    $service_id = $service['id'];
    echo "   ✓ Using existing service ID: $service_id\n";
}

// 3. Create a test and tag users
echo "\n3. Creating test with tagged users...\n";
$tagged_user_ids = array_map(function ($u) {
    return $u['id'];
}, $tagged);
$tagged_json = json_encode($tagged_user_ids);

$stmt = $pdo->prepare("INSERT INTO tests (service_id, name, description, tagged_users) VALUES (?, ?, ?, ?)");
$stmt->execute([
    $service_id,
    'Notification Test - ' . date('H:i:s'),
    'Testing notification system',
    $tagged_json
]);
$test_id = $pdo->lastInsertId();
echo "   ✓ Created test ID: $test_id\n";
echo "   ✓ Tagged users: " . implode(', ', array_map(function ($u) {
    return $u['username'];
}, $tagged)) . "\n";

// 4. Manually create notifications (simulating save_test.php behavior)
echo "\n4. Creating notifications...\n";
$notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, test_id, message) VALUES (?, ?, ?)");

foreach ($tagged as $user) {
    $message = "@{$tagger['username']} tagged you in test: Notification Test";
    $notifStmt->execute([$user['id'], $test_id, $message]);
    echo "   ✓ Created notification for @{$user['username']}\n";
}

// 5. Check notifications
echo "\n5. Checking created notifications...\n";
$stmt = $pdo->prepare("
    SELECT n.*, u.username, t.name as test_name 
    FROM notifications n
    JOIN users u ON n.user_id = u.id
    JOIN tests t ON n.test_id = t.id
    WHERE n.test_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$test_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "   Found " . count($notifications) . " notifications:\n";
foreach ($notifications as $notif) {
    $read_status = $notif['is_read'] ? '✓ Read' : '○ Unread';
    echo "   - [{$read_status}] For @{$notif['username']}: {$notif['message']}\n";
}

// 6. Test marking as read
echo "\n6. Testing mark as read...\n";
if (count($notifications) > 0) {
    $first_notif = $notifications[0];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->execute([$first_notif['id']]);
    echo "   ✓ Marked notification ID {$first_notif['id']} as read\n";

    // Verify
    $stmt = $pdo->prepare("SELECT is_read FROM notifications WHERE id = ?");
    $stmt->execute([$first_notif['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['is_read'] == 1) {
        echo "   ✓ Verification: Notification is now marked as read\n";
    } else {
        echo "   ❌ Verification failed: Notification is still unread\n";
    }
}

// 7. Test unread count
echo "\n7. Testing unread count...\n";
foreach ($users as $user) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user['id']]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   - @{$user['username']}: {$count} unread notification(s)\n";
}

echo "\n=== Test Complete ===\n";
echo "\nYou can now:\n";
echo "1. Login as: {$tagged[0]['email']}\n";
echo "2. Click the notification bell icon\n";
echo "3. You should see the notification\n";
echo "4. Click it to navigate to the test\n";
echo "5. The test row should be highlighted\n";
