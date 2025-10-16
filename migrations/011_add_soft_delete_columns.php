<?php
return [
    "ALTER TABLE services ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL",
    "ALTER TABLE tests ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL",
    "ALTER TABLE test_images ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL",
    "ALTER TABLE comments ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL",
    "ALTER TABLE roles ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL",
    "ALTER TABLE user_roles ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL",
    "ALTER TABLE notifications ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL",
    "CREATE INDEX idx_services_deleted ON services(deleted_at)",
    "CREATE INDEX idx_tests_deleted ON tests(deleted_at)",
    "CREATE INDEX idx_test_images_deleted ON test_images(deleted_at)",
    "CREATE INDEX idx_users_deleted ON users(deleted_at)",
    "CREATE INDEX idx_comments_deleted ON comments(deleted_at)",
    "CREATE INDEX idx_roles_deleted ON roles(deleted_at)",
    "CREATE INDEX idx_user_roles_deleted ON user_roles(deleted_at)",
    "CREATE INDEX idx_notifications_deleted ON notifications(deleted_at)"
];
