<?php

/**
 * Quick Setup Script
 * Run this script to set up the entire application
 */

echo "=== Testing Platform Manager - Quick Setup ===\n\n";

// Step 1: Run migrations
echo "Step 1: Running database migrations...\n";
require_once 'migrate.php';
echo "\n";

// Step 2: Seed data
echo "Step 2: Seeding initial data...\n";
require_once 'seed.php';
echo "\n";

echo "=== Setup Complete! ===\n\n";
echo "🎉 Your testing platform is ready to use!\n\n";
echo "Next steps:\n";
echo "1. Open your browser and navigate to the application\n";
echo "2. Login with:\n";
echo "   Email: admin@gmail.com\n";
echo "   Password: 123456789\n";
echo "3. Change the admin password after first login\n";
echo "4. Start managing your tests!\n";
