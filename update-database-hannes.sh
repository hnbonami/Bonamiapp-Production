#!/bin/bash
# Database update script with your specific credentials

echo "🔧 Connecting to your MySQL database..."

# Your database credentials
DB_NAME="Bonamisportcoaching"
DB_USER="Hannes"
DB_PASS="Hannes1986"

echo "Attempting to update tile_size enum..."

# Try to connect and update
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') DEFAULT 'medium';"

if [ $? -eq 0 ]; then
    echo "✅ Successfully updated tile_size enum!"
    echo ""
    echo "Verifying the change:"
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE staff_notes;" | grep tile_size
    echo ""
    echo "🎉 You can now use 'mini' tile size in your dashboard!"
else
    echo "❌ Failed to update. Let's check what tables exist:"
    echo ""
    echo "Available tables in database '$DB_NAME':"
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;"
    echo ""
    echo "If you see a different table name above, we'll need to use that instead of 'staff_notes'"
fi