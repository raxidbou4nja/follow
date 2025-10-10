# System Architecture: Roles & User Tagging

```
┌─────────────────────────────────────────────────────────────────────┐
│                        DATABASE LAYER                                │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌──────────┐      ┌──────────────┐      ┌─────────┐               │
│  │  roles   │      │ user_roles   │      │  users  │               │
│  ├──────────┤      ├──────────────┤      ├─────────┤               │
│  │ id       │◄─────┤ role_id (FK) │   ┌──┤ id      │               │
│  │ name     │      │ user_id (FK) │───┘  │ username│               │
│  │ descrip  │      └──────────────┘      │ password│               │
│  └──────────┘                            └─────────┘               │
│                                                                       │
│                        ┌─────────────┐                               │
│                        │    tests    │                               │
│                        ├─────────────┤                               │
│                        │ id          │                               │
│                        │ name        │                               │
│                        │ description │                               │
│                        │ tagged_users│ ← JSON: [1,2,3]              │
│                        └─────────────┘                               │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                         API LAYER (PHP)                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  Role Management          User-Role Mgmt        Test Management     │
│  ├─ get_roles.php         ├─ get_user_roles     ├─ save_test.php   │
│  ├─ save_role.php         ├─ assign_user_role   ├─ get_test.php    │
│  └─ delete_role.php       └─ remove_user_role   └─ get_tests.php   │
│                                                                       │
│  User Management                                                     │
│  └─ get_users.php                                                    │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                      FRONTEND LAYER (JS/HTML)                        │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌──────────────────┐              ┌──────────────────┐            │
│  │   index.php      │              │    roles.php     │            │
│  │  (Main App)      │              │  (Management)    │            │
│  ├──────────────────┤              ├──────────────────┤            │
│  │                  │              │                  │            │
│  │ ┌──────────────┐ │              │ ┌──────────────┐ │            │
│  │ │ Test Modal   │ │              │ │ Roles List   │ │            │
│  │ ├──────────────┤ │              │ ├──────────────┤ │            │
│  │ │ Name         │ │              │ │ Add Role     │ │            │
│  │ │ Description  │ │              │ │ Edit Role    │ │            │
│  │ │ ┌──────────┐ │ │              │ │ Delete Role  │ │            │
│  │ │ │Tag Users │ │ │              │ └──────────────┘ │            │
│  │ │ ├──────────┤ │ │              │                  │            │
│  │ │ │☑ john_doe│ │ │              │ ┌──────────────┐ │            │
│  │ │ │☑ jane    │ │ │              │ │  Users List  │ │            │
│  │ │ │☐ bob     │ │ │              │ ├──────────────┤ │            │
│  │ │ └──────────┘ │ │              │ │ @john_doe    │ │            │
│  │ └──────────────┘ │              │ │  [Admin]     │ │            │
│  │                  │              │ │ Manage Roles │ │            │
│  │ ┌──────────────┐ │              │ └──────────────┘ │            │
│  │ │ Tests Table  │ │              │                  │            │
│  │ ├──────────────┤ │              └──────────────────┘            │
│  │ │ Name         │ │                                               │
│  │ │ Description  │ │                                               │
│  │ │ @john @jane  │ │  ← Tagged users shown as badges             │
│  │ │ Passed ☑     │ │                                               │
│  │ └──────────────┘ │                                               │
│  └──────────────────┘                                               │
│                                                                       │
│  JavaScript Controllers                                              │
│  ├─ app.js (main functionality)                                     │
│  │  └─ loadUsersForTagging()                                        │
│  └─ roles.js (roles management)                                     │
│     ├─ loadRoles()                                                  │
│     ├─ loadUsers()                                                  │
│     └─ loadUserRoles()                                              │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                         DATA FLOW                                    │
└─────────────────────────────────────────────────────────────────────┘

TAGGING USERS IN TESTS:
───────────────────────
1. User opens test modal
2. JavaScript calls get_users.php → Returns all users
3. Display checkboxes for each user
4. User selects john_doe, jane_smith
5. On save: JavaScript collects IDs [2, 3]
6. POST to save_test.php with tagged_users: [2, 3]
7. PHP stores JSON string "[2,3]" in database
8. On display: get_tests.php reads JSON
9. PHP queries usernames for IDs 2 and 3
10. Display badges: [@john_doe] [@jane_smith]

ASSIGNING ROLES:
────────────────
1. User clicks "Manage Roles" for john_doe
2. JavaScript calls get_user_roles.php?user_id=2
3. API returns all roles with assignment status
4. Display checkboxes (Admin ☑, Developer ☐, etc.)
5. User checks "Developer"
6. POST to assign_user_role.php {user_id: 2, role_id: 2}
7. PHP inserts into user_roles table
8. Refresh user list showing new badge

┌─────────────────────────────────────────────────────────────────────┐
│                      FILE STRUCTURE                                  │
└─────────────────────────────────────────────────────────────────────┘

follow/
├── index.php                    Main application
├── roles.php                    Roles management page
├── login.php                    Authentication
├── migrate.php                  Database migrations
├── seed.php                     Sample data seeder
│
├── assets/
│   ├── app.js                   Main JavaScript
│   ├── roles.js                 Roles JavaScript
│   └── style.css                Styles
│
├── includes/
│   ├── config.php               DB config
│   └── connection.php           DB connection
│
├── migrations/
│   ├── 001_create_tables.php
│   ├── 002_create_users_table.php
│   ├── 003_create_comments_table.php
│   ├── 004_add_is_solved_to_images.php
│   ├── 005_create_roles_table.php          ← NEW
│   ├── 006_create_user_roles_table.php     ← NEW
│   └── 007_add_tagged_users_to_tests.php   ← NEW
│
├── API Endpoints (PHP files):
│   ├── get_roles.php            ← NEW
│   ├── save_role.php            ← NEW
│   ├── delete_role.php          ← NEW
│   ├── get_user_roles.php       ← NEW
│   ├── assign_user_role.php     ← NEW
│   ├── remove_user_role.php     ← NEW
│   ├── get_users.php            ← NEW
│   ├── save_test.php            (modified)
│   ├── get_test.php             (modified)
│   └── get_tests.php            (modified)
│
└── Documentation:
    ├── ROLES_AND_TAGGING_README.md
    ├── IMPLEMENTATION_SUMMARY.md
    ├── QUICK_REFERENCE.md
    └── ARCHITECTURE.md (this file)

┌─────────────────────────────────────────────────────────────────────┐
│                    VISUAL UI EXAMPLES                                │
└─────────────────────────────────────────────────────────────────────┘

TEST WITH TAGGED USERS:
═══════════════════════
┌────────────────────────────────────────────────────────────┐
│ Name: Login Test                                            │
├────────────────────────────────────────────────────────────┤
│ Description: Test user login functionality                 │
│ [@john_doe] [@jane_smith] [@bob_wilson]                   │
├────────────────────────────────────────────────────────────┤
│ Passed: ☑  Error: ☐  Images: View                         │
└────────────────────────────────────────────────────────────┘

USER WITH ROLES:
════════════════
┌────────────────────────────────────────────────────────────┐
│ @john_doe                                                   │
│ [Admin] [Developer]                                        │
│                                          [Manage Roles] →  │
└────────────────────────────────────────────────────────────┘

ROLE ASSIGNMENT MODAL:
══════════════════════
┌────────────────────────────────────────────────────────────┐
│ Manage Roles for @john_doe                          [X]    │
├────────────────────────────────────────────────────────────┤
│ ☑ Admin          - Full system access                     │
│ ☑ Developer      - Can create and modify tests            │
│ ☐ Tester         - Can run and report tests               │
│ ☐ Viewer         - Read-only access                       │
└────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                     KEY TECHNOLOGIES                                 │
└─────────────────────────────────────────────────────────────────────┘

Backend:  PHP 7.4+ with PDO
Database: MySQL/MariaDB
Frontend: Bootstrap 5.3, JavaScript (Vanilla)
Storage:  JSON for array data (tagged_users)
Security: Password hashing, prepared statements
```
