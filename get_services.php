<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once 'includes/connection.php';

try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY name");
    while ($service = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li class='list-group-item service-item d-flex justify-content-between align-items-center' data-id='{$service['id']}'>
                <span>{$service['name']}</span>
                <div>
                    <button class='btn btn-sm btn-outline-secondary edit-service' data-id='{$service['id']}'><i class='bi bi-pencil'></i></button>
                    <button class='btn btn-sm btn-outline-danger delete-service' data-id='{$service['id']}'><i class='bi bi-trash'></i></button>
                </div>
              </li>";
    }
} catch (PDOException $e) {
    echo "<li class='list-group-item text-danger'>Database error: Unable to load services.</li>";
}
