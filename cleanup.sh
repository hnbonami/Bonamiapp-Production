#!/bin/bash
# Clean up all added files and restore original state

echo "🗑️ Cleaning up added files..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

# Remove added CSS files
rm -f public/css/dashboard-stats.css

# Restore original dashboard view (remove any added content)
git checkout HEAD -- resources/views/dashboard.blade.php 2>/dev/null || echo "dashboard.blade.php not in git, skipping..."

# Clean up any other temporary files
rm -f *-database*.sh
rm -f *.sql
rm -f DATABASE_UPDATE_INSTRUCTIONS.md
rm -f reset-to-last-commit.sh

echo "✅ Cleanup completed"

# Check current status
echo "📋 Current git status:"
git status --porcelain