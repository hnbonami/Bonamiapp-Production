#!/bin/bash
# Direct database update for mini tile size
# This bypasses the problematic migration

echo "🔧 Updating database directly..."

# Try different database access methods for Herd/DBngin
echo "Attempting to connect to database..."

# Method 1: Direct SQL via Herd (no password usually needed)
mysql -u root bonamiapp -e "ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') DEFAULT 'medium';" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Database updated successfully via method 1!"
    mysql -u root bonamiapp -e "DESCRIBE staff_notes;" | grep tile_size
else
    echo "Method 1 failed, trying method 2..."
    # Method 2: Try with empty password
    mysql -u root -p'' bonamiapp -e "ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') DEFAULT 'medium';" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo "✅ Database updated successfully via method 2!"
        mysql -u root -p'' bonamiapp -e "DESCRIBE staff_notes;" | grep tile_size
    else
        echo "❌ Could not connect to database automatically."
        echo "Please run this SQL manually in your database tool:"
        echo ""
        echo "ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') DEFAULT 'medium';"
        echo ""
        echo "You can use TablePlus, Sequel Pro, or phpMyAdmin to run this query."
    fi
fi