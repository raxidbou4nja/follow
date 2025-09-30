<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once 'includes/connection.php';

$service_id = $_GET['service_id'] ?? 0;

if ($service_id) {
    try {
        echo '<button class="btn btn-primary btn-sm mb-2" id="add-test-btn" data-service-id="' . $service_id . '">Add Test</button>';
        $stmt = $pdo->prepare("SELECT * FROM tests WHERE service_id = ? ORDER BY name");
        $stmt->execute([$service_id]);
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>Name</th><th>Description</th><th>Passed</th><th>Error</th><th>Images</th><th>Upload</th><th>Actions</th></tr></thead>';
        echo '<tbody>';

        foreach ($tests as $test) {
            $images = $pdo->prepare("SELECT id, image_url FROM test_images WHERE test_id = ?");
            $images->execute([$test['id']]);
            $image_data = $images->fetchAll(PDO::FETCH_ASSOC);

            $image_links = '';
            foreach ($image_data as $img) {
                $image_links .= "<a href='#' class='image-link' data-url='{$img['image_url']}' data-image-id='{$img['id']}'>View</a> ";
            }
            echo "<tr data-test-id='{$test['id']}'>";
            echo "<td>{$test['name']}</td>";
            echo "<td>{$test['description']}</td>";
            echo "<td><label class='checkbox-container'><input type='checkbox' class='is-passed' " . ($test['is_passed'] ? 'checked' : '') . "><span class='checkmark'></span></label></td>";
            echo "<td><label class='checkbox-container'><input type='checkbox' class='has-error' " . ($test['has_error'] ? 'checked' : '') . "><span class='checkmark'></span></label></td>";
            echo "<td style='width:100px'>$image_links</td>";
            echo "<td><button class='btn btn-sm btn-primary upload-btn' data-test-id='{$test['id']}'><i class='bi bi-upload'></i> Upload</button></td>";
            echo "<td>
                    <button class='btn btn-xs btn-outline-secondary edit-test' data-id='{$test['id']}'><i class='bi bi-pencil'></i></button>
                    <button class='btn btn-xs btn-outline-danger delete-test' data-id='{$test['id']}'><i class='bi bi-trash'></i></button>
                  </td>";
            echo "</tr>";
        }

        echo '</tbody></table>';
        echo '</div>';
    } catch (PDOException $e) {
        echo '<p class="text-danger">Database error: Unable to load tests.</p>';
    }
} else {
    echo '<p>No service selected.</p>';
}
