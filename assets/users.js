document.addEventListener('DOMContentLoaded', function () {
    loadUsers();

    // Add User Button
    document.getElementById('addUserBtn').addEventListener('click', function () {
        document.getElementById('userId').value = '';
        document.getElementById('username').value = '';
        document.getElementById('email').value = '';
        document.getElementById('password').value = '';
        document.getElementById('confirmPassword').value = '';
        document.getElementById('userModalLabel').textContent = 'Add User';
        document.getElementById('password').required = true;
        document.getElementById('confirmPassword').required = true;
        new bootstrap.Modal(document.getElementById('userModal')).show();
    });

    // User Form Submit
    document.getElementById('userForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return;
        }

        const userId = document.getElementById('userId').value;
        const data = {
            id: userId || null,
            username: document.getElementById('username').value,
            email: document.getElementById('email').value,
            password: password
        };

        fetch('save_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
                    loadUsers();
                    alert(userId ? 'User updated successfully!' : 'User created successfully!');
                } else {
                    alert('Error: ' + result.error);
                }
            })
            .catch(error => {
                alert('Failed to save user: ' + error.message);
            });
    });

    // Password Form Submit
    document.getElementById('passwordForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const newPassword = document.getElementById('newPassword').value;
        const confirmNewPassword = document.getElementById('confirmNewPassword').value;

        if (newPassword !== confirmNewPassword) {
            alert('Passwords do not match!');
            return;
        }

        const data = {
            user_id: document.getElementById('passwordUserId').value,
            password: newPassword
        };

        fetch('update_user_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('passwordModal')).hide();
                    alert('Password updated successfully!');
                } else {
                    alert('Error: ' + result.error);
                }
            })
            .catch(error => {
                alert('Failed to update password: ' + error.message);
            });
    });

    // Edit User
    document.addEventListener('click', function (e) {
        if (e.target.closest('.edit-user')) {
            const btn = e.target.closest('.edit-user');
            const id = btn.dataset.id;
            const username = btn.dataset.username;
            const email = btn.dataset.email;

            document.getElementById('userId').value = id;
            document.getElementById('username').value = username;
            document.getElementById('email').value = email;
            document.getElementById('password').value = '';
            document.getElementById('confirmPassword').value = '';
            document.getElementById('userModalLabel').textContent = 'Edit User';
            document.getElementById('password').required = false;
            document.getElementById('confirmPassword').required = false;
            new bootstrap.Modal(document.getElementById('userModal')).show();
        }
    });

    // Change Password
    document.addEventListener('click', function (e) {
        if (e.target.closest('.change-password')) {
            const btn = e.target.closest('.change-password');
            const id = btn.dataset.id;
            const username = btn.dataset.username;

            document.getElementById('passwordUserId').value = id;
            document.getElementById('passwordUsername').textContent = username;
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmNewPassword').value = '';
            new bootstrap.Modal(document.getElementById('passwordModal')).show();
        }
    });

    // Delete User
    document.addEventListener('click', function (e) {
        if (e.target.closest('.delete-user')) {
            if (confirm('Delete this user? This action cannot be undone.')) {
                const btn = e.target.closest('.delete-user');
                const id = btn.dataset.id;

                fetch('delete_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            loadUsers();
                            alert('User deleted successfully!');
                        } else {
                            alert('Error: ' + result.error);
                        }
                    })
                    .catch(error => {
                        alert('Failed to delete user: ' + error.message);
                    });
            }
        }
    });
});

function loadUsers() {
    fetch('get_all_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('usersList');
                if (data.users.length === 0) {
                    container.innerHTML = '<p class="text-muted text-center">No users found.</p>';
                    return;
                }

                let html = '<div class="table-responsive"><table class="table table-hover">';
                html += '<thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Created</th><th>Actions</th></tr></thead>';
                html += '<tbody>';

                data.users.forEach(user => {
                    const createdDate = new Date(user.created_at).toLocaleDateString();
                    html += `
                        <tr>
                            <td>${user.id}</td>
                            <td><strong>@${escapeHtml(user.username)}</strong></td>
                            <td>${escapeHtml(user.email)}</td>
                            <td><small class="text-muted">${createdDate}</small></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary edit-user" 
                                            data-id="${user.id}" 
                                            data-username="${escapeHtml(user.username)}" 
                                            data-email="${escapeHtml(user.email)}"
                                            title="Edit user">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-info change-password" 
                                            data-id="${user.id}" 
                                            data-username="${escapeHtml(user.username)}"
                                            title="Change password">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <button class="btn btn-outline-danger delete-user" 
                                            data-id="${user.id}"
                                            title="Delete user">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';
                container.innerHTML = html;
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

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
