# GitHub Copilot Instructies - Bonami Sportcoaching App

## 🎯 Project Context
Dit is een Laravel 12 applicatie voor Bonami Sportcoaching, gespecialiseerd in bikefit metingen, inspanningstests en klantenbeheer voor wielrenners en sporters.

## 📋 Algemene Richtlijnen

### Taal en Communicatie
- **Altijd Nederlands gebruiken** in comments, variabelnamen, en documentatie
- Code responses altijd in het Nederlands geven
- Gebruik Nederlandse terminologie voor bikefit/sport concepten
- Variabelnamen mogen Engels zijn voor standaard Laravel conventies

### Code Kwaliteit
- Volg PSR-12 coding standards
- Gebruik expliciete type hints waar mogelijk
- Schrijf duidelijke, Nederlandse comments voor complexe logica
- Implementeer proper error handling met try-catch blokken
- Log belangrijke acties met `\Log::info()` of `\Log::error()`

### Laravel Best Practices
- Gebruik Eloquent ORM in plaats van raw SQL queries
- Implementeer Form Request validation voor complexe formulieren
- Gebruik Resource Controllers voor CRUD operaties
- Maak gebruik van Laravel's ingebouwde authenticatie
- Gebruik route model binding waar mogelijk

### Database & Models
- Database tabelnamen in het Nederlands (bijv. `klanten`, `bikefits`, `testzadels`)
- Gebruik meaningful foreign key names (bijv. `klant_id`, `bikefit_id`)
- Implementeer proper relationships in Eloquent models
- Gebruik migrations voor alle database wijzigingen

## 🚴‍♂️ Domein-specifieke Kennis

### Bikefit Terminologie
- `bikefit` = fietshouding meting en optimalisatie
- `zadel_trapas_hoek` = hoek tussen zadel en trapas
- `stuur_trapas_afstand` = afstand van stuur tot trapas
- `binnenbeenlengte` = inseam measurement
- `mobiliteit` = flexibiliteit metingen
- `testzadel` = uitleenzadel voor klanten

### Sleutel Entiteiten
- **Klant**: klantgegevens en contactinfo
- **Bikefit**: bikefit metingen en resultaten
- **Inspanningstest**: inspannings- en prestatiemetingen
- **Testzadel**: uitleensysteem voor fietszadels
- **Sjabloon**: rapport templates voor PDF generatie
- **Medewerker**: staff gebruikers

### Belangrijke Functionaliteiten
- Klantenbeheer met GDPR compliance
- Bikefit calculator met aangepaste waarden
- PDF rapport generatie met sjablonen
- Email automations voor herinneringen
- Upload systeem voor documenten en foto's
- Mobiliteitstabellen en berekeningen

## 🛠️ Technische Specificaties

### Frontend
- Blade templating engine
- TailwindCSS voor styling
- JavaScript voor interactiviteit (geen frameworks)
- Responsive design verplicht

### Backend Services
- `BikefitCalculator` voor metingen en berekeningen
- `BikefitReportGenerator` voor PDF generatie
- `EmailIntegrationService` voor email automations
- `SjabloonHelper` voor template matching

### Belangrijke Controllers
- `BikefitController` - bikefit CRUD en berekeningen
- `KlantController` - klantenbeheer
- `SjablonenController` - rapport templates
- `TestzadelsController` - uitleensysteem

### Database Structuur
- Gebruik `klant_id` voor klant foreign keys
- Implementeer soft deletes waar nodig
- Timestamp columns: `created_at`, `updated_at`
- Nederlandse kolomnamen voor domein-specifieke velden

## 🔒 Beveiliging & Privacy
- Gebruik Laravel's ingebouwde CSRF bescherming
- Implementeer proper authorization checks
- GDPR compliance voor klantgegevens
- Valideer alle user input
- Gebruik `auth()` helper voor gebruikerscontext

## 📊 Rapportage & PDF
- Gebruik DomPDF voor PDF generatie
- Implementeer Puppeteer als fallback voor complexe layouts
- Sjabloon-gebaseerde rapporten met placeholder vervanging
- Print-vriendelijke CSS voor rapportweergave

## 🎨 UI/UX Richtlijnen
- Gebruik Bonami brand kleuren (blauw/wit thema)
- Implementeer gebruiksvriendelijke formulieren
- Responsive design voor alle devices
- Duidelijke navigatie en breadcrumbs
- Success/error messaging met Laravel flash sessions

## 🚫 Vermijd
- Complexe JavaScript frameworks (Vue/React)
- Raw SQL queries (gebruik Eloquent)
- Hardcoded waarden (gebruik config files)
- Inline CSS (gebruik TailwindCSS classes)
- English comments in Nederlandse codebase
- Direct file system manipulation (gebruik Laravel Storage)

## 📝 Code Voorbeelden

### Typische Controller Method
```php
public function store(Request $request, Klant $klant)
{
    $validated = $request->validate([
        'datum' => 'required|date',
        'testtype' => 'required|string',
        // ... meer validatie
    ]);

    $validated['klant_id'] = $klant->id;
    $validated['user_id'] = auth()->id();

    $bikefit = Bikefit::create($validated);

    return redirect()->route('bikefit.show', [
        'klant' => $klant->id, 
        'bikefit' => $bikefit->id
    ])->with('success', 'Bikefit succesvol aangemaakt.');
}
```

### Nederlandse Comments
```php
// Bereken de optimale zadelhoek op basis van binnenbeenlengte
$optimaleZadelhoek = $this->berekenZadelhoek($bikefit->binnenbeenlengte_cm);

// Sla aangepaste waarden op voor dit bikefit
$calculator->saveCustomResults($bikefit, 'na', $customValues);
```

## 🔄 Workflow
1. Analyseer de bestaande code structuur
2. Volg Laravel conventions en projectpatterns
3. Test lokaal voordat je wijzigingen voorstelt
4. Documenteer complexe logica in het Nederlands
5. Zorg voor backward compatibility bij wijzigingen

---
*Deze instructies helpen Copilot om consistente, kwalitatieve code te genereren die past bij de Bonami Sportcoaching applicatie.*