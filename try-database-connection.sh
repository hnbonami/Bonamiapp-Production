#!/bin/bash
# Comprehensive database connection script for Herd/DBngin

echo "🔍 Trying to connect to database with different methods..."

# Method 1: Direct connection without password (common for Herd)
echo "Method 1: No password"
mysql -u root bonamiapp -e "ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') DEFAULT 'medium';" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Success with Method 1!"
    mysql -u root bonamiapp -e "DESCRIBE staff_notes;" | grep tile_size
    exit 0
fi

# Method 2: Empty password
echo "Method 2: Empty password"
mysql -u root -p'' bonamiapp -e "ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') DEFAULT 'medium';" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Success with Method 2!"
    mysql -u root -p'' bonamiapp -e "DESCRIBE staff_notes;" | grep tile_size
    exit 0
fi

# Method 3: Try with socket (common for local MySQL)
echo "Method 3: Using socket"
mysql -u root --socket=/tmp/mysql.sock bonamiapp -e "ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') DEFAULT 'medium';" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Success with Method 3!"
    mysql -u root --socket=/tmp/mysql.sock bonamiapp -e "DESCRIBE staff_notes;" | grep tile_size
    exit 0
fi

# Method 4: Try Herd specific port
echo "Method 4: Herd port 3306"
mysql -u root -h 127.0.0.1 -P 3306 bonamiapp -e "ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') DEFAULT 'medium';" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Success with Method 4!"
    mysql -u root -h 127.0.0.1 -P 3306 bonamiapp -e "DESCRIBE staff_notes;" | grep tile_size
    exit 0
fi

# Method 5: Interactive password prompt
echo "Method 5: Interactive password (you'll be prompted)"
echo "Try these common passwords when prompted:"
echo "- Just press Enter (empty password)"
echo "- password"
echo "- root" 
echo "- admin"
echo ""
mysql -u root -p bonamiapp -e "ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') DEFAULT 'medium';"

if [ $? -eq 0 ]; then
    echo "✅ Success with Method 5!"
    mysql -u root -p bonamiapp -e "DESCRIBE staff_notes;" | grep tile_size
    exit 0
fi

echo "❌ All methods failed. Please check:"
echo "1. Is MySQL running?"
echo "2. Is the database name correct?"
echo "3. Check your Herd/DBngin settings"