#!/bin/bash
# Run migration for link functionality

echo "🔄 Running migration to add link fields..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

# First try Laravel migration
php artisan migrate

if [ $? -eq 0 ]; then
    echo "✅ Laravel migration completed successfully!"
else
    echo "❌ Laravel migration failed, trying direct SQL..."
    
    # Fallback: Direct SQL execution
    DB_NAME="Bonamisportcoaching"
    DB_USER="Hannes"
    DB_PASS="Hannes1986"
    
    echo "Adding link_url and open_in_new_tab columns directly..."
    
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        ALTER TABLE staff_notes 
        ADD COLUMN link_url VARCHAR(500) NULL AFTER image_path,
        ADD COLUMN open_in_new_tab BOOLEAN NOT NULL DEFAULT FALSE AFTER link_url;
    "
    
    if [ $? -eq 0 ]; then
        echo "✅ Direct SQL migration completed successfully!"
        
        # Verify columns were added
        echo "Verifying new columns:"
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE staff_notes;" | grep -E "(link_url|open_in_new_tab)"
    else
        echo "❌ Direct SQL migration also failed!"
        echo "Please add these columns manually in your database tool:"
        echo "ALTER TABLE staff_notes ADD COLUMN link_url VARCHAR(500) NULL;"
        echo "ALTER TABLE staff_notes ADD COLUMN open_in_new_tab BOOLEAN NOT NULL DEFAULT FALSE;"
    fi
fi

echo ""
echo "🎉 Migration process completed!"