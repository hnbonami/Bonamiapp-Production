# 🚨 NOODPLAN: 500 Error Oplossen ZONDER SSH

## ⚠️ Je hebt nu 500 Server Error - Volg deze stappen EXACT

### 📋 STAP 1: Upload Helper Scripts

**Via FileZilla upload deze 2 bestanden:**

1. **deploy-helper.php** → upload naar `/public/deploy-helper.php`
2. **browser-migrate.php** → upload naar `/public/browser-migrate.php`

Locatie op je Mac:
```
/Users/hannesbonami/Desktop/Bonamiapp/public/deploy-helper.php
/Users/hannesbonami/Desktop/Bonamiapp/public/browser-migrate.php
```

---

### 🔧 STAP 2: Fix de 500 Error

**A. Open in browser:**
```
https://jouwdomain.nl/deploy-helper.php?pass=bonami2025temp
```

Dit toont de status. Waarschijnlijk zie je:
- ❌ vendor exists: NO - Run composer!

**B. Run Composer Install:**
```
https://jouwdomain.nl/deploy-helper.php?pass=bonami2025temp&action=composer
```

Wacht tot dit klaar is (kan 1-2 minuten duren)

**C. Clear Caches:**
```
https://jouwdomain.nl/deploy-helper.php?pass=bonami2025temp&action=clear-cache
```

**D. Fix Permissions:**
```
https://jouwdomain.nl/deploy-helper.php?pass=bonami2025temp&action=fix-permissions
```

**E. Check .env:**
```
https://jouwdomain.nl/deploy-helper.php?pass=bonami2025temp&action=check-env
```

Let op deze checklist:
- ✓ APP_KEY set
- ✓ DB_DATABASE set
- ✓ DB_USERNAME set
- ✓ DB_PASSWORD set

Als iets ✗ is, moet je .env aanpassen via FileZilla!

**F. Bekijk Logs:**
```
https://jouwdomain.nl/deploy-helper.php?pass=bonami2025temp&action=logs
```

Dit toont de exacte error!

---

### 🗄️ STAP 3: Database Migrations

**Alleen als site weer werkt (geen 500 error meer)!**

**A. Open Migration Tool:**
```
https://jouwdomain.nl/browser-migrate.php?pass=bonami2025migrate
```

**B. Bekijk Status:**
Klik op "📊 Bekijk Status" knop

**C. Preview SQL (veilig):**
Klik op "🔍 Preview SQL" knop
- Dit voert NIETS uit, alleen preview!

**D. Voer Migrations Uit:**
Klik op "🚀 Voer Migrations Uit" knop
- ALLEEN als preview OK lijkt!

---

## 🔍 TROUBLESHOOTING

### Als Composer Faalt:

**Plan B: Upload vendor folder**

1. Lokaal op Mac:
```bash
cd /Users/hannesbonami/Desktop/Bonamiapp
composer install --no-dev --optimize-autoloader
```

2. Zip de vendor folder:
```bash
cd /Users/hannesbonami/Desktop/Bonamiapp
zip -r vendor.zip vendor/
```

3. Upload `vendor.zip` via FileZilla naar server root

4. Unzip via One.com File Manager of via deploy-helper

### Als .env Problemen:

**Download huidige online .env:**
1. Via FileZilla: Download `/.env` van server
2. Vergelijk met lokale `.env`
3. Pas aan waar nodig
4. Upload terug

**Belangrijke .env settings voor productie:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jouwdomain.nl

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=jouw_database_naam
DB_USERNAME=jouw_database_user
DB_PASSWORD=jouw_database_wachtwoord
```

### Als Permissions Problemen:

**Via One.com File Manager:**
1. Login op One.com
2. Ga naar File Manager
3. Rechtsklik op `/storage` → Properties → Permissions
4. Zet op `775` (rwxrwxr-x)
5. Herhaal voor `/bootstrap/cache`

### Als APP_KEY Ontbreekt:

**Genereer nieuwe key:**
```
https://jouwdomain.nl/deploy-helper.php?pass=bonami2025temp&action=generate-key
```

Of handmatig:
1. Ga naar: https://generate-random.org/laravel-key-generator
2. Genereer een key
3. Voeg toe aan .env: `APP_KEY=base64:...`

---

## 📞 ALTERNATIVE: Via One.com Control Panel

**Als browser scripts niet werken:**

### 1. One.com PHP Command Line

1. Login One.com
2. **Advanced** → **PHP Command Line** (als beschikbaar)
3. Run:
```bash
cd domains/jouwdomain.nl
php -v
ls -la
```

### 2. One.com Cronjob Hack

1. **Advanced** → **Cron Jobs**
2. Maak nieuwe cronjob:
```bash
cd /home/username/domains/jouwdomain.nl && composer install --no-dev
```
3. Run manually
4. Verwijder cronjob daarna

### 3. One.com Support

Als niets werkt:
- Live chat: one.com/support
- Vraag: "Kan jullie composer install runnen op mijn Laravel app?"
- Of: "Kan jullie SSH tijdelijk enablen voor migrations?"

---

## ✅ CHECKLIST

**Vóór migrations:**
- [ ] 500 error is opgelost (site laadt)
- [ ] Composer dependencies geïnstalleerd
- [ ] Caches gecleared
- [ ] .env is correct
- [ ] Database backup gemaakt

**Migrations uitvoeren:**
- [ ] Status bekeken
- [ ] SQL preview bekeken
- [ ] Migrations uitgevoerd
- [ ] Status opnieuw gecheckt

**Na afloop:**
- [ ] Helper scripts verwijderd
- [ ] Site getest
- [ ] Database verificatie via TablePlus

---

## 🆘 NOODPROCEDURE

**Als ALLES misgaat:**

1. **Stop - Maak geen verdere wijzigingen**

2. **Rollback bestanden via FileZilla:**
   - Download je lokale backup
   - Upload oude versie terug

3. **Herstel database:**
   - Via TablePlus: Importeer je backup SQL
   - Of via phpMyAdmin op One.com

4. **Contact mij met:**
   - Screenshot van error
   - Laatste 50 regels van laravel.log
   - .env file (zonder wachtwoorden!)

---

## 📝 STAPPENPLAN IN VOLGORDE

1. ✅ Upload helper scripts
2. 🔧 Fix 500 error via deploy-helper.php
3. ✅ Controleer site werkt (geen 500 meer)
4. 🗄️ Run migrations via browser-migrate.php
5. ✅ Test functionaliteit
6. 🧹 Verwijder helper scripts
7. 🎉 Klaar!

**Start hier:** `https://jouwdomain.nl/deploy-helper.php?pass=bonami2025temp`
