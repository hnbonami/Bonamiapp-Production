#!/bin/bash
# Reset files to last commit and cleanup

echo "🔄 Resetting modified files to last commit..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

# Reset the modified files according to git status
git restore app/Http/Controllers/DashboardContentController.php
git restore app/Models/DashboardContent.php
git restore resources/css/app.css

echo "✅ Modified files have been reset to last commit state"
echo ""
echo "🗑️  Cleaning up untracked files..."

# Remove untracked files
rm -f DATABASE_UPDATE_INSTRUCTIONS.md
rm -f database/migrations/2024_09_27_005500_add_mini_tile_size_to_staff_notes.php
rm -f database/migrations/2024_09_27_005501_update_tile_size_enum_add_mini.php
rm -f database/migrations/2025_09_26_225553_update_tile_size_enum_add_mini.php
rm -f find_table.sql
rm -f fix-database.sh
rm -f run-migration.sh
rm -f try-database-connection.sh
rm -f update-database-hannes.sh
rm -f update_enum.sql

echo "✅ Cleanup completed"
echo ""
echo "📋 Final status:"
git status