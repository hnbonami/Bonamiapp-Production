#!/bin/bash

# Daily Database Backup Script voor Bonami App
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/Users/hannesbonami/Backups/bonami_db"
DB_NAME="Bonamisportcoaching"
DB_USER="Hannes"
DB_PASS="Hannes1986"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Create database backup met correcte credentials (zonder tablespaces om privilege errors te vermijden)
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" --single-transaction --routines --triggers --no-tablespaces > "$BACKUP_DIR/bonami_backup_$DATE.sql" 2>/dev/null

# Keep only last 30 days of backups
find "$BACKUP_DIR" -name "bonami_backup_*.sql" -mtime +30 -delete

echo "Database backup created: bonami_backup_$DATE.sql"

# Also backup the entire Laravel app
tar -czf "$BACKUP_DIR/app_backup_$DATE.tar.gz" -C /Users/hannesbonami/Herd/app/ Bonamiapp/

echo "App backup created: app_backup_$DATE.tar.gz"