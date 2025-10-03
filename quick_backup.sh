#!/bin/bash

# Snelle Database Backup voor Bonamiapp
# Gebruik: ./quick_backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p storage/backups

echo "💾 Database backup maken..."

# Herd/Local MySQL (meest waarschijnlijk voor jouw setup)
mysqldump -u root bonamiapp > storage/backups/bonamiapp_$DATE.sql 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Backup succesvol: storage/backups/bonamiapp_$DATE.sql"
    echo "📊 Bestand grootte: $(du -h storage/backups/bonamiapp_$DATE.sql | cut -f1)"
else
    echo "❌ Backup gefaald. Probeer handmatig:"
    echo "   mysqldump -u root -p bonamiapp > storage/backups/bonamiapp_$DATE.sql"
fi