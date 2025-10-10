<?php
return [
    "ALTER TABLE comments ADD COLUMN user_id INT AFTER image_id",
    "ALTER TABLE comments ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL"
];
