# Avatar Upload Probleem Oplossen

## Probleem
Avatars uploaden werkt lokaal maar geeft vraagtekens op de server.

## Diagnose & Oplossing

### Stap 1: Diagnose uitvoeren
Via SSH verbinden met de server en run:
```bash
cd /pad/naar/bonamiapp
php artisan storage:diagnose
```

Dit toont alle storage configuratie info en eventuele problemen.

### Stap 2: Permissies en structuur fixen
```bash
php artisan storage:fix-permissions
```

Dit commando:
- Creëert alle benodigde storage directories
- Zet correcte permissies (0755)
- Recreëert de storage symlink

### Stap 3: Handmatige verificatie (indien nodig)

#### A. Check permissies
```bash
ls -la storage/app/public
ls -la storage/app/public/avatars
ls -la public/storage
```

Alle directories moeten permissies `755` of `drwxr-xr-x` hebben.

#### B. Fix permissies handmatig
```bash
chmod -R 755 storage
chmod -R 755 public/storage
chown -R www-data:www-data storage
chown -R www-data:www-data public/storage
```

> **Let op**: Vervang `www-data` met de juiste webserver user (kan ook `nginx`, `apache`, of `hannesbonami` zijn)

#### C. Recreëer symlink handmatig
```bash
# Verwijder oude symlink
rm public/storage

# Creëer nieuwe symlink
php artisan storage:link
```

### Stap 4: Test avatar upload
- Log in op de applicatie
- Ga naar een klant profiel
- Upload een nieuwe avatar
- Controleer of de avatar direct zichtbaar is

## Mogelijke Oorzaken

### 1. Symlink bestaat niet
De `public/storage` symlink moet wijzen naar `storage/app/public`.
**Oplossing**: `php artisan storage:link`

### 2. Permissie problemen
Webserver heeft geen write rechten op storage directories.
**Oplossing**: `chmod -R 755 storage && chown -R www-data:www-data storage`

### 3. Storage directory bestaat niet
De `storage/app/public/avatars` directory bestaat niet.
**Oplossing**: `php artisan storage:fix-permissions`

### 4. SELinux blokkeert (alleen op CentOS/RHEL)
```bash
chcon -R -t httpd_sys_rw_content_t storage/
chcon -R -t httpd_sys_rw_content_t public/storage
```

### 5. Open_basedir restrictie
Check `php.ini` of `.htaccess` voor `open_basedir` restricties die storage path blokkeren.

## Verificatie Commands

```bash
# Check of symlink correct is
ls -la public/storage
# Moet tonen: storage -> ../storage/app/public

# Check storage permissions
namei -l storage/app/public/avatars

# Test write access
touch storage/app/public/avatars/test.txt
rm storage/app/public/avatars/test.txt

# Check webserver user
ps aux | grep -E 'apache|nginx|httpd'
```

## Laravel Storage Configuratie

De app gebruikt `public` disk gedefinieerd in `config/filesystems.php`:

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

Avatar URLs worden gegenereerd met:
```php
Storage::disk('public')->url('avatars/' . $filename)
// Resulteert in: https://hannesbonami.be/storage/avatars/filename.jpg
```

## Quick Fix Checklist
- [ ] `php artisan storage:diagnose` uitgevoerd
- [ ] `php artisan storage:fix-permissions` uitgevoerd
- [ ] Permissies zijn 755 voor directories
- [ ] Webserver user is owner van storage
- [ ] Symlink `public/storage` bestaat en is correct
- [ ] Avatar upload test succesvol
- [ ] Avatar is zichtbaar in browser
- [ ] Browser cache geleegd

## Contact
Bij problemen: check de output van `php artisan storage:diagnose` en deel deze info.
