#!/bin/bash

# Verbeterd Database Backup Script voor Bonami App
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/Users/hannesbonami/Backups/bonami_db"
DB_NAME="Bonamisportcoaching"
DB_USER="Hannes"
DB_PASS="Hannes1986"

echo "🚀 Bonami Database Backup gestart..."

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"
echo "📁 Backup directory: $BACKUP_DIR"

# Create database backup (veiligere methode)
echo "💾 Database backup maken..."
if mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    --single-transaction \
    --routines \
    --triggers \
    --no-tablespaces \
    --skip-lock-tables > "$BACKUP_DIR/bonami_backup_$DATE.sql" 2>/dev/null; then
    
    echo "✅ Database backup succesvol: bonami_backup_$DATE.sql"
    echo "📊 Backup grootte: $(du -h "$BACKUP_DIR/bonami_backup_$DATE.sql" | cut -f1)"
else
    echo "❌ Database backup gefaald"
    exit 1
fi

# Git status check
echo "📋 Git status:"
if git status --porcelain | head -5; then
    echo "💡 Tip: Vergeet niet je code wijzigingen te committen!"
fi

# Keep only last 30 days of backups
DELETED=$(find "$BACKUP_DIR" -name "bonami_backup_*.sql" -mtime +30 -delete -print | wc -l)
if [ "$DELETED" -gt 0 ]; then
    echo "🧹 $DELETED oude backups opgeruimd"
fi

# Also backup storage (uploads)
echo "📂 App storage backup maken..."
if [ -d "storage/app/public" ]; then
    tar -czf "$BACKUP_DIR/storage_backup_$DATE.tar.gz" storage/app/public/ 2>/dev/null
    echo "✅ Storage backup succesvol: storage_backup_$DATE.tar.gz"
fi

echo ""
echo "🎉 Backup voltooid!"
echo "📍 Locatie: $BACKUP_DIR"
echo "📊 Recente backups:"
ls -lt "$BACKUP_DIR"/*.sql 2>/dev/null | head -3