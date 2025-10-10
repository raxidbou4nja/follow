<?php
require_once 'includes/connection.php';

// Register migration 008
$pdo->exec("INSERT INTO migrations (migration) VALUES ('008_add_email_to_users')");
echo "Migration 008 registered\n";
