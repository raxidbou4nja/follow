<?php
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$current_user_id = $_COOKIE['user_id'] ?? null;

if (isset($data['name'], $data['service_id'])) {
    $tagged_users = isset($data['tagged_users']) ? json_encode($data['tagged_users']) : null;
    $tagged_users_array = isset($data['tagged_users']) ? $data['tagged_users'] : [];

    try {
        if (isset($data['id']) && $data['id']) {
            // Update - Get previous tagged users to find new tags
            $prevStmt = $pdo->prepare("SELECT tagged_users FROM tests WHERE id = ?");
            $prevStmt->execute([$data['id']]);
            $prevTagged = $prevStmt->fetch(PDO::FETCH_ASSOC)['tagged_users'] ?? null;
            $prevTaggedArray = $prevTagged ? json_decode($prevTagged, true) : [];

            $stmt = $pdo->prepare("UPDATE tests SET name = ?, description = ?, tagged_users = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['description'], $tagged_users, $data['id']]);

            $test_id = $data['id'];

            // Find newly tagged users
            $newlyTagged = array_diff($tagged_users_array, $prevTaggedArray);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO tests (service_id, name, description, tagged_users) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['service_id'], $data['name'], $data['description'], $tagged_users]);

            $test_id = $pdo->lastInsertId();

            // All tagged users are new for a new test
            $newlyTagged = $tagged_users_array;
        }

        // Create notifications for newly tagged users
        if (!empty($newlyTagged)) {
            // Get the user who created/updated the test
            $creatorStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $creatorStmt->execute([$current_user_id]);
            $creator = $creatorStmt->fetch(PDO::FETCH_ASSOC);
            $creator_name = $creator['username'] ?? 'Someone';

            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, test_id, message) VALUES (?, ?, ?)");

            foreach ($newlyTagged as $user_id) {
                // Don't notify the user who tagged themselves
                if ($user_id != $current_user_id) {
                    $message = "tagged: {$data['name']}";
                    $notifStmt->execute([$user_id, $test_id, $message]);
                }
            }
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false]);
}
