document.addEventListener('DOMContentLoaded', function () {
    loadRoles();
    loadUsers();

    // Add Role Button
    document.getElementById('addRoleBtn').addEventListener('click', function () {
        document.getElementById('roleId').value = '';
        document.getElementById('roleName').value = '';
        document.getElementById('roleDescription').value = '';
        document.getElementById('roleModalLabel').textContent = 'Add Role';
        new bootstrap.Modal(document.getElementById('roleModal')).show();
    });

    // Role Form Submit
    document.getElementById('roleForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const data = {
            id: document.getElementById('roleId').value,
            name: document.getElementById('roleName').value,
            description: document.getElementById('roleDescription').value
        };

        fetch('save_role.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
                    loadRoles();
                } else {
                    alert('Error: ' + result.error);
                }
            })
            .catch(error => {
                alert('Failed to save role: ' + error.message);
            });
    });

    // Edit Role
    document.addEventListener('click', function (e) {
        if (e.target.closest('.edit-role')) {
            const btn = e.target.closest('.edit-role');
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const description = btn.dataset.description;

            document.getElementById('roleId').value = id;
            document.getElementById('roleName').value = name;
            document.getElementById('roleDescription').value = description;
            document.getElementById('roleModalLabel').textContent = 'Edit Role';
            new bootstrap.Modal(document.getElementById('roleModal')).show();
        }
    });

    // Delete Role
    document.addEventListener('click', function (e) {
        if (e.target.closest('.delete-role')) {
            if (confirm('Delete this role? This will also remove it from all users.')) {
                const btn = e.target.closest('.delete-role');
                const id = btn.dataset.id;

                fetch('delete_role.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            loadRoles();
                            loadUsers();
                        } else {
                            alert('Error: ' + result.error);
                        }
                    })
                    .catch(error => {
                        alert('Failed to delete role: ' + error.message);
                    });
            }
        }
    });

    // Manage User Roles
    document.addEventListener('click', function (e) {
        if (e.target.closest('.manage-user-roles')) {
            const btn = e.target.closest('.manage-user-roles');
            const userId = btn.dataset.userId;
            const username = btn.dataset.username;

            document.getElementById('selectedUserId').value = userId;
            document.getElementById('userRolesModalLabel').textContent = `Manage Roles for @${username}`;
            loadUserRoles(userId);
            new bootstrap.Modal(document.getElementById('userRolesModal')).show();
        }
    });

    // Toggle User Role
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('role-checkbox')) {
            const userId = document.getElementById('selectedUserId').value;
            const roleId = e.target.dataset.roleId;
            const isChecked = e.target.checked;

            const endpoint = isChecked ? 'assign_user_role.php' : 'remove_user_role.php';

            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, role_id: roleId })
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        loadUsers(); // Refresh user list
                    } else {
                        alert('Error: ' + result.error);
                        e.target.checked = !isChecked; // Revert checkbox
                    }
                })
                .catch(error => {
                    alert('Failed to update role: ' + error.message);
                    e.target.checked = !isChecked; // Revert checkbox
                });
        }
    });
});

function loadRoles() {
    fetch('get_roles.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('rolesList');
                if (data.roles.length === 0) {
                    container.innerHTML = '<p class="text-muted text-center">No roles found. Create one to get started.</p>';
                    return;
                }

                let html = '<div class="list-group">';
                data.roles.forEach(role => {
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${escapeHtml(role.name)}</h6>
                                    <p class="mb-0 text-muted small">${escapeHtml(role.description || 'No description')}</p>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary edit-role" 
                                            data-id="${role.id}" 
                                            data-name="${escapeHtml(role.name)}" 
                                            data-description="${escapeHtml(role.description || '')}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger delete-role" data-id="${role.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                document.getElementById('rolesList').innerHTML =
                    '<div class="alert alert-danger">Failed to load roles</div>';
            }
        })
        .catch(error => {
            document.getElementById('rolesList').innerHTML =
                '<div class="alert alert-danger">Error loading roles</div>';
        });
}

function loadUsers() {
    fetch('get_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('usersList');
                if (data.users.length === 0) {
                    container.innerHTML = '<p class="text-muted text-center">No users found.</p>';
                    return;
                }

                let html = '<div class="list-group">';
                data.users.forEach(user => {
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">@${escapeHtml(user.username)}</h6>
                                    <small class="text-muted" id="user-roles-${user.id}">Loading roles...</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary manage-user-roles" 
                                        data-user-id="${user.id}" 
                                        data-username="${escapeHtml(user.username)}">
                                    <i class="bi bi-person-badge"></i> Manage Roles
                                </button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;

                // Load roles for each user
                data.users.forEach(user => {
                    loadUserRolesBadges(user.id);
                });
            } else {
                document.getElementById('usersList').innerHTML =
                    '<div class="alert alert-danger">Failed to load users</div>';
            }
        })
        .catch(error => {
            document.getElementById('usersList').innerHTML =
                '<div class="alert alert-danger">Error loading users</div>';
        });
}

function loadUserRolesBadges(userId) {
    fetch(`get_user_roles.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const userRoles = data.roles.filter(r => r.user_role_id !== null);
                const container = document.getElementById(`user-roles-${userId}`);

                if (userRoles.length === 0) {
                    container.textContent = 'No roles assigned';
                    container.className = 'text-muted small';
                } else {
                    const badges = userRoles.map(role =>
                        `<span class="badge bg-primary me-1">${escapeHtml(role.name)}</span>`
                    ).join('');
                    container.innerHTML = badges;
                    container.className = '';
                }
            }
        });
}

function loadUserRoles(userId) {
    const container = document.getElementById('userRolesContent');
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>';

    fetch(`get_user_roles.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.roles.length === 0) {
                    container.innerHTML = '<p class="text-muted text-center">No roles available. Create roles first.</p>';
                    return;
                }

                let html = '<div class="list-group">';
                data.roles.forEach(role => {
                    const isAssigned = role.user_role_id !== null;
                    html += `
                        <div class="list-group-item">
                            <div class="form-check">
                                <input class="form-check-input role-checkbox" type="checkbox" 
                                       id="role_${role.id}" 
                                       data-role-id="${role.id}" 
                                       ${isAssigned ? 'checked' : ''}>
                                <label class="form-check-label" for="role_${role.id}">
                                    <strong>${escapeHtml(role.name)}</strong>
                                    <br>
                                    <small class="text-muted">${escapeHtml(role.description || 'No description')}</small>
                                </label>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="alert alert-danger">Failed to load user roles</div>';
            }
        })
        .catch(error => {
            container.innerHTML = '<div class="alert alert-danger">Error loading user roles</div>';
        });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
