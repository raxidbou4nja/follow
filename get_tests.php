<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once 'includes/connection.php';

$service_id = $_GET['service_id'] ?? 0;
$filter = $_GET['filter'] ?? 'all';

if ($service_id) {
    try {
        echo '<button class="btn btn-primary btn-sm mb-2" id="add-test-btn" data-service-id="' . $service_id . '"><i class="bi bi-plus-circle"></i> Add Test</button>';

        // Get statistics first (exclude deleted tests)
        $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM tests WHERE service_id = ? AND deleted_at IS NULL");
        $totalStmt->execute([$service_id]);
        $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $solvedStmt = $pdo->prepare("SELECT COUNT(*) as solved FROM tests WHERE service_id = ? AND is_passed = 1 AND deleted_at IS NULL");
        $solvedStmt->execute([$service_id]);
        $solved = $solvedStmt->fetch(PDO::FETCH_ASSOC)['solved'];

        $all_active = $filter == 'all' ? ' active' : '';
        $passed_active = $filter == 'passed' ? ' active' : '';
        $error_active = $filter == 'error' ? ' active' : '';
        $undone_active = $filter == 'undone' ? ' active' : '';
        echo '<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">';
        echo '<div class="d-flex align-items-center gap-2 flex-wrap">';
        echo '<button class="btn btn-secondary btn-sm filter-btn' . $all_active . '" data-filter="all">All</button>';
        echo '<button class="btn btn-success btn-sm filter-btn' . $passed_active . '" data-filter="passed">Passed</button>';
        echo '<button class="btn btn-danger btn-sm filter-btn' . $error_active . '" data-filter="error">With Errors</button>';
        echo '<button class="btn btn-warning btn-sm filter-btn' . $undone_active . '" data-filter="undone">Undone</button>';
        echo '<div class="search-container-inline" style="margin: 0;">';
        echo '<i class="bi bi-search search-icon-inline"></i>';
        echo '<input type="text" class="search-bar-inline" id="test-search-inline" placeholder="Search tests...">';
        echo '</div>';
        echo '</div>';
        echo '<div class="d-flex align-items-center gap-2">';
        $percentage = $total > 0 ? round(($solved / $total) * 100) : 0;
        $progressClass = $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
        echo '<div>';
        echo '<small class="text-muted me-2">Progress:</small>';
        echo '<div class="progress" style="width: 120px; height: 20px;">';
        echo '<div class="progress-bar ' . $progressClass . '" role="progressbar" style="width: ' . $percentage . '%">';
        echo '<small class="text-white fw-bold">' . $percentage . '%</small>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="badge bg-secondary">';
        echo $solved . '/' . $total . ' solved';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        $where = "WHERE service_id = ? AND deleted_at IS NULL";
        if ($filter == 'passed') $where .= " AND is_passed = 1";
        elseif ($filter == 'error') $where .= " AND has_error = 1";
        elseif ($filter == 'undone') $where .= " AND is_passed = 0 AND has_error = 0";

        $stmt = $pdo->prepare("SELECT * FROM tests $where ORDER BY name");
        $stmt->execute([$service_id]);
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>Name</th><th>Description</th><th>Passed</th><th>Error</th><th>Images</th><th>Upload</th><th>Actions</th></tr></thead>';
        echo '<tbody>';

        foreach ($tests as $test) {
            $images = $pdo->prepare("SELECT id, image_url, is_solved FROM test_images WHERE test_id = ? AND deleted_at IS NULL");
            $images->execute([$test['id']]);
            $image_data = $images->fetchAll(PDO::FETCH_ASSOC);

            $image_links = '';
            foreach ($image_data as $img) {
                $solved_class = $img['is_solved'] ? 'solved-image' : '';
                $image_links .= "<a href='#' class='image-link {$solved_class}' data-url='{$img['image_url']}' data-image-id='{$img['id']}' data-is-solved='{$img['is_solved']}' data-test-name='" . htmlspecialchars($test['name']) . "'>View</a> ";
            }

            // Build description with tagged users
            $description = htmlspecialchars($test['description'] ?? '');
            if (!empty($test['tagged_users'])) {
                $tagged_users = json_decode($test['tagged_users'], true);
                if (is_array($tagged_users) && count($tagged_users) > 0) {
                    $user_ids = implode(',', array_map('intval', $tagged_users));
                    $usersStmt = $pdo->query("SELECT username FROM users WHERE id IN ($user_ids) AND deleted_at IS NULL");
                    $usernames = $usersStmt->fetchAll(PDO::FETCH_COLUMN);

                    if (count($usernames) > 0) {
                        $user_tags = array_map(function ($username) {
                            return "<span class='badge bg-info text-dark'>@{$username}</span>";
                        }, $usernames);
                        $description .= '<div class="mt-1">' . implode(' ', $user_tags) . '</div>';
                    }
                }
            }

            echo "<tr data-test-id='{$test['id']}'>";
            echo "<td>{$test['name']}</td>";
            echo "<td>{$description}</td>";
            echo "<td><label class='checkbox-container'><input type='checkbox' class='is-passed' " . ($test['is_passed'] ? 'checked' : '') . "><span class='checkmark'></span></label></td>";
            echo "<td><label class='checkbox-container'><input type='checkbox' class='has-error' " . ($test['has_error'] ? 'checked' : '') . "><span class='checkmark'></span></label></td>";
            echo "<td style='width:120px'>$image_links</td>";
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
    echo '<p>No Task selected.</p>';
}
