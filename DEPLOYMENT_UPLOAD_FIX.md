# Deployment Instructies - Upload Fix

## Probleem
Uploads werken lokaal maar niet in productie door verkeerde mappen structuur.

## Oplossing - Server Configuratie

### 1. Mappen Aanmaken via SSH/FTP
```bash
cd /pad/naar/je/webroot/public
mkdir -p uploads/avatars
mkdir -p uploads/documenten
chmod -R 755 uploads
```

### 2. Rechten Instellen
```bash
# Zorg dat de webserver (meestal www-data of nginx) eigenaar is
chown -R www-data:www-data uploads/
# OF voor sommige shared hosting:
chown -R [jouw-username]:[jouw-username] uploads/
```

### 3. Bestaande Avatars Migreren (indien nodig)
Als je bestaande avatars hebt in `storage/app/public/avatars`:
```bash
# Kopieer bestaande avatars naar nieuwe locatie
cp -r storage/app/public/avatars/* public/uploads/avatars/
```

### 4. Database Update (indien nodig)
Als bestaande klanten al avatar_path hebben met 'avatars/filename.jpg':
- Deze paden blijven werken omdat de code backwards compatible is
- Nieuwe uploads krijgen automatisch het correcte pad

### 5. Test Upload Functionaliteit
1. Log in als beheerder
2. Ga naar een klant profiel
3. Upload een nieuwe avatar
4. Controleer of de avatar zichtbaar is
5. Upload een document
6. Download het document

### 6. Logging Controleren
```bash
tail -f storage/logs/laravel.log
```
Kijk naar berichten zoals:
- "Avatar succesvol geüpload"
- "Document succesvol geüpload"
- Eventuele error berichten

## Environment Variables (.env)
Zorg dat deze correct staan:
```
APP_ENV=production
APP_URL=https://www.performancepulse.be
```

## Troubleshooting

### Probleem: "Permission denied"
```bash
chmod -R 755 public/uploads
chown -R www-data:www-data public/uploads
```

### Probleem: Avatars tonen niet
1. Controleer of bestand bestaat: `public/uploads/avatars/avatar_X_timestamp.jpg`
2. Controleer file permissions: `ls -la public/uploads/avatars/`
3. Test directe URL: `https://www.performancepulse.be/uploads/avatars/[filename]`

### Probleem: Upload faalt zonder error
1. Controleer PHP upload limits in `php.ini`:
   - `upload_max_filesize = 10M`
   - `post_max_size = 10M`
2. Check Apache/Nginx body size limits

### Probleem: 404 bij download
1. Controleer of `.htaccess` in `public/uploads/` aanwezig is
2. Controleer Apache mod_rewrite is enabled

## Verificatie Checklist
- [ ] `public/uploads/avatars/` directory bestaat
- [ ] `public/uploads/documenten/` directory bestaat
- [ ] Directories hebben 755 permissions
- [ ] Webserver is eigenaar van directories
- [ ] `.htaccess` in uploads directory
- [ ] Test avatar upload werkt
- [ ] Test document upload werkt
- [ ] Test document download werkt
- [ ] Bestaande avatars blijven zichtbaar

## Code Changes Summary
1. ✅ `config/filesystems.php` - Nieuwe 'uploads' disk toegevoegd
2. ✅ `KlantController@update` - Productie-compatibele avatar upload
3. ✅ `KlantController@storeDocument` - Productie-compatibele document upload
4. ✅ `KlantController@downloadDocument` - Correcte bestandspaden
5. ✅ `public/uploads/.htaccess` - Beveiliging uploads directory
6. ✅ `show.blade.php` - Avatar URL generatie blijft backwards compatible
