# 🎨 Custom Branding Feature - Documentatie

## Overzicht
De Custom Branding feature stelt organisaties in staat om hun eigen huisstijl toe te passen binnen de Bonami Sportcoaching applicatie. Dit omvat logo's, kleuren, lettertypen en styling voor rapporten en emails.

## Features

### 1. Logo Management
- **Hoofdlogo**: Wordt getoond in de navigatiebalk
- **Dark Mode Logo**: Optioneel alternatief logo voor dark mode
- **Klein Logo**: Geoptimaliseerd voor mobiele weergave
- **Favicon**: Custom browser icoon (32x32px)
- **Rapport Logo**: Specifiek logo voor PDF rapporten
- **Watermark**: Optioneel watermerk in rapporten

### 2. Kleurenschema
- **Primary Color**: Hoofdkleur voor knoppen en headers
- **Secondary Color**: Secundaire accentkleur
- **Accent Color**: Extra accent voor highlights
- **Text Color**: Standaard tekstkleur
- **Background Color**: Achtergrondkleur

### 3. Typografie
- **Heading Font**: Lettertype voor koppen
- **Body Font**: Lettertype voor normale tekst
- Ondersteunde fonts: Inter, Roboto, Montserrat, Poppins, Open Sans, Lato, Arial

### 4. Rapport Styling
- Custom header tekst voor rapporten
- Custom footer tekst voor rapporten
- Optioneel watermerk (met aan/uit toggle)
- Automatische toepassing van kleurenschema

### 5. Email Branding
- Custom logo in emails
- Custom header kleur
- Custom footer tekst
- Toggle om email branding aan/uit te zetten

### 6. Bedrijfsinformatie
- Bedrijfsnaam override (gebruikt organisatienaam als default)
- Tagline/slogan

## Toegangscontrole

### Wie heeft toegang?
- ✅ **SuperAdmin**: Kan feature activeren/deactiveren voor organisaties
- ✅ **Organisatie Admin**: Kan branding instellingen wijzigen
- ❌ **Medewerkers**: Kunnen branding niet wijzigen (alleen zien)
- ❌ **Klanten**: Kunnen branding niet wijzigen (alleen zien)

### Feature Activatie
De Custom Branding feature moet eerst geactiveerd worden door een SuperAdmin:
1. Ga naar Organisatie beheer
2. Bekijk organisatie details
3. Activeer "Custom Branding" feature (€12/mnd)

## Gebruik

### Branding Instellingen Beheren
1. Login als organisatie admin
2. Ga naar organisatie pagina
3. Klik op "🎨 Custom Branding" card
4. Klik op "Branding Beheren"
5. Upload logo's, kies kleuren, en pas instellingen aan
6. Klik "Instellingen Opslaan"

### Logo's Uploaden
- **Ondersteunde formaten**: PNG, JPG, JPEG, GIF
- **Max bestandsgrootte**: 
  - Logos: 2MB
  - Favicon: 512KB
- **Aanbevolen afmetingen**:
  - Hoofdlogo: 400x100px (landscape)
  - Favicon: 32x32px (vierkant)
  - Rapport logo: 600x150px

### Kleuren Kiezen
- Gebruik de color picker voor eenvoudige selectie
- Kleuren worden automatisch gesynchroniseerd
- Formaat: HEX code (bijv. #3B82F6)
- Live preview toont resultaat direct

## Technische Implementatie

### Database
Tabel: `organisatie_branding`
- Eén-op-één relatie met `organisaties` tabel
- Slaat alle branding configuratie op
- Automatische timestamps

### Models
- `OrganisatieBranding`: Hoofdmodel voor branding configuratie
- Relaties met `Organisatie` model
- Helper methods voor URL generatie

### Controllers
- `BrandingController`: Beheer van branding instellingen
- Routes onder `/branding` prefix
- Validatie en file upload handling

### Middleware
- `ApplyOrganisatieBranding`: Past branding toe op alle pagina's
- Deelt branding config met alle views
- Genereert CSS variabelen

### Views
- `branding/index.blade.php`: Hoofdpagina voor branding beheer
- Live preview van instellingen
- Drag & drop file uploads
- Color pickers met HEX waarde sync

### Helper Class
`App\Helpers\BrandingHelper`:
- `getBrandingForUser()`: Haal branding op voor user
- `getBrandingForOrganisatie()`: Haal branding op voor organisatie
- `getRapportCss()`: Genereer CSS voor rapporten
- `getRapportHeader()`: Genereer rapport header HTML
- `getRapportFooter()`: Genereer rapport footer HTML
- `getRapportWatermark()`: Genereer watermark HTML

## Integratie in Rapporten

### Bikefit Rapporten
```php
use App\Helpers\BrandingHelper;

$user = auth()->user();
$branding = BrandingHelper::getBrandingForUser($user);

$html = "
    <html>
        <head>
            " . BrandingHelper::getRapportCss($branding) . "
        </head>
        <body>
            " . BrandingHelper::getRapportHeader($branding, $organisatie) . "
            " . BrandingHelper::getRapportWatermark($branding) . "
            
            <!-- Rapport content hier -->
            
            " . BrandingHelper::getRapportFooter($branding, $organisatie) . "
        </body>
    </html>
";
```

### Inspanningstest Rapporten
Zelfde implementatie als bikefit rapporten.

### Email Templates
```php
// In email template blade:
@if(isset($organisatieBranding))
    <div style="background-color: {{ $organisatieBranding->email_header_color }};">
        <img src="{{ $organisatieBranding->logo_url }}" alt="Logo">
    </div>
@endif
```

## CSS Variabelen
De volgende CSS variabelen worden automatisch beschikbaar gesteld:
- `--primary-color`
- `--secondary-color`
- `--accent-color`
- `--text-color`
- `--background-color`
- `--heading-font`
- `--body-font`

Gebruik in custom CSS:
```css
.mijn-element {
    color: var(--primary-color);
    font-family: var(--heading-font);
}
```

## Reset Functionaliteit
Organisatie admins kunnen alle branding instellingen resetten:
1. Ga naar branding pagina
2. Klik "Reset naar Defaults"
3. Bevestig actie
4. Alle custom instellingen worden verwijderd
5. Standaard Bonami branding wordt hersteld

## Bestandsbeheer
- Alle uploads worden opgeslagen in `storage/app/public/branding/`
- Subdirectories: `logos/`, `favicons/`, `rapporten/`, `watermarks/`
- Bij verwijderen worden oude bestanden automatisch gewist
- Bij reset worden alle bestanden verwijderd

## Beveiliging
- Alle routes beschermd met `auth` en `verified` middleware
- Feature check: Alleen toegang als feature actief is
- Admin check: Alleen organisatie admins kunnen wijzigen
- File upload validatie (type, grootte)
- CSRF bescherming op alle formulieren

## Performance
- Branding config wordt gecached per request via middleware
- CSS variabelen worden één keer gegenereerd
- Logo's worden efficient geladen via Storage facade
- Geen database queries per view render

## Troubleshooting

### Logo wordt niet getoond
1. Check of bestand bestaat in `storage/app/public/branding/logos/`
2. Verifieer dat symbolic link werkt: `php artisan storage:link`
3. Check file permissions (755 voor directories, 644 voor files)

### Kleuren worden niet toegepast
1. Check of middleware is geregistreerd in Kernel.php
2. Verifieer dat branding feature actief is voor organisatie
3. Clear browser cache (Ctrl+Shift+R)

### Feature niet beschikbaar
1. Verifieer dat feature is geactiveerd door superadmin
2. Check `organisatie_features` tabel in database
3. Herstart applicatie: `php artisan optimize:clear`

## Toekomstige Uitbreidingen
- [ ] Dark mode theming support
- [ ] Meer lettertype opties
- [ ] Custom CSS editor voor gevorderden
- [ ] Meerdere kleurenschema's (presets)
- [ ] A/B testing voor verschillende brandings
- [ ] Export/import branding configuratie
- [ ] Preview mode voor verschillende devices

## Support
Voor vragen of problemen, neem contact op met het development team of maak een ticket aan in het issue tracking systeem.

---
Laatst bijgewerkt: Januari 2025
Versie: 1.0
