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
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Management</h2>
            <div>
                <a href="roles.php" class="btn btn-secondary me-2"><i class="bi bi-person-badge"></i> Roles</a>
                <a href="index.php" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Back to Tests</a>
                <a href="logout.php" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Users</h5>
                        <button class="btn btn-sm btn-primary" id="addUserBtn">
                            <i class="bi bi-plus"></i> Add User
                        </button>
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

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="userId">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" required
                                placeholder="john_doe" pattern="[a-zA-Z0-9_]+"
                                title="Only letters, numbers, and underscores allowed">
                            <small class="text-muted">Display name (letters, numbers, underscore only)</small>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" required
                                placeholder="user@example.com">
                            <small class="text-muted">Used for login</small>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" required
                                minlength="6" placeholder="Minimum 6 characters">
                            <small class="text-muted">At least 6 characters</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirmPassword" required
                                minlength="6" placeholder="Re-enter password">
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> User will be able to login using their email address.
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="passwordForm">
                        <input type="hidden" id="passwordUserId">
                        <p>Change password for: <strong id="passwordUsername"></strong></p>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="newPassword" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="confirmNewPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirmNewPassword" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-key"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/users.js"></script>
</body>

</html>