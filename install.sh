#!/bin/bash

# BlockPayOption Installation Script
# This script helps with the initial setup

echo "╔════════════════════════════════════════╗"
echo "║  BlockPayOption Installation Setup     ║"
echo "╚════════════════════════════════════════╝"
echo ""

# Check if config.php exists
if [ -f "includes/config.php" ]; then
    echo "✓ Configuration file already exists"
else
    echo "→ Creating configuration file..."
    cp includes/config.example.php includes/config.php
    echo "✓ Configuration file created"
    echo "⚠ Please edit includes/config.php with your database credentials"
fi

# Check uploads directory permissions
echo ""
echo "→ Setting uploads directory permissions..."
chmod 755 uploads/
echo "✓ Uploads directory permissions set"

# Database setup instructions
echo ""
echo "╔════════════════════════════════════════╗"
echo "║  Database Setup Instructions           ║"
echo "╚════════════════════════════════════════╝"
echo ""
echo "1. Create a MySQL database:"
echo "   CREATE DATABASE blockpayoption CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo ""
echo "2. Import the schema:"
echo "   mysql -u your_username -p blockpayoption < database/schema.sql"
echo ""
echo "3. Update includes/config.php with your database credentials"
echo ""
echo "╔════════════════════════════════════════╗"
echo "║  Default Admin Credentials             ║"
echo "╚════════════════════════════════════════╝"
echo ""
echo "Username: admin"
echo "Password: admin123"
echo ""
echo "⚠ IMPORTANT: Change this password immediately after first login!"
echo ""
echo "╔════════════════════════════════════════╗"
echo "║  Access URLs                           ║"
echo "╚════════════════════════════════════════╝"
echo ""
echo "Public Site: http://localhost/blockpayoption/"
echo "Admin Panel: http://localhost/blockpayoption/admin/login.php"
echo ""
echo "✓ Setup complete! Follow the instructions above to finish installation."
