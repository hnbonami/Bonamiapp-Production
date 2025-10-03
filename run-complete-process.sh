#!/bin/bash
# Prepare database backup directory and run complete commit

echo "🗄️  PREPARING DATABASE BACKUP & COMMIT"
echo "====================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

# Create database backup directory if it doesn't exist
if [ ! -d "database_backups" ]; then
    mkdir -p database_backups
    echo "✅ Created database_backups directory"
else
    echo "✅ Database backups directory exists"
fi

echo ""
echo "🚀 Running complete commit and backup process..."
chmod +x complete-commit-and-backup.sh
./complete-commit-and-backup.sh