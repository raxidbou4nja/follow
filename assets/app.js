document.addEventListener('DOMContentLoaded', function () {
    let currentFilter = 'all';
    // Load initial services
    loadServices();

    // Service selection
    document.addEventListener('click', function (e) {
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

            console.log('Image clicked:', { url, imageId, isSolved, element: e.target });
            console.log('All dataset:', e.target.dataset);

            document.getElementById('modal-image').src = url;
            document.getElementById('commentImageId').value = imageId;
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
    });    // Checkbox updates (placeholder for now)
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
                let html = `<div class="flex-grow-1">
                    <small class="text-muted fw-bold">${new Date(comment.created_at).toLocaleString()}</small><br>
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
    fetch(`get_tests.php?service_id=${serviceId}&filter=${currentFilter}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('tests-container').innerHTML = html;
            // Add event listeners for filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const activeService = document.querySelector('.service-item.active');
                    if (activeService) {
                        loadTests(activeService.dataset.id, this.dataset.filter);
                    }
                });
            });
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
