#!/bin/bash

# Bonamiapp Backup Script
# Gebruik: ./backup.sh

echo "🚀 Bonamiapp Backup gestart..."

# Variabelen
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="storage/backups"
PROJECT_NAME="bonamiapp"

# Maak backup directory aan
echo "📁 Backup directory aanmaken..."
mkdir -p $BACKUP_DIR

# Database backup
echo "💾 Database backup maken..."
if command -v mysqldump &> /dev/null; then
    # Probeer eerst met sail (Laravel Sail)
    if docker ps | grep -q sail; then
        echo "🐳 Laravel Sail gevonden, gebruik Sail..."
        ./vendor/bin/sail exec mysql mysqldump -u sail -psail $PROJECT_NAME > $BACKUP_DIR/db_backup_$DATE.sql
    else
        # Probeer lokale MySQL
        echo "🔧 Lokale MySQL gebruikt..."
        mysqldump -u root -p$PROJECT_NAME $PROJECT_NAME > $BACKUP_DIR/db_backup_$DATE.sql 2>/dev/null || \
        mysqldump -u root $PROJECT_NAME > $BACKUP_DIR/db_backup_$DATE.sql 2>/dev/null || \
        mysqldump -u $PROJECT_NAME -p $PROJECT_NAME > $BACKUP_DIR/db_backup_$DATE.sql
    fi
    
    if [ $? -eq 0 ]; then
        echo "✅ Database backup succesvol: $BACKUP_DIR/db_backup_$DATE.sql"
    else
        echo "❌ Database backup gefaald"
    fi
else
    echo "⚠️  mysqldump niet gevonden, database backup overgeslagen"
fi

# Storage backup (uploads)
echo "📂 Storage bestanden backup maken..."
if [ -d "storage/app/public" ]; then
    tar -czf $BACKUP_DIR/storage_backup_$DATE.tar.gz storage/app/public/ 2>/dev/null
    if [ $? -eq 0 ]; then
        echo "✅ Storage backup succesvol: $BACKUP_DIR/storage_backup_$DATE.tar.gz"
    else
        echo "❌ Storage backup gefaald"
    fi
else
    echo "⚠️  Storage directory niet gevonden"
fi

# Git status
echo "📋 Git status:"
git status --porcelain

# Toon backup overzicht
echo ""
echo "🎉 Backup voltooid!"
echo "📍 Backup locatie: $BACKUP_DIR/"
echo "📊 Bestanden:"
ls -la $BACKUP_DIR/ | tail -n +2 | grep backup_$DATE

echo ""
echo "💡 Tip: Voer 'git add . && git commit -m \"Backup $DATE\"' uit om wijzigingen te committen"