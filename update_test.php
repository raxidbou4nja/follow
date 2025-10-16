<?php
require_once 'includes/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['test_id'], $data['field'], $data['value'])) {
    $test_id = $data['test_id'];
    $field = $data['field'];
    $value = $data['value'];

    if (in_array($field, ['is_passed', 'has_error'])) {
        $stmt = $pdo->prepare("UPDATE tests SET $field = ? WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$value, $test_id]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid field']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
