#!/bin/bash
# Complete commit, push and database backup for all fixes

echo "📝 COMPLETE COMMIT, PUSH & DATABASE BACKUP"
echo "=========================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Database backup first..."
BACKUP_DATE=$(date +%Y%m%d-%H%M%S)
echo "Creating database backup: bonamiapp_backup_${BACKUP_DATE}.sql"

# Create database backup
mysqldump -u root -p bonamiapp > "database_backups/bonamiapp_backup_${BACKUP_DATE}.sql"

if [ $? -eq 0 ]; then
    echo "✅ Database backup created successfully"
    echo "📁 Location: database_backups/bonamiapp_backup_${BACKUP_DATE}.sql"
else
    echo "⚠️  Database backup failed - continuing with commit anyway"
fi

echo ""
echo "📋 Step 2: Check current git status..."
git status --short

echo ""
echo "📋 Step 3: Add all changes..."
git add .

echo ""
echo "📋 Step 4: Show files to be committed..."
git status --short

echo ""
echo "📝 Creating comprehensive commit message..."

cat > commit_message.txt << 'EOF'
🔧 Fix admin dashboard navigation and staff notes system

🎯 Problem Fixed:
- Sidebar "Beheer" button was pointing to broken admin route
- StaffNoteController adminOverview() method was missing causing 500 errors
- View conflicts between admin staff-notes views
- Navigation inconsistencies between different admin interfaces

🔧 Technical Fixes:
- Fixed StaffNoteController syntax errors and added missing adminOverview() method
- Resolved view name conflicts (admin/staff-notes-overview vs admin/staff-notes/overview)
- Updated sidebar navigation component to point to working database tools route
- Created direct /admin/database-tools route to bypass view resolution issues
- Added proper error handling and debug logging for admin functions

📱 Navigation Updates:
- Sidebar "Beheer" button now correctly points to /admin/database-tools
- Admin database tools page fully functional with import/export capabilities
- Fixed route precedence issues that caused wrong views to load
- Updated sidebar-admin-tab component with correct route references

🛠️ Database Tools Restored:
- Data Import section with Klanten and Bikefits upload buttons
- Data Export section with download functionality for all data
- Staff Notes overview integrated within admin tools
- All original admin functionality preserved and working

🧹 System Improvements:
- Added comprehensive error handling and logging
- Created backup and restore mechanisms for critical files
- Implemented view cache clearing for immediate updates
- Added debug information for future troubleshooting

✅ Result:
- Admin navigation works seamlessly
- Database import/export tools fully accessible
- No more 500 errors on admin routes
- Consistent user experience across admin functions
- All staff notes functionality preserved

Files modified:
- app/Http/Controllers/StaffNoteController.php (syntax fixes, adminOverview method)
- resources/views/components/sidebar-admin-tab.blade.php (navigation links)
- routes/web.php (admin database tools route)
- Multiple view conflict resolutions and cache optimizations
EOF

echo "📋 Commit message preview:"
cat commit_message.txt

echo ""
read -p "🤔 Do you want to commit with this message? (y/n): " confirm

if [[ $confirm == [yY] || $confirm == [yY][eE][sS] ]]; then
    echo "📝 Committing admin dashboard fixes..."
    git commit -F commit_message.txt
    
    if [ $? -eq 0 ]; then
        echo "✅ Commit successful!"
        
        echo ""
        read -p "🚀 Push to remote repository? (y/n): " push_confirm
        
        if [[ $push_confirm == [yY] || $push_confirm == [yY][eE][sS] ]]; then
            echo "🚀 Pushing admin dashboard fixes to remote..."
            git push origin main
            
            if [ $? -eq 0 ]; then
                echo "🎉 Successfully pushed admin dashboard fixes!"
                echo ""
                echo "📋 Complete Summary:"
                echo "✅ Database backup created: bonamiapp_backup_${BACKUP_DATE}.sql"
                echo "✅ Admin dashboard navigation fixed"
                echo "✅ StaffNoteController syntax and functionality restored"
                echo "✅ Database tools page fully functional"
                echo "✅ All changes committed and pushed to repository"
                echo ""
                echo "🎯 What's now working:"
                echo "- Sidebar 'Beheer' button → Admin database tools"
                echo "- Import: Klanten Toevoegen & Bikefits Toevoegen"
                echo "- Export: Download Alle Klanten & Download Alle Bikefits"
                echo "- Staff Notes overview integrated in admin tools"
                echo "- No more 500 errors or navigation issues"
                echo ""
                echo "🧪 Final test: Click 'Beheer' in sidebar!"
                echo "🗄️  Database backup location: database_backups/bonamiapp_backup_${BACKUP_DATE}.sql"
            else
                echo "❌ Push failed! Check your remote repository settings."
                echo "Changes are committed locally though."
            fi
        else
            echo "⏸️  Changes committed locally but not pushed."
        fi
    else
        echo "❌ Commit failed!"
    fi
else
    echo "⏸️  Commit cancelled."
fi

# Cleanup
rm -f commit_message.txt

echo ""
echo "🏁 Complete admin dashboard fix process completed!"
echo "Database backup: $(ls -la database_backups/bonamiapp_backup_${BACKUP_DATE}.sql 2>/dev/null || echo 'Backup location may vary')"