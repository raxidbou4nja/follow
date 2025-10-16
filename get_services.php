<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once 'includes/connection.php';

try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY name");
    while ($service = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Get statistics for this service
        $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM tests WHERE service_id = ?");
        $totalStmt->execute([$service['id']]);
        $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $solvedStmt = $pdo->prepare("SELECT COUNT(*) as solved FROM tests WHERE service_id = ? AND is_passed = 1");
        $solvedStmt->execute([$service['id']]);
        $solved = $solvedStmt->fetch(PDO::FETCH_ASSOC)['solved'];

        $percentage = $total > 0 ? round(($solved / $total) * 100) : 0;
        $progressClass = $percentage >= 80 ? 'text-success' : ($percentage >= 50 ? 'text-warning' : 'text-danger');

        // Correctly calculate stroke-dasharray for circle progress
        $circumference = 2 * M_PI * 15.915; // 2 * pi * r
        $strokeDasharray = ($percentage / 100) * $circumference;

        // Adjust font size based on percentage length
        $fontSize = $percentage == 100 ? '6px' : ($percentage >= 10 ? '7px' : '8px');

        echo "<li class='list-group-item service-item d-flex justify-content-between align-items-center' data-id='{$service['id']}'>
                <span class='service-name'>{$service['name']}</span>
                <div class='d-flex align-items-center'>
                    <div class='position-relative me-2' style='width: 30px; height: 30px;'>
                        <svg class='position-absolute' width='30' height='30' viewBox='0 0 42 42' style='transform: rotate(-90deg);'>
                            <circle cx='21' cy='21' r='15.915' fill='transparent' stroke='#e9ecef' stroke-width='3'></circle>
                            <circle cx='21' cy='21' r='15.915' fill='transparent' stroke='currentColor' stroke-width='3' 
                                    stroke-dasharray='{$strokeDasharray} {$circumference}' 
                                    class='{$progressClass}' stroke-linecap='round'></circle>
                        </svg>
                        <div class='position-absolute top-50 start-50 translate-middle'>
                            <small class='fw-bold {$progressClass}' style='font-size: {$fontSize};'>{$percentage}%</small>
                        </div>
                    </div>
                    <div>
                        <button class='btn btn-xs btn-outline-secondary edit-service' data-id='{$service['id']}'><i class='bi bi-pencil'></i></button>
                        <button class='btn btn-xs btn-outline-danger delete-service' data-id='{$service['id']}'><i class='bi bi-trash'></i></button>
                    </div>
                </div>
              </li>";
    }
} catch (PDOException $e) {
    echo "<li class='list-group-item text-danger'>Database error: Unable to load services.</li>";
}
