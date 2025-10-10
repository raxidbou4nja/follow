<?php
require_once 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Find user by email only
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Set cookies for 30 days
        $expire = time() + (30 * 24 * 60 * 60);
        setcookie('user_id', $user['id'], $expire, '/', '', false, true);
        setcookie('username', $user['username'], $expire, '/', '', false, true);
        setcookie('user_email', $user['email'], $expire, '/', '', false, true);
        header('Location: index.php');
        exit;
    } else {
        header('Location: login.php?error=1');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
