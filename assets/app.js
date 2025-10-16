let currentFilter = 'all';

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Load initial services
    loadServices();

    // Load notifications
    loadNotifications();

    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);

    // Handle dropdown show/hide events for z-index
    document.addEventListener('show.bs.dropdown', function (e) {
        const serviceItem = e.target.closest('.service-item');
        if (serviceItem) {
            serviceItem.style.zIndex = '1060';
        }
    });

    document.addEventListener('hide.bs.dropdown', function (e) {
        const serviceItem = e.target.closest('.service-item');
        if (serviceItem) {
            // Reset z-index after animation
            setTimeout(() => {
                if (serviceItem.classList.contains('active')) {
                    serviceItem.style.zIndex = '2';
                } else {
                    serviceItem.style.zIndex = '1';
                }
            }, 150);
        }
    });

    // Service Search Functionality
    document.getElementById('service-search').addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase();
        const serviceItems = document.querySelectorAll('.service-item');

        serviceItems.forEach(item => {
            const serviceName = item.querySelector('.service-name');
            if (serviceName) {
                const nameText = serviceName.textContent.toLowerCase();
                if (nameText.includes(searchTerm)) {
                    item.style.setProperty('display', 'flex', 'important');
                } else {
                    item.style.setProperty('display', 'none', 'important');
                }
            }
        });
    });

    // Test Search Functionality (Inline)
    document.addEventListener('input', function (e) {
        if (e.target.id === 'test-search-inline') {
            const searchTerm = e.target.value.toLowerCase();
            const testRows = document.querySelectorAll('#tests-container tbody tr');

            testRows.forEach(row => {
                const testName = row.cells[0].textContent.toLowerCase();
                const testDescription = row.cells[1].textContent.toLowerCase();

                if (testName.includes(searchTerm) || testDescription.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    });

    // Service selection
    // Notification handlers
    document.getElementById('mark-all-read').addEventListener('click', function () {
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mark_all: true })
        }).then(() => {
            loadNotifications();
        });
    });

    document.addEventListener('click', function (e) {
        // Prevent service selection when clicking dropdown or its menu items
        if (e.target.closest('.dropdown') || e.target.closest('.dropdown-menu')) {
            return;
        }

        if ((e.target.classList.contains('service-item') || e.target.closest('.service-item')) && !e.target.closest('button')) {
            const item = e.target.classList.contains('service-item') ? e.target : e.target.closest('.service-item');
            // Remove active class from all
            document.querySelectorAll('.service-item').forEach(i => i.classList.remove('active'));
            // Add to clicked
            item.classList.add('active');
            const serviceId = item.dataset.id;
            currentFilter = 'all';
            loadTests(serviceId);
        }
    });

    // Image modal
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('image-link')) {
            e.preventDefault();
            const url = e.target.dataset.url;
            const imageId = e.target.dataset.imageId;
            const isSolved = e.target.dataset.isSolved == '1';
            const testName = e.target.dataset.testName || 'Image';

            console.log('Image clicked:', { url, imageId, isSolved, testName, element: e.target });
            console.log('All dataset:', e.target.dataset);

            document.getElementById('modal-image').src = url;
            document.getElementById('commentImageId').value = imageId;
            document.querySelector('#imageModal .modal-title').textContent = testName;
            updateSolvedButton(isSolved);
            loadComments(imageId);
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }
    });

    // Toggle solved status
    document.getElementById('toggleSolvedBtn').addEventListener('click', function () {
        const imageId = document.getElementById('commentImageId').value;
        const button = this;

        // Get current solved state from the button's appearance
        // If button is green (btn-success), it means "Mark as Solved" -> current state is unsolved
        // If button is yellow (btn-warning), it means "Mark as Unsolved" -> current state is solved
        const currentState = button.classList.contains('btn-warning'); // true if already solved
        const newState = !currentState;

        console.log('Toggling solved status:', { imageId, currentState, newState });

        // Validate imageId
        if (!imageId || imageId === '' || imageId === 'undefined') {
            alert('Error: No image ID found. Please close the modal and click on an image link again.');
            return;
        }

        fetch('update_image_solved.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image_id: parseInt(imageId), is_solved: newState })
        }).then(response => {
            console.log('Response status:', response.status);
            return response.json();
        }).then(data => {
            console.log('Response data:', data);
            if (data.success) {
                updateSolvedButton(newState);
                // Reload tests to update the View link color
                const activeService = document.querySelector('.service-item.active');
                if (activeService) {
                    loadTests(activeService.dataset.id);
                }
            } else {
                alert('Error updating status: ' + (data.error || 'Unknown error'));
            }
        }).catch(error => {
            console.error('Fetch error:', error);
            alert('Network error: ' + error.message);
        });
    });

    // Delete image button
    document.getElementById('deleteImageBtn').addEventListener('click', function () {
        if (confirm('Delete this image?')) {
            const imageId = document.getElementById('commentImageId').value;

            fetch('delete_image.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: imageId })
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('imageModal')).hide();
                    // Reload tests
                    const activeService = document.querySelector('.service-item.active');
                    if (activeService) {
                        loadTests(activeService.dataset.id);
                    }
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete image'));
                }
            }).catch(error => {
                alert('Delete failed: ' + error.message);
            });
        }
    });
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('is-passed') || e.target.classList.contains('has-error')) {
            const testId = e.target.closest('tr').dataset.testId;
            const field = e.target.classList.contains('is-passed') ? 'is_passed' : 'has_error';
            const value = e.target.checked ? 1 : 0;
            // AJAX to update
            fetch('update_test.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ test_id: testId, field: field, value: value })
            }).then(() => {
                // Update progress bar dynamically
                updateProgressBar();
            });
        }
    });

    // Upload button
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('upload-btn') || e.target.closest('.upload-btn')) {
            const testId = e.target.dataset.testId || e.target.closest('.upload-btn').dataset.testId;
            document.getElementById('uploadTestId').value = testId;
            new bootstrap.Modal(document.getElementById('uploadModal')).show();
        }
    });

    // Upload modal interactions
    document.getElementById('uploadImageBtn').addEventListener('click', function () {
        const testId = document.getElementById('uploadTestId').value;
        const fileInput = document.getElementById('fileInput');
        const files = fileInput.files;
        if (files.length > 0) {
            uploadFiles(files, testId, this);
        } else {
            alert('Please select a file or paste an image.');
        }
    });

    // Paste area
    document.getElementById('pasteArea').addEventListener('click', function () {
        this.focus();
    });

    document.getElementById('pasteArea').addEventListener('paste', function (e) {
        const testId = document.getElementById('uploadTestId').value;
        const items = e.clipboardData.items;
        const uploadBtn = document.getElementById('uploadImageBtn');
        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image') !== -1) {
                const file = items[i].getAsFile();
                uploadFiles([file], testId, uploadBtn);
                break;
            }
        }
    });

    function uploadFiles(files, testId, button) {
        // Disable button and show loading state
        const originalText = button.textContent;
        button.textContent = 'Uploading...';
        button.disabled = true;

        let uploadPromises = [];
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const formData = new FormData();
            formData.append('image', file);
            formData.append('test_id', testId);

            const promise = fetch('upload_image.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json()).then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    return false;
                }
                if (data.success) {
                    return true;
                } else {
                    alert('Upload failed: ' + data.error);
                    return false;
                }
            }).catch(error => {
                alert('Upload failed: ' + error.message);
                return false;
            });

            uploadPromises.push(promise);
        }

        // Wait for all uploads to complete
        Promise.all(uploadPromises).then(results => {
            // Restore button state
            button.textContent = originalText;
            button.disabled = false;

            // Check if at least one upload was successful
            if (results.some(result => result === true)) {
                // Reload tests to show new images
                const activeService = document.querySelector('.service-item.active');
                if (activeService) {
                    loadTests(activeService.dataset.id);
                }

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();

                // Clear file input
                const fileInput = document.getElementById('fileInput');
                fileInput.value = null;
            }
        });
    }

    // Service CRUD
    document.getElementById('add-service-btn').addEventListener('click', function () {
        document.getElementById('serviceId').value = '';
        document.getElementById('serviceName').value = '';
        document.getElementById('serviceDescription').value = '';
        document.getElementById('serviceModalLabel').textContent = 'Add Service';
        new bootstrap.Modal(document.getElementById('serviceModal')).show();
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-service') || e.target.closest('.edit-service')) {
            e.preventDefault();
            const id = e.target.dataset.id || e.target.closest('.edit-service').dataset.id;
            fetch(`get_service.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    document.getElementById('serviceId').value = data.id;
                    document.getElementById('serviceName').value = data.name;
                    document.getElementById('serviceDescription').value = data.description;
                    document.getElementById('serviceModalLabel').textContent = 'Edit Service';
                    new bootstrap.Modal(document.getElementById('serviceModal')).show();
                });
        } else if (e.target.classList.contains('delete-service') || e.target.closest('.delete-service')) {
            e.preventDefault();
            if (confirm('Delete this service?')) {
                const id = e.target.dataset.id || e.target.closest('.delete-service').dataset.id;
                fetch('delete_service.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                }).then(() => {
                    loadServices();
                    // Clear tests if deleted service was active
                    const activeService = document.querySelector('.service-item.active');
                    if (activeService && activeService.dataset.id == id) {
                        document.getElementById('tests-container').innerHTML = '<p>Select a service to view tests.</p>';
                    }
                });
            }
        }
    });

    document.getElementById('serviceForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        fetch('save_service.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(() => {
            loadServices();
            bootstrap.Modal.getInstance(document.getElementById('serviceModal')).hide();
            // Clear form
            document.getElementById('serviceId').value = '';
            document.getElementById('serviceName').value = '';
            document.getElementById('serviceDescription').value = '';
        });
    });

    // Test CRUD
    document.addEventListener('click', function (e) {
        if (e.target.id === 'add-test-btn') {
            const serviceId = e.target.dataset.serviceId;
            document.getElementById('testId').value = '';
            document.getElementById('testServiceId').value = serviceId;
            document.getElementById('testName').value = '';
            document.getElementById('testDescription').value = '';
            document.getElementById('testModalLabel').textContent = 'Add Test';
            loadUsersForTagging();
            new bootstrap.Modal(document.getElementById('testModal')).show();
        } else if (e.target.classList.contains('edit-test') || e.target.closest('.edit-test')) {
            const id = e.target.dataset.id || e.target.closest('.edit-test').dataset.id;
            fetch(`get_test.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    document.getElementById('testId').value = data.id;
                    document.getElementById('testServiceId').value = data.service_id;
                    document.getElementById('testName').value = data.name;
                    document.getElementById('testDescription').value = data.description;
                    document.getElementById('testModalLabel').textContent = 'Edit Test';
                    loadUsersForTagging(data.tagged_users || []);
                    new bootstrap.Modal(document.getElementById('testModal')).show();
                });
        } else if (e.target.classList.contains('delete-test') || e.target.closest('.delete-test')) {
            if (confirm('Delete this test?')) {
                const id = e.target.dataset.id || e.target.closest('.delete-test').dataset.id;
                fetch('delete_test.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                }).then(() => {
                    const activeService = document.querySelector('.service-item.active');
                    if (activeService) {
                        loadTests(activeService.dataset.id);
                    }
                });
            }
        }
    });

    document.getElementById('testForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        // Collect selected tagged users
        const taggedUsers = [];
        document.querySelectorAll('input[name="tagged_users[]"]:checked').forEach(checkbox => {
            taggedUsers.push(parseInt(checkbox.value));
        });
        data.tagged_users = taggedUsers;

        fetch('save_test.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(() => {
            const activeService = document.querySelector('.service-item.active');
            if (activeService) {
                loadTests(activeService.dataset.id);
            }
            bootstrap.Modal.getInstance(document.getElementById('testModal')).hide();
            // Clear form
            document.getElementById('testId').value = '';
            document.getElementById('testServiceId').value = '';
            document.getElementById('testName').value = '';
            document.getElementById('testDescription').value = '';
        });
    });

    // CSV Upload
    document.getElementById('upload-csv-btn').addEventListener('click', function () {
        new bootstrap.Modal(document.getElementById('csvModal')).show();
    });

    document.getElementById('csvForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('upload_csv.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            if (data.success) {
                alert('CSV imported successfully');
                loadServices();
                const activeService = document.querySelector('.service-item.active');
                if (activeService) {
                    loadTests(activeService.dataset.id);
                }
                bootstrap.Modal.getInstance(document.getElementById('csvModal')).hide();
                // Clear form
                document.getElementById('csvFile').value = null;
                document.getElementById('csvType').value = 'services';
            } else {
                alert('Import failed: ' + data.error);
            }
        });
    });

    // Comment form
    document.getElementById('commentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const button = this.querySelector('button[type="submit"]');
        const originalText = button.textContent;
        button.textContent = 'Uploading...';
        button.disabled = true;

        const formData = new FormData();
        formData.append('image_id', document.getElementById('commentImageId').value);
        formData.append('comment_text', document.getElementById('commentText').value);
        const imageFile = document.getElementById('commentImage').files[0];
        if (imageFile) {
            formData.append('comment_image', imageFile);
        }
        fetch('save_comment.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            button.textContent = originalText;
            button.disabled = false;
            if (data.success) {
                document.getElementById('commentText').value = '';
                document.getElementById('commentImage').value = null;
                loadComments(document.getElementById('commentImageId').value);
            } else {
                alert('Error: ' + (data.error || 'Failed to add comment'));
            }
        }).catch(error => {
            button.textContent = originalText;
            button.disabled = false;
            alert('Upload failed: ' + error.message);
        });
    });

    // Delete comment
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-comment') || e.target.closest('.delete-comment')) {
            if (confirm('Delete this comment?')) {
                const commentId = e.target.dataset.commentId || e.target.closest('.delete-comment').dataset.commentId;
                fetch('delete_comment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `comment_id=${commentId}`
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        loadComments(document.getElementById('commentImageId').value);
                    } else {
                        alert('Error: ' + (data.error || 'Failed to delete comment'));
                    }
                }).catch(error => {
                    alert('Delete failed: ' + error.message);
                });
            }
        }
    });

    // Edit comment - click on text or edit button
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('editable-comment') || e.target.classList.contains('edit-comment') || e.target.closest('.edit-comment')) {
            const commentId = e.target.dataset.commentId || e.target.closest('.edit-comment').dataset.commentId;
            const commentSpan = document.querySelector(`.editable-comment[data-comment-id="${commentId}"]`);
            if (commentSpan) {
                startEditingComment(commentSpan, commentId);
            }
        }
    });

    // Handle comment editing
    function startEditingComment(commentSpan, commentId) {
        const originalText = commentSpan.textContent;
        const textarea = document.createElement('textarea');
        textarea.className = 'form-control form-control-sm';
        textarea.value = originalText;
        textarea.style.resize = 'none';
        textarea.style.minHeight = '60px';
        textarea.style.whiteSpace = 'pre-wrap';

        // Replace span with textarea
        commentSpan.parentNode.replaceChild(textarea, commentSpan);
        textarea.focus();
        textarea.select();

        // Handle save on Enter (without Shift) or blur
        textarea.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                saveCommentEdit(textarea, commentId, originalText);
            } else if (e.key === 'Escape') {
                cancelCommentEdit(textarea, commentId, originalText);
            }
        });

        textarea.addEventListener('blur', function () {
            saveCommentEdit(textarea, commentId, originalText);
        });
    }

    function saveCommentEdit(textarea, commentId, originalText) {
        const newText = textarea.value.trim();
        if (newText === originalText) {
            // No changes, just cancel
            cancelCommentEdit(textarea, commentId, originalText);
            return;
        }

        if (newText === '') {
            alert('Comment cannot be empty');
            textarea.focus();
            return;
        }

        // Save to server
        fetch('update_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `comment_id=${commentId}&comment_text=${encodeURIComponent(newText)}`
        }).then(response => response.json()).then(data => {
            if (data.success) {
                // Replace textarea with updated span
                const span = document.createElement('span');
                span.className = 'comment-text text-dark editable-comment';
                span.dataset.commentId = commentId;
                span.style.cursor = 'pointer';
                span.style.whiteSpace = 'pre-line';
                span.textContent = newText;
                textarea.parentNode.replaceChild(span, textarea);
            } else {
                alert('Error: ' + (data.error || 'Failed to update comment'));
                cancelCommentEdit(textarea, commentId, originalText);
            }
        }).catch(error => {
            alert('Update failed: ' + error.message);
            cancelCommentEdit(textarea, commentId, originalText);
        });
    }

    function cancelCommentEdit(textarea, commentId, originalText) {
        const span = document.createElement('span');
        span.className = 'comment-text text-dark editable-comment';
        span.dataset.commentId = commentId;
        span.style.cursor = 'pointer';
        span.style.whiteSpace = 'pre-line';
        span.textContent = originalText;
        textarea.parentNode.replaceChild(span, textarea);
    }
});

// Comment functions
function loadComments(imageId) {
    const list = document.getElementById('comments-list');

    // Show loading spinner
    list.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading comments...</p>
        </div>
    `;

    fetch(`get_comments.php?image_id=${imageId}`)
        .then(response => response.json())
        .then(comments => {
            list.innerHTML = '';

            if (comments.length === 0) {
                list.innerHTML = '<p class="text-muted text-center py-3">No comments yet. Be the first to comment!</p>';
                return;
            }

            comments.forEach(comment => {
                const div = document.createElement('div');
                div.className = 'comment mb-2 p-2 border rounded bg-light d-flex justify-content-between align-items-start';

                // Display username and email if available
                const userInfo = comment.username
                    ? `<span class="badge bg-secondary me-2">@${comment.username}</span>`
                    : '';
                const emailInfo = '';

                let html = `<div class="flex-grow-1">
                    <div class="mb-1">
                        ${userInfo}
                        <small class="text-muted fw-bold ms-2">${new Date(comment.created_at).toLocaleString()}</small>
                    </div>
                    <span class="comment-text text-dark editable-comment" data-comment-id="${comment.id}" style="cursor: pointer; white-space: pre-line;">${comment.comment_text}</span>`;
                if (comment.image_url) {
                    html += `<br><img src="${comment.image_url}" class="img-fluid mt-2 rounded" style="max-width: 100%;">`;
                }
                html += `</div>
                    <div class="btn-group btn-group-sm ms-2" role="group">
                        <button class="btn btn-outline-secondary edit-comment" data-comment-id="${comment.id}" title="Edit comment" style="padding: 0.1rem 0.3rem; font-size: 0.75rem;">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger delete-comment" data-comment-id="${comment.id}" title="Delete comment" style="padding: 0.1rem 0.3rem; font-size: 0.75rem;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>`;
                div.innerHTML = html;
                list.appendChild(div);
            });
        })
        .catch(error => {
            list.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> Failed to load comments. Please try again.
                </div>
            `;
            console.error('Error loading comments:', error);
        });
}

// Helper functions
function loadServices() {
    fetch('get_services.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('services-list').innerHTML = html;
        });
}

function loadTests(serviceId, newFilter = null) {
    if (newFilter !== null) {
        currentFilter = newFilter;
    }

    // Show loading status
    const testsContainer = document.getElementById('tests-container');
    testsContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading tests...</p>
        </div>
    `;

    fetch(`get_tests.php?service_id=${serviceId}&filter=${currentFilter}`)
        .then(response => response.text())
        .then(html => {
            testsContainer.innerHTML = html;
            // Add event listeners for filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const activeService = document.querySelector('.service-item.active');
                    if (activeService) {
                        loadTests(activeService.dataset.id, this.dataset.filter);
                    }
                });
            });
        })
        .catch(error => {
            testsContainer.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> Failed to load tests. Please try again.
                </div>
            `;
            console.error('Error loading tests:', error);
        });
}

function updateSolvedButton(isSolved) {
    const button = document.getElementById('toggleSolvedBtn');
    if (isSolved) {
        button.classList.remove('btn-success');
        button.classList.add('btn-warning');
        button.innerHTML = '<i class="bi bi-x-circle"></i> Mark as Unsolved';
    } else {
        button.classList.remove('btn-warning');
        button.classList.add('btn-success');
        button.innerHTML = '<i class="bi bi-check-circle"></i> Mark as Solved';
    }
}

function updateProgressBar() {
    // Count checked passed checkboxes
    const totalTests = document.querySelectorAll('.is-passed').length;
    const solvedTests = document.querySelectorAll('.is-passed:checked').length;

    const progressBar = document.querySelector('.progress-bar');
    const badge = document.querySelector('.badge');

    if (progressBar && badge) {
        const percentage = totalTests > 0 ? Math.round((solvedTests / totalTests) * 100) : 0;

        // Update progress bar with animation
        progressBar.style.width = percentage + '%';
        progressBar.innerHTML = '<small class="text-white fw-bold">' + percentage + '%</small>';

        // Update progress bar color based on percentage
        progressBar.className = 'progress-bar';
        if (percentage >= 80) {
            progressBar.classList.add('bg-success');
        } else if (percentage >= 50) {
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.add('bg-danger');
        }

        // Update badge
        badge.textContent = solvedTests + '/' + totalTests + ' solved';

        // Add a subtle animation effect
        progressBar.style.transition = 'width 0.3s ease-in-out';
    }
}

function loadUsersForTagging(selectedUsers = []) {
    fetch('get_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.users) {
                const container = document.getElementById('taggedUsersContainer');
                container.innerHTML = '';

                if (data.users.length === 0) {
                    container.innerHTML = '<p class="text-muted">No users available</p>';
                    return;
                }

                data.users.forEach(user => {
                    const isChecked = selectedUsers.includes(user.id);
                    const checkbox = document.createElement('div');
                    checkbox.className = 'form-check';
                    checkbox.innerHTML = `
                        <input class="form-check-input" type="checkbox" name="tagged_users[]" 
                               value="${user.id}" id="user_${user.id}" ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label" for="user_${user.id}">
                            @${user.username}
                        </label>
                    `;
                    container.appendChild(checkbox);
                });
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            document.getElementById('taggedUsersContainer').innerHTML =
                '<p class="text-danger">Failed to load users</p>';
        });
}

function loadNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('notification-badge');
                const list = document.getElementById('notification-list');

                // Update badge
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }

                // Update notification list
                list.innerHTML = '';

                if (data.notifications.length === 0) {
                    list.innerHTML = '<div class="text-center text-muted p-3">No notifications</div>';
                    return;
                }

                data.notifications.forEach(notif => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'dropdown-item notification-item py-2 px-3' + (notif.is_read == 0 ? ' unread-notification' : '');
                    item.dataset.notificationId = notif.id;
                    item.dataset.testId = notif.test_id;
                    item.dataset.serviceId = notif.service_id;

                    const timeAgo = formatTimeAgo(notif.created_at);

                    item.innerHTML = `
                        <div class="d-flex">
                            ${notif.is_read == 0 ? '<div class="me-2"><span class="badge bg-primary rounded-circle" style="width: 8px; height: 8px;">&nbsp;</span></div>' : '<div class="me-2" style="width: 8px;"></div>'}
                            <div class="flex-grow-1">
                                <div class="fw-bold">${notif.message}</div>
                                <small class="text-muted">in ${notif.service_name} • ${timeAgo}</small>
                            </div>
                        </div>
                    `;

                    item.addEventListener('click', function (e) {
                        e.preventDefault();
                        handleNotificationClick(notif.id, notif.test_id, notif.service_id);
                    });

                    list.appendChild(item);
                });
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

function handleNotificationClick(notificationId, testId, serviceId) {
    // Mark notification as read
    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notification_id: notificationId })
    }).then(() => {
        // Reload notifications to update badge
        loadNotifications();

        // Close dropdown
        const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('notification-btn'));
        if (dropdown) {
            dropdown.hide();
        }

        // Load the service
        const serviceItem = document.querySelector(`.service-item[data-id="${serviceId}"]`);
        if (serviceItem) {
            document.querySelectorAll('.service-item').forEach(i => i.classList.remove('active'));
            serviceItem.classList.add('active');

            // Load tests and then highlight the specific test
            currentFilter = 'all';
            fetch(`get_tests.php?service_id=${serviceId}&filter=${currentFilter}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('tests-container').innerHTML = html;

                    // Add filter button listeners
                    document.querySelectorAll('.filter-btn').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const activeService = document.querySelector('.service-item.active');
                            if (activeService) {
                                loadTests(activeService.dataset.id, this.dataset.filter);
                            }
                        });
                    });

                    // Highlight and scroll to the test
                    setTimeout(() => {
                        const testRow = document.querySelector(`tr[data-test-id="${testId}"]`);
                        if (testRow) {
                            testRow.classList.add('highlighted-test');
                            testRow.scrollIntoView({ behavior: 'smooth', block: 'center' });

                            // Remove highlight after 3 seconds
                            setTimeout(() => {
                                testRow.classList.remove('highlighted-test');
                            }, 3000);
                        }
                    }, 100);
                });
        }
    });
}

function formatTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const seconds = Math.floor((now - date) / 1000);

    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}d ago`;
    const weeks = Math.floor(days / 7);
    if (weeks < 4) return `${weeks}w ago`;
    return date.toLocaleDateString();
}
