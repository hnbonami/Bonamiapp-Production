# 🚀 DEPLOYMENT CHECKLIST - Bonami Sportcoaching

## ✅ PRE-DEPLOYMENT (Lokaal)

### Code Kwaliteit
- [ ] Alle tests draaien succesvol (`php artisan test`)
- [ ] Geen debug code achtergelaten (`dd()`, `dump()`, `var_dump()`)
- [ ] Alle TODO comments afgehandeld
- [ ] Git commits up-to-date

### Beveiliging Check
- [ ] `APP_DEBUG=false` in `.env.production`
- [ ] `APP_ENV=production` in `.env.production`
- [ ] Sterke wachtwoorden voor database
- [ ] `.env` staat in `.gitignore`
- [ ] Geen credentials in code (gebruik `.env`)

---

## 🔒 SERVER SETUP

### 1. Server Requirements
```bash
# Check PHP versie (moet 8.2+)
php -v

# Check vereiste extensies
php -m | grep -E 'pdo|mbstring|tokenizer|xml|ctype|json|bcmath|fileinfo'

# Check Composer
composer --version

# Check MySQL/MariaDB
mysql --version
```

### 2. SSL/HTTPS Setup ⚠️ KRITIEK!
```bash
# Install Certbot voor Let's Encrypt
sudo apt install certbot python3-certbot-nginx

# Verkrijg SSL certificaat
sudo certbot --nginx -d jouwdomein.be -d www.jouwdomein.be

# Test auto-renewal
sudo certbot renew --dry-run
```

### 3. Nginx Configuratie
```nginx
server {
    listen 443 ssl http2;
    server_name jouwdomein.be www.jouwdomein.be;
    
    root /var/www/bonamisportcoaching/public;
    index index.php;

    # SSL Certificaten
    ssl_certificate /etc/letsencrypt/live/jouwdomein.be/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/jouwdomein.be/privkey.pem;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Verberg .env bestand
    location ~ /\.env {
        deny all;
    }

    # Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name jouwdomein.be www.jouwdomein.be;
    return 301 https://$server_name$request_uri;
}
```

### 4. File Permissions
```bash
cd /var/www/bonamisportcoaching

# Storage moet writable zijn
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# .env moet beschermd zijn
chmod 600 .env
chown www-data:www-data .env
```

### 5. Firewall Setup
```bash
# Install UFW
sudo apt install ufw

# Configureer firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw enable
```

---

## 🚀 DEPLOYMENT STAPPEN

### 1. Code Deployment
```bash
# Op de server
cd /var/www/bonamisportcoaching

# Pull laatste code
git pull origin main

# Install dependencies (zonder dev packages!)
composer install --no-dev --optimize-autoloader

# Clear en cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### 2. Database Setup
```bash
# Maak database
mysql -u root -p
CREATE DATABASE bonamisportcoaching CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bonami_user'@'localhost' IDENTIFIED BY 'STERK_WACHTWOORD';
GRANT ALL PRIVILEGES ON bonamisportcoaching.* TO 'bonami_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force
```

### 3. Queue Worker (voor email triggers)
```bash
# Maak supervisor config
sudo nano /etc/supervisor/conf.d/bonami-worker.conf
```

```ini
[program:bonami-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/bonamisportcoaching/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/bonamisportcoaching/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start bonami-worker:*
```

### 4. Cron Jobs
```bash
# Edit crontab
crontab -e

# Voeg Laravel scheduler toe
* * * * * cd /var/www/bonamisportcoaching && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔐 POST-DEPLOYMENT SECURITY

### 1. Verify Security
```bash
# Check SSL rating
# Ga naar: https://www.ssllabs.com/ssltest/
# Moet minimaal A rating hebben!

# Check security headers
curl -I https://jouwdomein.be
```

### 2. Test Authentication
- [ ] Login werkt met HTTPS
- [ ] Cookies zijn secure (check browser dev tools)
- [ ] Session expireert na timeout
- [ ] Logout werkt correct

### 3. Test File Uploads
- [ ] Alleen toegestane bestanden kunnen geüpload worden
- [ ] Bestandsgrootte limiet werkt
- [ ] Uploads worden veilig opgeslagen

### 4. Test Authorization
- [ ] Klanten kunnen niet naar `/admin`
- [ ] Klanten kunnen niet naar `/klanten`
- [ ] Medewerkers kunnen niet naar admin functies
- [ ] 403 errors worden correct getoond

---

## 📊 MONITORING & BACKUP

### 1. Setup Backups
```bash
# Install backup package
composer require spatie/laravel-backup

# Configureer in config/backup.php
# Stel dagelijkse backups in via cron
```

### 2. Setup Monitoring
```bash
# Laravel Telescope (development only!)
composer require laravel/telescope --dev

# Production monitoring tools:
# - Sentry (error tracking)
# - New Relic (performance)
# - UptimeRobot (uptime monitoring)
```

### 3. Log Monitoring
```bash
# Check logs regelmatig
tail -f storage/logs/laravel.log

# Setup log rotation
# Logs ouder dan 7 dagen automatisch verwijderen
```

---

## ⚠️ KRITIEKE CHECKS VOOR GO-LIVE

- [ ] ✅ HTTPS/SSL werkt en geforceerd
- [ ] ✅ APP_DEBUG=false
- [ ] ✅ APP_ENV=production
- [ ] ✅ Sterke database wachtwoorden
- [ ] ✅ .env niet publiek toegankelijk
- [ ] ✅ Firewall actief en geconfigureerd
- [ ] ✅ Backups draaien
- [ ] ✅ Queue workers actief
- [ ] ✅ Cron jobs geconfigureerd
- [ ] ✅ Security headers geconfigureerd
- [ ] ✅ File permissions correct (755/644)
- [ ] ✅ Storage folders writable
- [ ] ✅ Email sending getest
- [ ] ✅ PDF generatie getest
- [ ] ✅ Bikefit calculator getest
- [ ] ✅ Alle admin functies getest
- [ ] ✅ Authorization checks getest

---

## 🆘 ROLLBACK PLAN

Als er iets misgaat:

```bash
# Rollback naar vorige versie
git log  # Vind vorige commit hash
git reset --hard <commit-hash>

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart services
sudo systemctl restart php8.2-fpm nginx
```

---

## 📞 SUPPORT CONTACTEN

- Laravel support: https://laravel.com/docs
- Hosting support: [Jouw hosting provider]
- SSL issues: https://letsencrypt.org/docs/
- Server admin: [Server administrator contact]

---

**BELANGRIJK:** Test ALLES eerst op een staging server voordat je naar productie gaat!

**SUCCES MET DE LAUNCH! 🚀**
