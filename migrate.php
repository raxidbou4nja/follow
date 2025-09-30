<?php
require_once 'includes/config.php';

function runMigrations()
{
    // First, connect without database to create it if needed
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Now connect to the database
    require_once 'includes/connection.php';
    global $pdo;

    // Create migrations table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Get applied migrations
    $stmt = $pdo->query("SELECT migration FROM migrations");
    $applied = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get migration files
    $files = glob('migrations/*.php');
    sort($files);

    foreach ($files as $file) {
        $migration = basename($file, '.php');
        if (!in_array($migration, $applied)) {
            echo "Running migration: $migration\n";
            $sqls = include $file;
            foreach ($sqls as $sql) {
                $pdo->exec($sql);
            }
            $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)")->execute([$migration]);
            echo "Migration $migration applied.\n";
        }
    }
}

runMigrations();
