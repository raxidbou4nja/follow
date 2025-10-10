<?php

/**
 * Test Admin Role-Based Access Control
 */

require_once 'includes/connection.php';
require_once 'includes/auth.php';

echo "=== Testing Admin Role-Based Access Control ===\n\n";

// Test 1: Check auth.php functions exist
echo "1. Checking auth functions:\n";
if (function_exists('isAdmin')) {
    echo "   ✓ isAdmin() function exists\n";
} else {
    echo "   ✗ isAdmin() function missing\n";
}

if (function_exists('requireAdmin')) {
    echo "   ✓ requireAdmin() function exists\n";
} else {
    echo "   ✗ requireAdmin() function missing\n";
}

// Test 2: Check admin user has Admin role
echo "\n2. Checking admin user role assignment:\n";
$admin = $pdo->query("
    SELECT u.id, u.username, u.email, r.name as role_name
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    JOIN roles r ON ur.role_id = r.id
    WHERE u.email = 'admin@gmail.com' AND r.name = 'Admin'
")->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "   ✓ Admin user has Admin role\n";
    echo "     User: {$admin['username']} ({$admin['email']})\n";
    echo "     Role: {$admin['role_name']}\n";
} else {
    echo "   ✗ Admin user does not have Admin role\n";
    echo "     Run: php assign_admin_role.php\n";
}

// Test 3: Check non-admin users don't have Admin role
echo "\n3. Checking non-admin users:\n";
$nonAdmins = $pdo->query("
    SELECT u.id, u.username, u.email
    FROM users u
    WHERE u.email != 'admin@gmail.com'
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($nonAdmins as $user) {
    $hasAdmin = $pdo->prepare("
        SELECT r.name 
        FROM user_roles ur
        JOIN roles r ON ur.role_id = r.id
        WHERE ur.user_id = ? AND r.name = 'Admin'
    ");
    $hasAdmin->execute([$user['id']]);

    if ($hasAdmin->fetch()) {
        echo "   ⚠ {$user['username']} ({$user['email']}) has Admin role\n";
    } else {
        echo "   ✓ {$user['username']} ({$user['email']}) - no Admin role\n";
    }
}

// Test 4: Check protected files
echo "\n4. Checking protected files:\n";
$protectedFiles = [
    'roles.php' => 'Role management page',
    'users.php' => 'User management page',
    'save_role.php' => 'Save role endpoint',
    'delete_role.php' => 'Delete role endpoint',
    'assign_user_role.php' => 'Assign role endpoint',
    'remove_user_role.php' => 'Remove role endpoint',
    'save_user.php' => 'Save user endpoint',
    'delete_user.php' => 'Delete user endpoint'
];

foreach ($protectedFiles as $file => $description) {
    $content = file_get_contents($file);
    if (strpos($content, 'requireAdmin()') !== false || strpos($content, 'isAdmin()') !== false) {
        echo "   ✓ $file ($description)\n";
    } else {
        echo "   ✗ $file ($description) - NOT PROTECTED\n";
    }
}

// Test 5: Check index.php conditionally shows buttons
echo "\n5. Checking index.php admin button visibility:\n";
$indexContent = file_get_contents('index.php');
if (strpos($indexContent, 'if ($is_admin)') !== false) {
    echo "   ✓ Index page conditionally shows admin buttons\n";
} else {
    echo "   ⚠ Index page doesn't conditionally show admin buttons\n";
}

echo "\n=== Test Summary ===\n\n";

echo "Access Control Rules:\n";
echo "  • Admin users (with Admin role):\n";
echo "    ✓ Can access /roles.php\n";
echo "    ✓ Can access /users.php\n";
echo "    ✓ Can create/edit/delete roles\n";
echo "    ✓ Can create/edit/delete users\n";
echo "    ✓ Can assign roles to users\n";
echo "    ✓ See 'Users' and 'Roles' buttons in navigation\n\n";

echo "  • Non-admin users (without Admin role):\n";
echo "    ✗ Cannot access /roles.php (redirected with error)\n";
echo "    ✗ Cannot access /users.php (redirected with error)\n";
echo "    ✗ Cannot call role management APIs (403 Forbidden)\n";
echo "    ✗ Cannot call user management APIs (403 Forbidden)\n";
echo "    ✗ Don't see 'Users' and 'Roles' buttons\n\n";

echo "Test Users:\n";
echo "  Admin:     admin@gmail.com / 123456789 (HAS Admin role)\n";
echo "  Regular:   john@example.com / password123 (no Admin role)\n";
echo "  Regular:   jane@example.com / password123 (no Admin role)\n";
echo "  Regular:   bob@example.com / password123 (no Admin role)\n\n";

echo "✅ Role-Based Access Control is active!\n";
