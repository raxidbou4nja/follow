<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once 'includes/connection.php';

$id = $_GET['id'] ?? 0;
if ($id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
        $stmt->execute([$id]);
        $test = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($test);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
