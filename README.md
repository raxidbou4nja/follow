# Testing Platform Manager

A professional web-based testing platform for managing services, tests, and quality assurance workflows.

## Features

- 🎯 **Service Management** - Create and organize testing services
- ✅ **Test Management** - Add, edit, and track tests for each service
- 📸 **Image Upload** - Upload test screenshots and mark as solved
- 💬 **Comments System** - Collaborate with team members on test issues
- 👥 **User Management** - Admin can manage users and roles
- 🔔 **Notifications** - Get notified when tagged in tests
- 🔍 **Real-time Search** - Search services and tests instantly
- 📊 **Progress Tracking** - Visual progress indicators for each service

## Installation

### 1. Database Setup

Run the migration to create all necessary tables:

```bash
php migrate.php
```

### 2. Seed Initial Data

Populate the database with admin user, roles, and sample data:

```bash
php seed.php
```

This will create:

- Admin user (email: admin@gmail.com, password: 123456789)
- Sample users (john_doe, jane_smith, bob_wilson)
- Default roles (Admin, Developer, Tester, Viewer)
- Sample services and tests

### 3. Login

Access the application and login with:

- **Email:** admin@gmail.com
- **Password:** 123456789

## Default Admin Account

After seeding, you can login with:

- Email: `admin@gmail.com`
- Password: `123456789`

**Important:** Change the admin password after first login!

## User Roles

- **Admin** - Full system access including user and role management
- **Developer** - Can create and modify tests
- **Tester** - Can run and report tests
- **Viewer** - Read-only access

## File Structure

```
/
├── assets/
│   ├── app.js          # Main JavaScript functionality
│   ├── style.css       # Professional styling
│   ├── roles.js        # Roles management JS
│   └── users.js        # Users management JS
├── includes/
│   ├── auth.php        # Authentication functions
│   ├── config.php      # Database configuration
│   └── connection.php  # Database connection
├── migrations/
│   └── *.php           # Database migration files
├── uploads/            # Uploaded images directory
├── index.php           # Main application page
├── login.php           # Login page
├── users.php           # User management (Admin only)
├── roles.php           # Role management (Admin only)
├── seed.php            # Database seeding script
├── migrate.php         # Migration runner
└── README.md           # This file
```

## Key Files

### Core Pages

- `index.php` - Main testing dashboard
- `login.php` - User authentication
- `users.php` - User management (Admin only)
- `roles.php` - Role management (Admin only)

### API Endpoints

- `get_services.php` - Fetch all services
- `get_tests.php` - Fetch tests for a service
- `save_service.php` - Create/update service
- `save_test.php` - Create/update test
- `upload_image.php` - Upload test screenshot
- `save_comment.php` - Add comment to image
- `get_notifications.php` - Fetch user notifications

## Technologies Used

- **Backend:** PHP 7.4+
- **Database:** MySQL/MariaDB
- **Frontend:** Bootstrap 5, Vanilla JavaScript
- **Icons:** Bootstrap Icons
- **Fonts:** Inter (Google Fonts)

## Security Features

- Password hashing with PHP's password_hash()
- Prepared statements for SQL queries
- Role-based access control
- Cookie-based session management
- XSS protection through htmlspecialchars()

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

Private project - All rights reserved

## Support

For issues or questions, contact the development team.
