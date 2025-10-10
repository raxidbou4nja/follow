<!DOCTYPE html>
<?php
if (!isset($_COOKIE['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user has admin role
require_once 'includes/auth.php';
if (!isAdmin()) {
    header('Location: index.php?error=access_denied');
    exit;
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Roles & User Management</h2>
            <div>
                <a href="users.php" class="btn btn-success me-2"><i class="bi bi-people"></i> Manage Users</a>
                <a href="index.php" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Back to Tests</a>
                <a href="logout.php" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>

        <div class="row">
            <!-- Roles Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Roles</h5>
                        <button class="btn btn-sm btn-primary" id="addRoleBtn">
                            <i class="bi bi-plus"></i> Add Role
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="rolesList">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted">Loading roles...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Users & Role Assignments</h5>
                    </div>
                    <div class="card-body">
                        <div id="usersList">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted">Loading users...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="roleForm">
                        <input type="hidden" id="roleId">
                        <div class="mb-3">
                            <label for="roleName" class="form-label">Role Name</label>
                            <input type="text" class="form-control" id="roleName" required>
                        </div>
                        <div class="mb-3">
                            <label for="roleDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="roleDescription" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Role</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Roles Modal -->
    <div class="modal fade" id="userRolesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userRolesModalLabel">Manage User Roles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="selectedUserId">
                    <div id="userRolesContent">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/roles.js"></script>
</body>

</html>