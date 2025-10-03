#!/bin/bash

# Quick restore naar stabiele versie
echo "🚨 RESTORING TO STABLE VERSION..."
echo "This will:"
echo "1. Reset code to v1.0-stable tag"
echo "2. Restore database from latest backup"
echo ""
read -p "Continue? (y/N): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    cd /Users/hannesbonami/Herd/app/Bonamiapp
    
    # Reset code
    echo "🔄 Resetting code to stable version..."
    git reset --hard v1.0-stable
    
    # Find latest backup
    BACKUP_DIR="/Users/hannesbonami/Herd/app/Bonamiapp/backups"
    LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/db_backup_*.sql 2>/dev/null | head -n1)
    
    if [ -n "$LATEST_BACKUP" ]; then
        echo "🔄 Restoring database from: $LATEST_BACKUP"
        mysql -u root bonamiapp < "$LATEST_BACKUP"
        echo "✅ Restore completed!"
        echo "🌐 Visit: https://bonamiapp.test/dashboard"
        echo "👤 Login: info@bonami-sportcoaching.be / password"
    else
        echo "❌ No backup found in $BACKUP_DIR"
        echo "You may need to run: php artisan migrate:fresh"
    fi
else
    echo "❌ Restore cancelled"
fi