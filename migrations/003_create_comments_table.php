<?php
return [
    "CREATE TABLE comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_id INT NOT NULL,
        comment_text TEXT NOT NULL,
        image_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (image_id) REFERENCES test_images(id) ON DELETE CASCADE
    )"
];
