<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once 'includes/connection.php';

$service_id = $_GET['service_id'] ?? 0;
$filter = $_GET['filter'] ?? 'all';

if ($service_id) {
    try {
        echo '<button class="btn btn-primary btn-sm mb-2" id="add-test-btn" data-service-id="' . $service_id . '">Add Test</button>';

        $all_active = $filter == 'all' ? ' active' : '';
        $passed_active = $filter == 'passed' ? ' active' : '';
        $error_active = $filter == 'error' ? ' active' : '';
        echo '<div class="mb-2">';
        echo '<button class="btn btn-secondary btn-sm me-2 filter-btn' . $all_active . '" data-filter="all">All</button>';
        echo '<button class="btn btn-success btn-sm me-2 filter-btn' . $passed_active . '" data-filter="passed">Passed</button>';
        echo '<button class="btn btn-danger btn-sm me-2 filter-btn' . $error_active . '" data-filter="error">With Errors</button>';
        echo '</div>';

        $where = "WHERE service_id = ?";
        if ($filter == 'passed') $where .= " AND is_passed = 1";
        elseif ($filter == 'error') $where .= " AND has_error = 1";

        $stmt = $pdo->prepare("SELECT * FROM tests $where ORDER BY name");
        $stmt->execute([$service_id]);
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>Name</th><th style="width: 30%;">Description</th><th>Passed</th><th>Error</th><th>Images</th><th>Upload</th><th>Actions</th></tr></thead>';
        echo '<tbody>';

        foreach ($tests as $test) {
            $images = $pdo->prepare("SELECT id, image_url, is_solved FROM test_images WHERE test_id = ?");
            $images->execute([$test['id']]);
            $image_data = $images->fetchAll(PDO::FETCH_ASSOC);

            $image_links = '';
            foreach ($image_data as $img) {
                $solved_class = $img['is_solved'] ? 'solved-image' : '';
                $image_links .= "<a href='#' class='image-link {$solved_class}' data-url='{$img['image_url']}' data-image-id='{$img['id']}' data-is-solved='{$img['is_solved']}'>View</a> ";
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
