# 📄 Rapporten Opmaken Feature - Installatie Instructies

## 🚀 **SNELLE START** (5 minuten setup)

```bash
# 1. Database setup
php artisan migrate
php artisan db:seed --class=RapportenOpmakenFeatureSeeder

# 2. Storage link (voor uploads)
php artisan storage:link

# 3. QR Code package (optioneel)
composer require simplesoftwareio/simple-qrcode

# 4. Feature activeren (via tinker)
php artisan tinker
```

```php
// In tinker:
$feature = App\Models\Feature::where('key', 'rapporten_opmaken')->first();
$organisatie = App\Models\Organisatie::find(1); // Pas ID aan
$organisatie->features()->attach($feature->id, ['is_actief' => true]);
exit
```

**Klaar!** Ga naar `/admin` en klik op "Rapporten Configureren" 🎉

---

## ✅ Wat is er toegevoegd?

Deze feature stelt organisatie admins in staat om rapporten volledig te personaliseren met:
- **Header & Footer** tekst op elke pagina
- **Logo** en **Voorblad foto** uploads
- **Primaire & Secundaire kleuren** voor branding
- **Lettertype keuze** (Arial, Tahoma, Calibri, Helvetica)
- **Paginanummering** aan/uit met positie keuze
- **Contactgegevens** (adres, telefoon, email, website)
- **QR Code** met custom URL
- **Disclaimer tekst** voor juridische zaken
- **Inleidende & laatste blad tekst**

## 🚀 Installatie Stappen

### 1. Database Migratie Uitvoeren
```bash
php artisan migrate
```

Dit maakt de `organisatie_rapport_instellingen` tabel aan.

### 2. Feature Seeder Uitvoeren
```bash
php artisan db:seed --class=RapportenOpmakenFeatureSeeder
```

Dit maakt de `rapporten_opmaken` feature aan in de database.

### 3. Feature Activeren voor een Organisatie

#### Via Tinker (development):
```bash
php artisan tinker
```

```php
// Zoek de feature
$feature = App\Models\Feature::where('key', 'rapporten_opmaken')->first();

// Zoek de organisatie (bijvoorbeeld ID 1)
$organisatie = App\Models\Organisatie::find(1);

// Activeer de feature voor deze organisatie
$organisatie->features()->attach($feature->id, [
    'is_actief' => true,
    'expires_at' => null, // Of een datum: '2025-12-31'
    'notities' => 'Geactiveerd voor testing'
]);
```

#### Via Admin Panel (future):
Dit kan later via het admin panel worden gedaan als er een feature management UI is.

### 4. Storage Link Aanmaken (voor uploads)
```bash
php artisan storage:link
```

Dit maakt een symbolic link naar de `storage/app/public` directory zodat uploads zichtbaar zijn.

### 5. QR Code Package Installeren (optioneel)
```bash
composer require simplesoftwareio/simple-qrcode
```

Voor QR code functionaliteit.

## 📋 Gebruik

### Voor Organisatie Admins:

1. Ga naar `/admin` dashboard
2. Klik op de **"Rapporten Opmaken"** tegel (alleen zichtbaar als feature actief is)
3. Configureer je rapport instellingen via de tabs:
   - **Algemeen**: Header, Footer, Lettertype, Paginanummering
   - **Branding**: Logo, Voorblad foto, Kleuren
   - **Teksten**: Inleidende tekst, Laatste blad, Disclaimer
   - **Contact**: Contactgegevens & QR Code

### Voor Superadmins (Sjablonen):

Bij het maken/bewerken van sjablonen zijn nieuwe variabelen beschikbaar:

**Rapport Instellingen Variabelen:**
- `{{rapport.header}}` - Header tekst
- `{{rapport.footer}}` - Footer tekst
- `{{rapport.logo}}` - Logo (IMG tag)
- `{{rapport.voorblad_foto}}` - Voorblad foto (IMG tag)
- `{{rapport.inleidende_tekst}}` - Inleidende tekst
- `{{rapport.laatste_blad_tekst}}` - Laatste blad tekst
- `{{rapport.disclaimer}}` - Disclaimer tekst
- `{{rapport.primaire_kleur}}` - Primaire kleur (HEX)
- `{{rapport.secundaire_kleur}}` - Secundaire kleur (HEX)
- `{{rapport.lettertype}}` - Lettertype
- `{{rapport.contactgegevens}}` - Contactgegevens (HTML)
- `{{rapport.contact_adres}}` - Contact adres
- `{{rapport.contact_telefoon}}` - Contact telefoon
- `{{rapport.contact_email}}` - Contact email
- `{{rapport.contact_website}}` - Contact website
- `{{rapport.qr_code}}` - QR Code (IMG tag)
- `{{rapport.paginanummer}}` - Huidige paginanummer

Deze variabelen worden **automatisch vervangen** bij het genereren van rapporten.

## 🔒 Beveiliging

- **Toegang**: Alleen `admin`, `organisatie_admin`, `superadmin` rollen
- **Feature Toggle**: Tegel is alleen zichtbaar als organisatie de feature heeft
- **Organisatie Isolation**: Elke organisatie ziet alleen eigen instellingen
- **File Validatie**: Uploads worden gevalideerd (type, grootte)

## 🎨 Default Waarden (Performance Pulse)

Als een organisatie geen rapport instellingen heeft geconfigureerd, worden deze defaults gebruikt:

- **Primaire Kleur**: `#c8e1eb` (Bonami blauw)
- **Secundaire Kleur**: `#111111` (Bonami zwart)
- **Lettertype**: `Arial`
- **Header**: "Performance Pulse Rapport"
- **Footer**: "© 2024 Performance Pulse - Sportcoaching"
- **Paginanummering**: Rechtsonder, aan

## ✅ Backwards Compatibility

- **Bestaande sjablonen blijven 100% werken**
- **Nieuwe variabelen zijn optioneel** - als ze niet in sjabloon staan, gebeurt er niets
- **Default waarden zorgen voor fallback** - geen lege rapporten
- **Organisaties zonder feature** krijgen Performance Pulse branding

## 📝 Voorbeeld Sjabloon Gebruik

```html
<div style="font-family: {{rapport.lettertype}}; color: {{rapport.secundaire_kleur}};">
    <header style="background-color: {{rapport.primaire_kleur}}; padding: 20px;">
        {{rapport.logo}}
        <h1>{{rapport.header}}</h1>
    </header>
    
    <div class="content">
        <div class="voorblad">
            {{rapport.voorblad_foto}}
            {{rapport.inleidende_tekst}}
        </div>
        
        <!-- Jouw rapport content hier -->
        
        <div class="laatste-pagina">
            {{rapport.laatste_blad_tekst}}
            {{rapport.disclaimer}}
            {{rapport.qr_code}}
        </div>
    </div>
    
    <footer>
        {{rapport.footer}}
        {{rapport.contactgegevens}}
        <span>Pagina {{rapport.paginanummer}}</span>
    </footer>
</div>
```

## 🐛 Troubleshooting

### Tegel niet zichtbaar?
- Check of feature actief is: `$organisatie->hasFeature('rapporten_opmaken')`
- Check of user admin rol heeft
- Clear cache: `php artisan cache:clear`

### Uploads werken niet?
- Check `storage:link`: `php artisan storage:link`
- Check permissions: `chmod -R 775 storage`
- Check `.env`: `FILESYSTEM_DISK=public`

### QR Code werkt niet?
- Installeer package: `composer require simplesoftwareio/simple-qrcode`
- Check `qr_code_url` is gevuld en valide URL

## 📞 Support

Bij problemen, check de logs:
```bash
tail -f storage/logs/laravel.log
```

---

**Versie**: 1.0  
**Datum**: Januari 2024  
**Auteur**: Bonami App Development Team
