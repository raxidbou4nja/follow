<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once 'includes/connection.php';

$id = $_GET['id'] ?? 0;
if ($id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($service);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
