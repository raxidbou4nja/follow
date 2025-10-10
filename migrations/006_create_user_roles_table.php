<?php
return [
    "CREATE TABLE user_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        role_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_role (user_id, role_id)
    )"
];
