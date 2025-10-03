#!/bin/bash

echo "🚨 DATABASE BACKUP HERSTEL SCRIPT 🚨"
echo "====================================="

# Ga naar de juiste directory
cd /Users/hannesbonami/Herd/app/Bonamiapp

# Check of backup bestand bestaat
if [ ! -f "./database_backups/bonamiapp_backup_20250927-134900.sql" ]; then
    echo "❌ Backup bestand niet gevonden!"
    ls -la database_backups/
    exit 1
fi

echo "✅ Backup bestand gevonden!"
echo "📁 Bestand: ./database_backups/bonamiapp_backup_20250927-134900.sql"

# Probeer verschillende MySQL verbindingsmethoden
echo ""
echo "🔄 Poging 1: MySQL zonder wachtwoord..."
if mysql -u root bonamisportcoaching < ./database_backups/bonamiapp_backup_20250927-134900.sql 2>/dev/null; then
    echo "✅ DATABASE SUCCESVOL HERSTELD!"
    exit 0
fi

echo "🔄 Poging 2: MySQL met lege wachtwoord..."
if mysql -u root -p'' bonamisportcoaching < ./database_backups/bonamiapp_backup_20250927-134900.sql 2>/dev/null; then
    echo "✅ DATABASE SUCCESVOL HERSTELD!"
    exit 0
fi

echo "🔄 Poging 3: Via Herd MySQL..."
if /Applications/Herd.app/Contents/Resources/bin/mysql -u root bonamisportcoaching < ./database_backups/bonamiapp_backup_20250927-134900.sql 2>/dev/null; then
    echo "✅ DATABASE SUCCESVOL HERSTELD!"
    exit 0
fi

echo ""
echo "❌ Automatische herstel mislukt!"
echo "🔧 Probeer handmatig:"
echo "mysql -u root -p bonamisportcoaching < ./database_backups/bonamiapp_backup_20250927-134900.sql"
echo ""
echo "Of importeer via je database tool (DBngin/Herd) het bestand:"
echo "./database_backups/bonamiapp_backup_20250927-134900.sql"