# 🔧 Document Upload - File Size Limiet Oplossing

## Probleem: 413 Content Too Large

De upload faalt omdat de server limiet lager is dan 100MB. We moeten 3 configuraties aanpassen:

### Stap 1: PHP.ini Aanpassen

Zoek je `php.ini` bestand en pas deze waarden aan:

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

**Vind je php.ini:**
```bash
# Toon locatie
php --ini

# Of gebruik Laravel's artisan
php artisan tinker
>>> phpinfo();
```

**Voor macOS (Homebrew PHP):**
```bash
# Edit php.ini
nano /opt/homebrew/etc/php/8.2/php.ini

# Of gebruik de juiste versie
nano $(php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||")
```

### Stap 2: Nginx Configuratie (indien Nginx gebruikt)

Als je Nginx gebruikt, voeg dit toe aan je server block:

```nginx
server {
    ...
    client_max_body_size 100M;
    ...
}
```

**Edit Nginx config:**
```bash
# macOS
nano /opt/homebrew/etc/nginx/nginx.conf

# Linux
sudo nano /etc/nginx/nginx.conf

# Herstart Nginx
brew services restart nginx  # macOS
sudo systemctl restart nginx  # Linux
```

### Stap 3: Laravel .htaccess (Apache)

Als je Apache gebruikt, voeg dit toe aan `public/.htaccess`:

```apache
php_value upload_max_filesize 100M
php_value post_max_size 100M
php_value max_execution_time 300
php_value max_input_time 300
```

### Stap 4: PHP-FPM Herstarten

```bash
# macOS (Homebrew)
brew services restart php
brew services restart php@8.2

# Linux
sudo systemctl restart php8.2-fpm
```

---

## Snelle Test Commands

### Test 1: Check huidige PHP limieten
```bash
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

### Test 2: Check via Laravel
```bash
php artisan tinker
>>> echo ini_get('upload_max_filesize');
>>> echo ini_get('post_max_size');
```

### Test 3: Browser Console Test
Open browser console en run:
```javascript
fetch('/api/test-upload-limit').then(r => r.json()).then(console.log)
```

---

## Alternatieve Oplossing: Verlaag Max Upload Size

Als je geen server toegang hebt, verlaag de limiet in de controller validatie:

**In `KlantDocumentController.php`:**
```php
'document' => 'required|file|max:20480', // 20MB in plaats van 100MB
```

**En update de frontend melding in `klanten/show.blade.php`:**
```
PDF, Afbeeldingen, Video's, Word, Excel (max 20MB)
```

---

## Test of het werkt

1. Herstart PHP-FPM: `brew services restart php`
2. Clear Laravel cache: `php artisan config:clear`
3. Test met klein bestand eerst (< 1MB)
4. Test daarna met groter bestand

**Als het nog steeds faalt:**
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Check Nginx logs: `tail -f /opt/homebrew/var/log/nginx/error.log`
- Check PHP-FPM logs: `tail -f /opt/homebrew/var/log/php-fpm.log`

---

## Production Ready Settings

Voor productie gebruik, stel in:
- `upload_max_filesize = 50M` (50MB is vaak voldoende)
- `post_max_size = 55M` (iets hoger dan upload_max)
- `memory_limit = 256M` (voor grote PDF processing)
- `max_execution_time = 180` (3 minuten)
