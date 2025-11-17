# 🎯 Inspanningstesten Zones Configuratie - Installatie Handleiding

## 📦 Wat is er toegevoegd?

Deze feature voegt een **volledig configureerbaar trainingszones systeem** toe aan de Bonami app, waarmee je custom zones templates kunt maken voor verschillende sporttypes en berekeningsbases.

### ✨ Features:
- ✅ Meerdere zone templates per organisatie
- ✅ Systeem templates (Bonami, Karvonen, FTP)
- ✅ Custom templates maken/bewerken/verwijderen
- ✅ Live preview bij configureren
- ✅ Ondersteunt fietsen, lopen, of beide
- ✅ Flexibele berekeningsbases (LT1, LT2, MAX, FTP, Custom)
- ✅ Visuele zone weergave met kleuren

---

## 🚀 Installatie Stappen

### 1️⃣ Voeg Routes Toe

Open `routes/web.php` en voeg dit toe binnen de `auth` middleware group:

```php
// Inspanningstesten Instellingen Routes
Route::prefix('admin/inspanningstesten')->name('admin.inspanningstesten.')->group(function () {
    Route::get('/instellingen', [App\Http\Controllers\Admin\InspanningstestenInstellingenController::class, 'index'])->name('instellingen');
    Route::get('/templates/create', [App\Http\Controllers\Admin\InspanningstestenInstellingenController::class, 'create'])->name('create');
    Route::post('/templates', [App\Http\Controllers\Admin\InspanningstestenInstellingenController::class, 'store'])->name('store');
    Route::get('/templates/{id}/edit', [App\Http\Controllers\Admin\InspanningstestenInstellingenController::class, 'edit'])->name('edit');
    Route::put('/templates/{id}', [App\Http\Controllers\Admin\InspanningstestenInstellingenController::class, 'update'])->name('update');
    Route::delete('/templates/{id}', [App\Http\Controllers\Admin\InspanningstestenInstellingenController::class, 'destroy'])->name('destroy');
});
```

### 2️⃣ Run Database Migrations

```bash
php artisan migrate
```

Dit maakt de volgende tabellen aan:
- `trainings_zones_templates` - Voor zone templates
- `trainings_zones` - Voor individuele zones per template

### 3️⃣ Seed Standaard Zones (Optioneel maar Aanbevolen)

```bash
php artisan db:seed --class=BonaminZonesSeeder
```

Dit voegt 3 standaard templates toe:
- **Bonami Standaard Zones** (LT2 basis) - 7 zones
- **Karvonen Hartslag Zones** (MAX basis) - 5 zones  
- **Cycling Power Zones** (FTP basis) - 6 zones

---

## 📁 Aangemaakte Bestanden

### Database
- `database/migrations/2024_01_15_000001_create_trainings_zones_templates_table.php`
- `database/migrations/2024_01_15_000002_create_trainings_zones_table.php`
- `database/seeders/BonaminZonesSeeder.php`

### Models
- `app/Models/TrainingsZonesTemplate.php`
- `app/Models/TrainingsZone.php`

### Controllers
- `app/Http/Controllers/Admin/InspanningstestenInstellingenController.php`

### Views
- `resources/views/admin/inspanningstesten/index.blade.php`
- `resources/views/admin/inspanningstesten/create.blade.php`
- `resources/views/admin/inspanningstesten/edit.blade.php`

---

## 🎯 Gebruik

### Navigeer naar Instellingen
Ga naar: `/admin/inspanningstesten/instellingen`

### Nieuwe Template Maken
1. Klik op "Nieuwe Zone Template"
2. Vul template informatie in (naam, sport type, berekeningsbasis)
3. Voeg zones toe met de "➕ Zone Toevoegen" knop
4. Configureer elke zone:
   - Zone naam (bijv. "Herstel", "Threshold")
   - Min/Max percentages
   - Kleur (color picker)
   - Referentie waarde (optioneel: LT1, LT2, MAX, etc.)
5. Bekijk live preview rechts
6. Klik "💾 Template Opslaan"

### Template Bewerken
- Klik op "✏️ Bewerken" bij een custom template
- Pas aan en sla op
- ⚠️ Systeem templates kunnen NIET bewerkt worden

### Template Verwijderen
- Klik op "🗑️ Verwijderen" bij een custom template
- Bevestig de actie
- ⚠️ Systeem templates kunnen NIET verwijderd worden

---

## 🏗️ Database Structuur

### `trainings_zones_templates`
| Kolom | Type | Beschrijving |
|-------|------|--------------|
| id | bigint | Primary key |
| organisatie_id | bigint | Foreign key naar organisaties |
| naam | string | Template naam |
| sport_type | enum | 'fietsen', 'lopen', 'beide' |
| berekening_basis | enum | 'lt1', 'lt2', 'max', 'ftp', 'custom' |
| beschrijving | text | Optionele beschrijving |
| is_actief | boolean | Of template actief is |
| is_systeem | boolean | Systeem template (niet bewerkbaar) |

### `trainings_zones`
| Kolom | Type | Beschrijving |
|-------|------|--------------|
| id | bigint | Primary key |
| template_id | bigint | Foreign key naar templates |
| zone_naam | string | Naam van de zone |
| kleur | string | Hex kleurcode (#E3F2FD) |
| min_percentage | integer | Min % (0-200) |
| max_percentage | integer | Max % (0-200) |
| referentie_waarde | string | Optioneel (LT1, LT2, MAX, etc.) |
| volgorde | integer | Sorteervolgorde |
| beschrijving | text | Optionele beschrijving |

---

## 🔗 Eloquent Relaties

```php
// TrainingsZonesTemplate
$template->organisatie; // BelongsTo Organisatie
$template->zones;       // HasMany TrainingsZone

// TrainingsZone
$zone->template;        // BelongsTo TrainingsZonesTemplate
```

---

## 🎨 UI Features

### Index Pagina
- ✅ Grid layout met template cards
- ✅ Visuele zone preview bar
- ✅ Sport type badges
- ✅ Systeem vs Custom labels
- ✅ Bewerk/Verwijder acties

### Create/Edit Pagina
- ✅ 2-koloms layout (config + preview)
- ✅ Live preview met visuele bar
- ✅ Dynamisch zones toevoegen/verwijderen
- ✅ Color picker voor zones
- ✅ Validatie op alle velden

---

## 🔐 Beveiliging

- ✅ CSRF protection op alle forms
- ✅ Validatie op alle inputs
- ✅ Organisatie-scope (gebruikers zien alleen hun eigen templates + systeem templates)
- ✅ Systeem templates zijn read-only

---

## 🚀 Volgende Stappen

1. **Navigatie toevoegen**: Voeg een link toe in je admin menu naar `/admin/inspanningstesten/instellingen`

2. **Integratie met Inspanningstesten**: Gebruik de templates in je inspanningstesten rapportage:
   ```php
   $template = TrainingsZonesTemplate::with('zones')->find($id);
   foreach ($template->zones as $zone) {
       // Bereken zone waarden op basis van testresultaten
   }
   ```

3. **PDF Rapportage**: Voeg zones toe aan je PDF rapporten met de kleuren en percentages

4. **Klant specifieke zones**: Voeg eventueel toe dat klanten hun eigen voorkeurs-template kunnen kiezen

---

## 📝 Voorbeeld Gebruik in Code

```php
// Haal actieve templates op voor een organisatie
$templates = TrainingsZonesTemplate::with('zones')
    ->where('organisatie_id', $organisatie->id)
    ->orWhere('is_systeem', true)
    ->where('is_actief', true)
    ->get();

// Bereken zone waarden voor een inspanningstest
$template = TrainingsZonesTemplate::with('zones')->find($template_id);
$lt2_waarde = 280; // bijv. in Watt

foreach ($template->zones as $zone) {
    $min_watt = ($lt2_waarde * $zone->min_percentage) / 100;
    $max_watt = ($lt2_waarde * $zone->max_percentage) / 100;
    
    echo "{$zone->zone_naam}: {$min_watt}W - {$max_watt}W\n";
}
```

---

## ✅ Checklist

- [ ] Routes toegevoegd aan `web.php`
- [ ] Migrations uitgevoerd (`php artisan migrate`)
- [ ] Seeder uitgevoerd (optioneel: `php artisan db:seed --class=BonaminZonesSeeder`)
- [ ] Navigatie link toegevoegd in admin menu
- [ ] Getest: nieuwe template maken
- [ ] Getest: template bewerken
- [ ] Getest: template verwijderen

---

## 🎉 Klaar!

Je kunt nu volledig configureerbare trainingszones templates beheren in de Bonami app!

**Test URL**: `/admin/inspanningstesten/instellingen`

Bij vragen of problemen, check de code comments of Laravel logs! 🚀
