# 🚀 Deployment Checklist - One.com

## ✅ LOKAAL (Voor Upload)

### 1. Analyseer Migrations
```bash
# Bekijk welke migrations nog niet online draaien
php artisan migrations:analyseer

# Preview SQL zonder uit te voeren
php artisan migrate --pretend
```

### 2. Test Lokaal Grondig
- [ ] Alle functionaliteiten testen
- [ ] PDF generatie checken
- [ ] Email functionaliteit checken
- [ ] Bikefit calculator testen
- [ ] Testzadel systeem checken

### 3. Voorbereid Bestanden
```bash
# Optimaliseer voor productie
composer install --no-dev --optimize-autoloader

# Of maak een clean export zonder dev dependencies
composer install --no-dev
```

## 📤 UPLOAD VIA FILEZILLA

### 4. Maak Online Backup
**BELANGRIJK: Download eerst deze bestanden/mappen:**
- [ ] `.env` bestand
- [ ] `/public/uploads` (klantdata!)
- [ ] `/storage/app` (geüploade bestanden)

**Backup Database via TablePlus:**
- [ ] Verbind met online database
- [ ] Rechtermuisknop → Export → Structure + Data
- [ ] Sla op als: `bonami_backup_[datum].sql`

### 5. Upload Nieuwe/Gewijzigde Bestanden

**Upload deze mappen (overschrijf):**
```
✅ /app/Console/Commands      (nieuwe commands)
✅ /app/Http/Controllers      (bijgewerkte controllers)
✅ /app/Models                (bijgewerkte models)
✅ /app/Services              (nieuwe/bijgewerkte services)
✅ /config                    (configuratie updates)
✅ /database/migrations       (ALLE migrations)
✅ /resources/views           (blade templates)
✅ /routes                    (route updates)
✅ /public (behalve /uploads) (assets, index.php)
✅ composer.json
✅ composer.lock
```

**NIET uploaden (behoud online versie):**
```
❌ .env                       (handmatig vergelijken/updaten)
❌ /storage                   (bevat cache, logs, sessions)
❌ /public/uploads            (bestaande klantdata)
❌ /vendor                    (regenereren via composer)
❌ /node_modules              (indien aanwezig)
```

## 🌐 ONLINE (Via One.com Control Panel)

### 6. Update Dependencies
Via One.com SSH/Terminal of Control Panel:
```bash
# Navigeer naar je website root
cd public_html  # of domains/jouwdomain.nl

# Update composer dependencies
composer install --no-dev --optimize-autoloader
```

### 7. Run Migrations VEILIG

**Optie A: Met Veiligheidscheck (AANGERADEN)**
```bash
# Stap 1: Check eerst wat er uitgevoerd gaat worden
php artisan migrate:veilig --check --backup

# Stap 2: Als alles OK lijkt, voer uit
php artisan migrate:veilig --backup
```

**Optie B: Standaard Laravel**
```bash
# Preview eerst (ALTIJD!)
php artisan migrate --pretend

# Dan uitvoeren
php artisan migrate --force
```

### 8. Clear Caches
```bash
# Clear alle caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Rebuild caches voor productie
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9. Fix Permissions (indien nodig)
```bash
# Storage en bootstrap cache beschrijfbaar maken
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Uploads folder
chmod -R 775 public/uploads
```

## 🧪 VERIFICATIE ONLINE

### 10. Test Alle Functionaliteiten
- [ ] Login werkt
- [ ] Klanten overzicht tonen
- [ ] Nieuwe klant aanmaken
- [ ] Bikefit aanmaken en berekeningen
- [ ] PDF generatie testen
- [ ] Testzadel uitlenen/retour
- [ ] Email herinneringen (indien actief)
- [ ] Upload functionaliteit

### 11. Check Database
Via TablePlus:
- [ ] Verbind met online database
- [ ] Verifieer nieuwe tabellen aanwezig
- [ ] Check of bestaande data intact is
- [ ] Controleer migrations tabel

### 12. Check Logs
Via One.com File Manager of FTP:
- [ ] Bekijk `storage/logs/laravel.log`
- [ ] Check op errors
- [ ] Verifieer geen waarschuwingen

## 🚨 ROLLBACK PLAN (indien nodig)

**Als iets misgaat:**

1. **Database Rollback**
```bash
# Laatste migration terugdraaien
php artisan migrate:rollback --step=1

# Of alles van laatste batch
php artisan migrate:rollback
```

2. **Bestanden Terugzetten**
- Upload je backup bestanden via FileZilla
- Herstel .env indien gewijzigd

3. **Database Volledig Herstellen**
- Via TablePlus: Importeer je backup SQL bestand
- ALLEEN als laatste redmiddel!

## 📋 ONE.COM SPECIFIEKE TIPS

### Artisan Commands Uitvoeren
**Methode 1: Via One.com Control Panel**
1. Log in op One.com
2. Ga naar "Advanced" → "SSH Access"
3. Klik "Enable SSH" (indien uitgeschakeld)
4. Gebruik Web Terminal in browser
5. Navigeer naar site: `cd domains/jouwdomain.nl`
6. Run commands: `php artisan ...`

**Methode 2: Via lokale SSH (indien enabled)**
```bash
ssh username@ssh.one.com
cd domains/jouwdomain.nl
php artisan migrate:veilig --check
```

### Composer Update
One.com heeft composer globaal geïnstalleerd:
```bash
# Check composer versie
composer --version

# Update dependencies
composer install --no-dev --optimize-autoloader
```

### Database Credentials
Check je `.env` bestand:
```env
DB_CONNECTION=mysql
DB_HOST=localhost          # Of specifieke One.com host
DB_PORT=3306
DB_DATABASE=jouw_db_naam
DB_USERNAME=jouw_db_user
DB_PASSWORD=jouw_db_pass
```

## 📝 NOTITIES

**Datum deployment:** _______________

**Uitgevoerde migrations:**
- 
- 
- 

**Problemen tegengekomen:**
- 

**Opgelost door:**
- 

---

## 🆘 HULP NODIG?

**One.com Support:**
- Live chat beschikbaar
- Email: support@one.com
- Kennisbank: help.one.com

**Laravel Errors:**
- Check `storage/logs/laravel.log`
- Run `php artisan migrate:status` voor migration status
- Run `php artisan route:list` om routes te checken
