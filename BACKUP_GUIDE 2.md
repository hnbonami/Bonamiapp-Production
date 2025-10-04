# Bonami App - Backup & Restore Gids

## 🚀 Quick Commands

### Backup maken
```bash
./backup_database.sh
```

### Restore naar werkende versie
```bash
# Bekijk beschikbare backups
./restore.sh

# Restore specifieke backup (gebruik datum van backup)
./restore.sh 20250929_163154
```

## 📅 Werkende Versies

### Stabiele Versie - 29/09/2025 16:31
- **Backup**: `bonami_backup_20250929_163154.sql` 
- **Status**: ✅ Werkend na foto upload fix
- **Fixes**: Medewerker model $casts duplicate opgelost
- **Features**: Volledige CRUD voor klanten en medewerkers

### Git Tags
```bash
# Huidige versie taggen
git tag -a v1.0-stable -m "Stabiele versie na foto upload fixes"
git push origin v1.0-stable

# Terug naar tag
git checkout v1.0-stable
```

## 🔧 Troubleshooting

### Als database corrupt is:
```bash
# Restore laatste werkende backup
./restore.sh 20250929_163154

# Of handmatig:
mysql -u Hannes -pHannes1986 Bonamisportcoaching < /Users/hannesbonami/Backups/bonami_db/bonami_backup_20250929_163154.sql
```

### Als code problemen heeft:
```bash
# Terug naar laatste commit
git reset --hard HEAD~1

# Of naar specifieke commit
git checkout [commit-hash]
```

## 📍 Backup Locatie
`/Users/hannesbonami/Backups/bonami_db/`

## 🆘 Emergency Commands
```bash
# Stop alle processen
php artisan down

# Cache legen
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# App weer online
php artisan up
```