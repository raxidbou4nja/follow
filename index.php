<!DOCTYPE html>
<?php
if (!isset($_COOKIE['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
require_once 'includes/auth.php';
$is_admin = isAdmin();
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testing Platform Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-3 sidebar">
                <!-- Logo -->
                <div class="logo-container">
                    <img src="assets/images/logo.png" alt="" width="80%">
                    <div class="logo-subtitle mt-2">Testing Manager</div>
                </div>

                <h4 class="mb-3" style="font-weight: 700; color: var(--primary-color);">Tasks</h4>
                <button class="btn btn-primary btn-custom mb-3 w-100" id="add-service-btn">
                    <i class="bi bi-plus-circle"></i> Add Task
                </button>

                <!-- Search Bar for Services -->
                <div class="search-container">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="search-bar" id="service-search" placeholder="Search services...">
                </div>

                <ul class="list-group" id="services-list">
                    <!-- Services loaded via AJAX -->
                </ul>
            </div>
            <div class="col-12 col-md-8 main-content">
                <?php if (isset($_GET['error']) && $_GET['error'] == 'access_denied'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Access Denied!</strong>
                        You need Admin role to access that page.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <div class="header-container mb-4">
                    <div class="page-header">
                        <h2 class="mb-0">Tasks Management</h2>
                    </div>
                    <div class="header-buttons">
                        <button class="btn btn-icon-header position-relative" id="notification-btn" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notifications">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-badge" style="display: none;">
                                0
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" id="notification-dropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notifications</span>
                                <button class="btn btn-sm btn-link text-decoration-none" id="mark-all-read">Mark all read</button>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li id="notification-list">
                                <div class="text-center text-muted p-3">Loading...</div>
                            </li>
                        </ul>
                        <?php if ($is_admin): ?>
                            <a href="users.php" class="btn btn-icon-header" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Users">
                                <i class="bi bi-people"></i>
                            </a>
                            <a href="roles.php" class="btn btn-icon-header" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Roles">
                                <i class="bi bi-person-badge"></i>
                            </a>
                        <?php endif; ?>
                        <button class="btn btn-icon-header" id="upload-csv-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Upload CSV">
                            <i class="bi bi-upload"></i>
                        </button>
                        <a href="logout.php" class="btn btn-icon-header btn-logout" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Logout">
                            <i class="bi bi-box-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div id="tests-container" class="fade-in">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-clipboard2-data"></i>
                        </div>
                        <div class="empty-state-title">No Task Selected</div>
                        <div class="empty-state-text">Please select a Task from the sidebar to view and manage tests</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for images -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Image</h5>
                    <div>
                        <button type="button" class="btn btn-danger btn-sm me-2" id="deleteImageBtn">
                            <i class="bi bi-trash"></i> Delete Image
                        </button>
                        <button type="button" class="btn btn-success btn-sm me-2" id="toggleSolvedBtn">
                            <i class="bi bi-check-circle"></i> Mark as Solved
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <img id="modal-image" src="" class="img-fluid" alt="Image">
                        </div>
                        <div class="col-md-4">
                            <div id="comments-section" class="d-flex flex-column h-100">
                                <h6 class="mb-3">Comments</h6>
                                <div id="comments-list" class="flex-grow-1 overflow-auto mb-3" style="max-height: 300px;"></div>
                                <form id="commentForm" enctype="multipart/form-data" class="border-top pt-3">
                                    <input type="hidden" id="commentImageId">
                                    <div class="mb-2">
                                        <textarea class="form-control form-control-sm" id="commentText" rows="2" placeholder="Add a comment..." required></textarea>
                                    </div>
                                    <div class="mb-2">
                                        <input type="file" class="form-control form-control-sm" id="commentImage" accept="image/*">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Add Comment</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for service -->
    <div class="modal fade" id="serviceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalLabel">Add Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="serviceForm">
                        <input type="hidden" id="serviceId" name="id">
                        <div class="mb-3">
                            <label for="serviceName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="serviceName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="serviceDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="serviceDescription" name="description"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for test -->
    <div class="modal fade" id="testModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="testModalLabel">Add Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="testForm">
                        <input type="hidden" id="testId" name="id">
                        <input type="hidden" id="testServiceId" name="service_id">
                        <div class="mb-3">
                            <label for="testName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="testName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="testDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="testDescription" name="description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tag Users</label>
                            <div id="taggedUsersContainer" class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                <p class="text-muted mb-0">Loading users...</p>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for CSV upload -->
    <div class="modal fade" id="csvModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="csvForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">Select CSV File</label>
                            <input type="file" class="form-control" id="csvFile" name="csv" accept=".csv" required>
                        </div>
                        <div class="mb-3">
                            <label for="csvType" class="form-label">Import Type</label>
                            <select class="form-control" id="csvType" name="type" required>
                                <option value="services">Tasks (columns: name, description)</option>
                                <option value="tests">Tests (columns: service_name, name, description)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for image upload -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="uploadTestId">
                    <div class="mb-3">
                        <label class="form-label">Upload from Device</label>
                        <input type="file" class="form-control" id="fileInput" accept="image/*" multiple>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Or Paste from Clipboard</label>
                        <div class="upload-area" id="pasteArea" tabindex="0">
                            <i class="bi bi-clipboard"></i>
                            <p>Click here and paste (Ctrl+V) an image</p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="uploadImageBtn">Upload</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/app.js"></script>
</body>

</html>