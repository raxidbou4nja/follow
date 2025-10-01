document.addEventListener('DOMContentLoaded', function () {
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
            loadTests(serviceId);
        }
    });

    // Image modal
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('image-link')) {
            e.preventDefault();
            const url = e.target.dataset.url;
            const imageId = e.target.dataset.imageId; // Need to add this
            document.getElementById('modal-image').src = url;
            document.getElementById('commentImageId').value = imageId;
            loadComments(imageId);
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }
    });

    // Checkbox updates (placeholder for now)
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
            uploadFiles(files, testId);
            bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
            // Clear input
            fileInput.value = null;
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
        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image') !== -1) {
                const file = items[i].getAsFile();
                uploadFiles([file], testId);
                bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                break;
            }
        }
    });

    function uploadFiles(files, testId) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const formData = new FormData();
            formData.append('image', file);
            formData.append('test_id', testId);
            fetch('upload_image.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json()).then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                if (data.success) {
                    // Reload tests to show new images
                    const activeService = document.querySelector('.service-item.active');
                    if (activeService) {
                        loadTests(activeService.dataset.id);
                    }
                } else {
                    alert('Upload failed: ' + data.error);
                }
            });
        }
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
});

// Comment functions
function loadComments(imageId) {
    fetch(`get_comments.php?image_id=${imageId}`)
        .then(response => response.json())
        .then(comments => {
            const list = document.getElementById('comments-list');
            list.innerHTML = '';
            comments.forEach(comment => {
                const div = document.createElement('div');
                div.className = 'comment mb-2 p-2 border rounded bg-light';
                let html = `<small class="text-muted fw-bold">${new Date(comment.created_at).toLocaleString()}</small><br><span class="text-dark">${comment.comment_text}</span>`;
                if (comment.image_url) {
                    html += `<br><img src="${comment.image_url}" class="img-fluid mt-2 rounded" style="max-width: 100%;">`;
                }
                div.innerHTML = html;
                list.appendChild(div);
            });
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

function loadTests(serviceId) {
    fetch(`get_tests.php?service_id=${serviceId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('tests-container').innerHTML = html;
        });
}