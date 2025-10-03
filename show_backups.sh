#!/bin/bash

# Restore script voor Bonami App
BACKUP_DIR="/Users/hannesbonami/Herd/app/Bonamiapp/backups"
DB_NAME="bonamiapp"

echo "🔍 Available backups:"
ls -la "$BACKUP_DIR"/*.sql 2>/dev/null || echo "No SQL backups found"

echo ""
echo "📂 Git tags:"
git tag -l

echo ""
echo "To restore database:"
echo "1. Choose a backup file from above"
echo "2. Run: mysql -u root $DB_NAME < BACKUP_FILE.sql"
echo ""
echo "To restore code:"
echo "1. git reset --hard [COMMIT_HASH or TAG]"
echo "2. git reset --hard v1.0-stable  (for stable version)"
echo ""
echo "Quick restore to stable:"
echo "bash restore_stable.sh"