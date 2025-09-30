<?php
require_once 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv'], $_POST['type'])) {
    $type = $_POST['type'];
    $file = $_FILES['csv'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle !== false) {
            // Skip header
            fgetcsv($handle);

            if ($type === 'services') {
                while (($data = fgetcsv($handle)) !== false) {
                    if (count($data) >= 2) {
                        try {
                            $stmt = $pdo->prepare("INSERT INTO services (name, description) VALUES (?, ?)");
                            $stmt->execute([$data[0], $data[1]]);
                        } catch (PDOException $e) {
                            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
                            fclose($handle);
                            exit;
                        }
                    }
                }
            } elseif ($type === 'tests') {
                while (($data = fgetcsv($handle)) !== false) {
                    if (count($data) >= 3) {
                        $service_name = $data[0];
                        $test_name = $data[1];
                        $description = $data[2];

                        try {
                            // Find or create service
                            $stmt = $pdo->prepare("SELECT id FROM services WHERE name = ?");
                            $stmt->execute([$service_name]);
                            $service = $stmt->fetch(PDO::FETCH_ASSOC);
                            if (!$service) {
                                $stmt = $pdo->prepare("INSERT INTO services (name) VALUES (?)");
                                $stmt->execute([$service_name]);
                                $service_id = $pdo->lastInsertId();
                            } else {
                                $service_id = $service['id'];
                            }

                            // Insert test
                            $stmt = $pdo->prepare("INSERT INTO tests (service_id, name, description) VALUES (?, ?, ?)");
                            $stmt->execute([$service_id, $test_name, $description]);
                        } catch (PDOException $e) {
                            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
                            fclose($handle);
                            exit;
                        }
                    }
                }
            }

            fclose($handle);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Cannot open file']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Upload error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
