# 🚀 SERVER SETUP GUIDE - Bonami Sportcoaching
## Stapsgewijze Instructies voor Production Deployment

---

## 📋 VOORDAT JE BEGINT

**Benodigdheden:**
- Ubuntu/Debian server (20.04 LTS of nieuwer)
- Domein naam (bijv. bonamisportcoaching.be)
- SSH toegang tot server
- Root of sudo rechten

**Server Specificaties (Minimum):**
- 2GB RAM
- 2 CPU cores
- 50GB disk space
- Ubuntu 22.04 LTS

---

## STAP 1: SERVER BASIS SETUP (30 minuten)

### 1.1 Connect naar Server
```bash
# Via SSH
ssh root@jouw-server-ip

# Of als je al een user hebt
ssh username@jouw-server-ip
```

### 1.2 Update Server
```bash
# Update package lijst
sudo apt update

# Upgrade alle packages
sudo apt upgrade -y

# Install basis utilities
sudo apt install -y curl git unzip software-properties-common
```

### 1.3 Maak Deployment User
```bash
# Maak nieuwe user (niet root gebruiken!)
sudo adduser bonami

# Geef sudo rechten
sudo usermod -aG sudo bonami

# Switch naar nieuwe user
su - bonami
```

### 1.4 Setup SSH Key (Veiliger dan password)
```bash
# Op je LOKALE machine (MacBook):
ssh-keygen -t rsa -b 4096 -C "jouw@email.com"

# Copy public key naar server
ssh-copy-id bonami@jouw-server-ip

# Test SSH key login
ssh bonami@jouw-server-ip

# Disable password login (optioneel maar veiliger)
sudo nano /etc/ssh/sshd_config
# Zoek en wijzig: PasswordAuthentication no
sudo systemctl restart ssh
```

---

## STAP 2: INSTALLEER LAMP STACK (45 minuten)

### 2.1 Install PHP 8.2
```bash
# Voeg PHP repository toe
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 en extensies
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql \
    php8.2-curl php8.2-mbstring php8.2-xml php8.2-zip \
    php8.2-gd php8.2-bcmath php8.2-intl php8.2-redis

# Verify installatie
php -v
# Moet tonen: PHP 8.2.x
```

### 2.2 Install MySQL/MariaDB
```bash
# Install MySQL
sudo apt install -y mysql-server

# Secure MySQL installatie
sudo mysql_secure_installation
# Beantwoord vragen:
# - Set root password? YES (kies sterk wachtwoord!)
# - Remove anonymous users? YES
# - Disallow root login remotely? YES
# - Remove test database? YES
# - Reload privilege tables? YES

# Test MySQL login
sudo mysql -u root -p
```

### 2.3 Maak Database en User
```bash
# Login in MySQL
sudo mysql -u root -p

# Run deze SQL commands:
```
```sql
-- Maak database
CREATE DATABASE bonamisportcoaching CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Maak database user met STERK wachtwoord
CREATE USER 'bonami_user'@'localhost' IDENTIFIED BY 'JouwSterkWachtwoord123!@#';

-- Geef rechten
GRANT ALL PRIVILEGES ON bonamisportcoaching.* TO 'bonami_user'@'localhost';

-- Reload privileges
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

```bash
# Test database toegang
mysql -u bonami_user -p bonamisportcoaching
# Enter wachtwoord, als het werkt typ: EXIT;
```

### 2.4 Install Nginx
```bash
# Install Nginx
sudo apt install -y nginx

# Start Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Check status
sudo systemctl status nginx

# Test in browser: http://jouw-server-ip
# Je zou "Welcome to nginx" moeten zien
```

### 2.5 Install Composer
```bash
# Download Composer
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php

# Install globally
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Cleanup
rm composer-setup.php

# Verify
composer --version
```

### 2.6 Install Node.js (voor asset compilation)
```bash
# Install Node.js 20.x
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verify
node --version
npm --version
```

### 2.7 Install Redis (voor caching)
```bash
# Install Redis
sudo apt install -y redis-server

# Start Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Test Redis
redis-cli ping
# Should return: PONG
```

---

## STAP 3: DEPLOY LARAVEL APP (30 minuten)

### 3.1 Maak Project Directory
```bash
# Maak directory
sudo mkdir -p /var/www/bonamisportcoaching
sudo chown -R bonami:bonami /var/www/bonamisportcoaching

# Ga naar directory
cd /var/www/bonamisportcoaching
```

### 3.2 Clone Repository
```bash
# Clone je Git repository
git clone https://github.com/jouw-username/Bonamiapp.git .

# Of via SSH (als je SSH key hebt)
git clone git@github.com:jouw-username/Bonamiapp.git .
```

### 3.3 Install Dependencies
```bash
# Install PHP dependencies (ZONDER dev packages!)
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm install

# Build assets
npm run build
```

### 3.4 Setup Environment File
```bash
# Copy production example
cp .env.production.example .env

# Edit .env file
nano .env
```

**EDIT .env met deze instellingen:**
```env
APP_NAME="Bonami Sportcoaching"
APP_ENV=production
APP_KEY=  # Genereren we zo
APP_DEBUG=false  # KRITIEK!
APP_TIMEZONE=Europe/Brussels
APP_URL=https://jouwdomein.be

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bonamisportcoaching
DB_USERNAME=bonami_user
DB_PASSWORD=JouwSterkWachtwoord123!@#

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=jouw@email.com
MAIL_PASSWORD=jouw-app-wachtwoord
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@bonamisportcoaching.be

QUEUE_CONNECTION=database

CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3.5 Generate App Key & Run Migrations
```bash
# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Cache config (performance boost)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link
```

### 3.6 Set File Permissions
```bash
# Storage en cache moeten writable zijn
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# .env moet beschermd zijn
sudo chmod 600 .env
sudo chown bonami:bonami .env

# Andere bestanden
sudo chown -R bonami:www-data /var/www/bonamisportcoaching
sudo find /var/www/bonamisportcoaching -type f -exec chmod 644 {} \;
sudo find /var/www/bonamisportcoaching -type d -exec chmod 755 {} \;
```

---

## STAP 4: CONFIGUREER NGINX (20 minuten)

### 4.1 Maak Nginx Config
```bash
# Maak nieuwe site config
sudo nano /etc/nginx/sites-available/bonamisportcoaching
```

**Plak deze configuratie:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name bonamisportcoaching.be www.bonamisportcoaching.be;
    
    root /var/www/bonamisportcoaching/public;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/bonami-access.log;
    error_log /var/log/nginx/bonami-error.log;

    # Security: Verberg .env en .git
    location ~ /\.(env|git) {
        deny all;
        return 404;
    }

    # Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4.2 Activeer Site
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/bonamisportcoaching /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test config
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### 4.3 Test Website
```bash
# Bezoek http://jouw-server-ip in browser
# Je zou de Laravel app moeten zien (zonder SSL voorlopig)
```

---

## STAP 5: INSTALLEER SSL (Let's Encrypt) 🔒 (15 minuten)

### 5.1 Punt Domein naar Server
**EERST dit doen voordat je SSL installeert!**

1. Ga naar je domein registrar (bijv. TransIP, Hostnet)
2. Voeg deze DNS records toe:

```
Type    Host    Value               TTL
A       @       jouw-server-ip      3600
A       www     jouw-server-ip      3600
```

3. Wacht 5-10 minuten voor DNS propagatie
4. Test met: `ping bonamisportcoaching.be`

### 5.2 Install Certbot
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Verify installatie
certbot --version
```

### 5.3 Verkrijg SSL Certificaat
```bash
# Run Certbot
sudo certbot --nginx -d bonamisportcoaching.be -d www.bonamisportcoaching.be

# Beantwoord vragen:
# - Email: jouw@email.com
# - Terms of Service: Yes (A)
# - Share email: No (N)
# - Redirect HTTP to HTTPS: Yes (2)

# Certbot update automatisch de Nginx config!
```

### 5.4 Test Auto-Renewal
```bash
# Test renewal proces (dry-run)
sudo certbot renew --dry-run

# Als succesvol, is auto-renewal geconfigureerd!
```

### 5.5 Verify SSL
```bash
# Check SSL rating
# Ga naar: https://www.ssllabs.com/ssltest/
# Voer in: bonamisportcoaching.be
# Moet A of A+ rating hebben!
```

---

## STAP 6: QUEUE WORKERS & CRON (20 minuten)

### 6.1 Install Supervisor (Queue Workers)
```bash
# Install Supervisor
sudo apt install -y supervisor

# Maak worker config
sudo nano /etc/supervisor/conf.d/bonami-worker.conf
```

**Plak deze config:**
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
# Reload Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start bonami-worker:*

# Check status
sudo supervisorctl status
```

### 6.2 Setup Cron Jobs
```bash
# Edit crontab
crontab -e

# Voeg toe (Laravel scheduler):
* * * * * cd /var/www/bonamisportcoaching && php artisan schedule:run >> /dev/null 2>&1

# Save en exit
```

---

## STAP 7: FIREWALL SETUP (10 minuten)

```bash
# Install UFW
sudo apt install -y ufw

# Default policies
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH (BELANGRIJK!)
sudo ufw allow OpenSSH

# Allow HTTP & HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

---

## STAP 8: BACKUPS INSTELLEN (15 minuten)

### 8.1 Install Laravel Backup Package
```bash
cd /var/www/bonamisportcoaching

# Install package
composer require spatie/laravel-backup

# Publish config
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

### 8.2 Configureer Backups
```bash
# Edit backup config
nano config/backup.php
```

**Belangrijke instellingen:**
```php
'name' => env('APP_NAME', 'bonamisportcoaching'),

'source' => [
    'files' => [
        'include' => [
            base_path(),
        ],
        'exclude' => [
            base_path('vendor'),
            base_path('node_modules'),
        ],
    ],
    
    'databases' => ['mysql'],
],

'destination' => [
    'disks' => [
        'local', // Of 's3' voor cloud backups
    ],
],
```

### 8.3 Test Backup
```bash
# Run backup
php artisan backup:run

# Check backups
ls -lh storage/app/backups/
```

### 8.4 Schedule Daily Backups
```bash
# Edit schedule in app/Console/Kernel.php
# Voeg toe in schedule() method:
```
```php
protected function schedule(Schedule $schedule)
{
    // Dagelijkse backup om 2:00 AM
    $schedule->command('backup:run')->daily()->at('02:00');
    
    // Cleanup oude backups (ouder dan 7 dagen)
    $schedule->command('backup:clean')->daily()->at('03:00');
}
```

---

## STAP 9: SECURITY CHECKLIST ✅

Loop deze checklist door:

```bash
□ SSL certificaat geïnstalleerd en A rating
□ APP_DEBUG=false in .env
□ APP_ENV=production in .env
□ Sterk database wachtwoord gebruikt
□ .env file niet publiek toegankelijk (chmod 600)
□ Firewall actief (ufw status)
□ SSH key-based authentication
□ File permissions correct (755/644)
□ Storage writable (775)
□ Queue workers draaien (supervisorctl status)
□ Cron jobs geconfigureerd (crontab -l)
□ Backups draaien (php artisan backup:run)
□ Redis werkt (redis-cli ping)
□ MySQL werkt (mysql -u bonami_user -p)
□ Website bereikbaar via HTTPS
□ HTTP redirect naar HTTPS werkt
□ Email sending getest
□ PDF generatie getest
□ File uploads getest
□ All authorization checks getest
```

---

## STAP 10: MONITORING & ONDERHOUD

### 10.1 Check Logs Regelmatig
```bash
# Laravel logs
tail -f /var/www/bonamisportcoaching/storage/logs/laravel.log

# Nginx error log
sudo tail -f /var/log/nginx/bonami-error.log

# PHP-FPM log
sudo tail -f /var/log/php8.2-fpm.log
```

### 10.2 Updates
```bash
# Wekelijks updaten
sudo apt update
sudo apt upgrade -y

# Laravel updates
cd /var/www/bonamisportcoaching
composer update
php artisan migrate --force
php artisan config:cache
```

### 10.3 Disk Space Monitoring
```bash
# Check disk usage
df -h

# Check largest directories
du -sh /var/www/bonamisportcoaching/* | sort -h
```

---

## 🆘 TROUBLESHOOTING

### Website toont 500 error
```bash
# Check Laravel logs
tail -n 50 storage/logs/laravel.log

# Check Nginx error log
sudo tail -n 50 /var/log/nginx/bonami-error.log

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Queue workers stoppen
```bash
# Restart workers
sudo supervisorctl restart bonami-worker:*

# Check status
sudo supervisorctl status
```

### SSL certificaat verloopt
```bash
# Renew certificaat
sudo certbot renew

# Restart Nginx
sudo systemctl reload nginx
```

### Database connectie errors
```bash
# Check MySQL status
sudo systemctl status mysql

# Test database login
mysql -u bonami_user -p bonamisportcoaching
```

---

## 📞 HULP NODIG?

**Laravel Documentatie:** https://laravel.com/docs
**DigitalOcean Tutorials:** https://www.digitalocean.com/community/tutorials
**Stack Overflow:** https://stackoverflow.com/questions/tagged/laravel

---

**🎉 GEFELICITEERD! Je applicatie is nu LIVE en VEILIG!**

**Volgende stap:** Monitor de applicatie de eerste week intensief voor errors!