# 🚀 DEPLOYMENT GUIDE - Bonami Sportcoaching naar Wix Hosting

## ✅ PRE-DEPLOYMENT CHECKLIST

### Lokale Voorbereiding
- [ ] Alle wijzigingen getest in lokale omgeving
- [ ] Database backup gemaakt
- [ ] .env.production bestand geconfigureerd
- [ ] Composer dependencies geoptimaliseerd
- [ ] Cache gecleared

## 📦 STAP 1: LOKALE BESTANDEN VOORBEREIDEN

### 1.1 Optimaliseer Laravel voor Productie

Run deze commands in je lokale terminal:

```bash
cd /Users/hannesbonami/Desktop/Bonamiapp

# Clear alle caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimaliseer voor productie
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Genereer nieuwe APP_KEY voor productie
php artisan key:generate --show
```

⚠️ **BELANGRIJK**: Kopieer de gegenereerde APP_KEY en zet deze in `.env.production`

### 1.2 Bestanden die NIET geüpload mogen worden

Creëer een `.deployignore` lijst:
- `/node_modules/`
- `/vendor/` (wordt later opnieuw gegenereerd)
- `.env` (gebruik .env.production)
- `.git/`
- `/storage/logs/*.log`
- `/tests/`
- `.DS_Store`

## 🌐 STAP 2: WIX HOSTING CONFIGURATIE

### 2.1 Controleer Wix Hosting Requirements

✅ Zorg dat je hosting heeft:
- PHP 8.1 of hoger
- MySQL database
- Composer support
- SSH toegang (ideaal, maar niet verplicht)

### 2.2 Database Setup via TablePlus

1. **Maak verbinding met productie database**
   - Open TablePlus
   - Nieuw → MySQL connectie
   - Vul in: host, database naam, username, password (van Wix)
   
2. **Importeer database structuur**
   ```sql
   -- Eerst: Export je lokale database
   -- In TablePlus: Klik rechtsboven → Export → SQL
   ```

3. **Schema's die geïmporteerd moeten worden**:
   - `migrations`
   - `users`
   - `klanten`
   - `bikefits`
   - `inspanningstests`
   - `testresultaten`
   - `sjablonen`
   - `testzadels`
   - Alle andere tabellen

⚠️ **LET OP**: Controleer character encoding (utf8mb4_unicode_ci)

## 📤 STAP 3: BESTANDEN UPLOADEN VIA FILEZILLA

### 3.1 FileZilla Voorbereiding

**Connectie instellingen**:
- Host: ftp.jouw-wix-site.com (krijg je van Wix)
- Username: jouw_ftp_username
- Password: jouw_ftp_password
- Port: 21 (of 22 voor SFTP)

### 3.2 Upload Volgorde (BELANGRIJK!)

**STAP A: Root Laravel bestanden**
Upload naar `/public_html/` of `/httpdocs/`:

1. Eerst de core bestanden:
   - `app/`
   - `bootstrap/`
   - `config/`
   - `database/`
   - `public/`
   - `resources/`
   - `routes/`
   - `storage/`
   - `artisan`
   - `composer.json`
   - `composer.lock`

2. Rename `.env.production` → `.env` tijdens upload

**STAP B: Public folder instelling**

⚠️ **KRITIEK**: Laravel's public folder moet de document root zijn!

Wix configuratie:
- Document root moet wijzen naar: `/public_html/public/`
- OF: Verplaats alles uit `/public/` naar de root en pas `index.php` aan

**Alternatief voor Wix** (als document root niet instelbaar is):
```php
// Wijzig /public/index.php regel 34
$app = require_once __DIR__.'/../bootstrap/app.php';
// Naar:
$app = require_once __DIR__.'/bootstrap/app.php';

// En verplaats alle bestanden één niveau omhoog
```

### 3.3 Permissies instellen

Via FileZilla → Rechtermuisklik op folders → Permissions:

```
storage/          → 775
storage/logs/     → 775
storage/framework/→ 775
bootstrap/cache/  → 775
```

## 🔧 STAP 4: ONLINE CONFIGURATIE

### 4.1 Composer Dependencies Installeren

**Via SSH** (als beschikbaar):
```bash
ssh jouw_username@jouw_host.com
cd public_html
composer install --optimize-autoloader --no-dev
```

**Zonder SSH** (via Wix control panel):
- Sommige Wix hostings hebben een "Composer Install" knop
- OF: upload je volledige `/vendor/` folder via FileZilla (traag!)

### 4.2 .env File Aanpassen

Via FileZilla → open `.env` → wijzig:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jouw-echte-domein.com

DB_HOST=localhost
DB_DATABASE=jouw_wix_database_naam
DB_USERNAME=jouw_wix_db_user
DB_PASSWORD=jouw_wix_db_password
```

### 4.3 Storage Link Creëren

**Via SSH**:
```bash
php artisan storage:link
```

**Zonder SSH**: Maak handmatig een symlink via Wix control panel of:
1. Upload bestanden naar `/public/storage/`
2. Zorg dat `/storage/app/public/` gespiegeld wordt

### 4.4 Database Migraties

**OPTIE A - Via SSH**:
```bash
php artisan migrate --force
```

**OPTIE B - Handmatig via TablePlus**:
1. Exporteer je lokale database structuur
2. Importeer in productie database
3. Controleer alle tabellen

## ✅ STAP 5: POST-DEPLOYMENT CHECKS

### 5.1 Basis Functionaliteit Testen

- [ ] Website laadt (https://jouw-domein.com)
- [ ] Login functionaliteit werkt
- [ ] Database verbinding werkt (probeer klant te laden)
- [ ] Afbeeldingen worden getoond
- [ ] PDF generatie werkt
- [ ] Auto-save functionaliteit werkt

### 5.2 Error Logs Controleren

Via FileZilla → `/storage/logs/laravel.log`

Veelvoorkomende errors:
- `500 Error` → Check `.env` configuratie
- `Permission denied` → Check folder permissies (775)
- `Class not found` → Run `composer dump-autoload`
- `Database connection failed` → Check DB credentials

### 5.3 Performance Optimalisatie

**Cache warmup** (via SSH):
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 🆘 TROUBLESHOOTING

### Error: "500 Internal Server Error"

1. Schakel debug mode tijdelijk aan:
   ```env
   APP_DEBUG=true
   ```
2. Refresh de pagina → bekijk error
3. Fix de error
4. Zet debug weer uit: `APP_DEBUG=false`

### Error: "Storage not writable"

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Error: "Class not found"

```bash
composer dump-autoload
```

### Database verbinding werkt niet

Controleer in `.env`:
- `DB_HOST` (vaak `localhost`, soms IP-adres)
- `DB_DATABASE` (exacte naam van Wix database)
- `DB_USERNAME` (Wix database gebruiker)
- `DB_PASSWORD` (check voor speciale karakters)

## 📞 SUPPORT CHECKLIST

Bij problemen, verzamel:
- [ ] Error logs (`/storage/logs/laravel.log`)
- [ ] PHP version (`php -v`)
- [ ] Composer version
- [ ] Database verbinding test
- [ ] Bestand permissies (`ls -la storage/`)

## 🎉 SUCCESS CHECKLIST

Deployment succesvol als:
- [x] Alle pagina's laden zonder errors
- [x] Login werkt
- [x] Database queries werken
- [x] File uploads werken
- [x] Auto-save werkt
- [x] PDF generatie werkt
- [x] Inspanningstests kunnen worden aangemaakt/bewerkt

---

**Laatst bijgewerkt**: {{ now()->format('Y-m-d') }}
**Deployment door**: Hannes Bonami
**Laravel versie**: 12.x
**PHP versie**: 8.1+
