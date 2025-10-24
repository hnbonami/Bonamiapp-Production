# Feature Toegang Systeem - Debug & Implementatie Guide

## 🔍 Probleem Diagnose

Het probleem was dat organisatie ID 2 alle features kon zien, terwijl deze organisatie maar beperkte features toegewezen heeft.

## ✅ Oplossingen Geïmplementeerd

### 1. **User Model** - Feature Check Methodes
- `hasFeatureAccess($featureKey)` - Check of user toegang heeft tot een feature
- `hasAnyFeatureAccess($featureKeys)` - Check op meerdere features (OR logica)
- Superadmin heeft altijd toegang tot alles

### 2. **Organisatie Model** - Enhanced hasFeature()
- Debug logging toegevoegd om te zien waarom features wel/niet werken
- Controleert `is_actief` status en `expires_at` datum
- Superadmin organisatie (ID 1) heeft altijd alle features

### 3. **Middleware** - CheckFeatureAccess
- Nieuwe middleware `feature:key` voor route bescherming
- Blokkeert toegang als organisatie geen toegang heeft
- Geeft duidelijke foutmelding met 403 error

### 4. **Routes** - Feature Bescherming
Alle routes zijn nu beschermd met feature middleware:
```php
Route::middleware(['auth', 'feature:klantenbeheer'])->group(function () {
    Route::resource('klanten', KlantController::class);
});

Route::middleware(['auth', 'feature:bikefits'])->group(function () {
    // Bikefit routes
});
```

### 5. **Blade Directive** - @hasFeature
Nieuwe Blade directive voor in views:
```blade
@hasFeature('klantenbeheer')
    <a href="{{ route('klanten.index') }}">Klanten</a>
@endhasFeature
```

### 6. **Sidebar Navigatie** - Voorbeeld
Zie `sidebar-navigation-example.blade.php` voor complete voorbeeldcode van hoe je de sidebar moet aanpassen.

### 7. **Debug Commando**
Nieuw Artisan commando om features te controleren:
```bash
php artisan organisatie:check-features 2
```

## 🚀 Hoe Te Testen

### Stap 1: Check huidige features van organisatie 2
```bash
php artisan organisatie:check-features 2
```

Dit toont:
- Alle features
- Welke features actief zijn
- Of organisatie toegang heeft
- Vervaldatum indien van toepassing

### Stap 2: Toggle features in admin panel
1. Log in als superadmin
2. Ga naar organisatie detail pagina
3. Toggle features aan/uit via de switches
4. Check de browser console voor logs

### Stap 3: Test als gebruiker van organisatie 2
1. Log in met een account van organisatie 2
2. Probeer naar verschillende pagina's te gaan
3. Je zou alleen toegang moeten hebben tot actieve features
4. Bij geen toegang krijg je een 403 foutmelding

### Stap 4: Check de logs
```bash
tail -f storage/logs/laravel.log | grep "Feature check"
```

Dit toont:
- Welke features gecontroleerd worden
- Of organisatie toegang heeft
- Aantal actieve features

## 🔧 Je Eigen Navigatie Aanpassen

Pas je eigen sidebar/navigatie bestand aan met de voorbeeldcode uit `sidebar-navigation-example.blade.php`.

Vervang bijvoorbeeld:
```blade
<!-- OUD -->
<a href="{{ route('klanten.index') }}">Klanten</a>

<!-- NIEUW -->
@hasFeature('klantenbeheer')
<a href="{{ route('klanten.index') }}">Klanten</a>
@endhasFeature
```

## 📊 Feature Keys Mapping

| Feature Naam | Feature Key | Route Middleware |
|-------------|-------------|------------------|
| Klantenbeheer | `klantenbeheer` | `feature:klantenbeheer` |
| Bikefit Metingen | `bikefits` | `feature:bikefits` |
| Inspanningstesten | `inspanningstesten` | `feature:inspanningstesten` |
| Veldtesten | `veldtesten` | `feature:veldtesten` |
| Testzadel Uitleensysteem | `testzadels` | `feature:testzadels` |
| Sjablonen | `sjablonen` | `feature:sjablonen` |
| Medewerkerbeheer | `medewerkerbeheer` | `feature:medewerkerbeheer` |
| Instagram | `instagram` | `feature:instagram` |
| Nieuwsbrief | `nieuwsbrief` | `feature:nieuwsbrief` |
| Database Tools | `database_tools` | `feature:database_tools` |
| API Toegang | `api_toegang` | `feature:api_toegang` |
| Analytics Dashboard | `analytics` | `feature:analytics` |
| Custom Branding | `custom_branding` | `feature:custom_branding` |

## ⚠️ Belangrijke Opmerkingen

1. **Superadmin** (role = 'superadmin') heeft ALTIJD toegang tot alles
2. **Organisatie ID 1** heeft ALTIJD toegang tot alles (= Bonami hoofdorganisatie)
3. Feature checks gebeuren op **organisatie niveau**, niet user niveau
4. Features met `expires_at` datum worden automatisch uitgeschakeld na vervaldatum
5. Toggle switch werkt real-time via AJAX

## 🐛 Debug Tips

Als features niet werken zoals verwacht:

1. **Check de database**:
```sql
SELECT * FROM organisatie_features WHERE organisatie_id = 2;
```

2. **Check de logs**:
```bash
tail -f storage/logs/laravel.log | grep "Feature\|🔍"
```

3. **Check in Tinker**:
```bash
php artisan tinker
$org = Organisatie::find(2);
$org->hasFeature('klantenbeheer');
$org->features()->wherePivot('is_actief', true)->pluck('key');
```

4. **Forceer cache clear**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## ✅ Checklist Implementatie

- [x] User model feature check methodes
- [x] Organisatie model enhanced hasFeature
- [x] Middleware CheckFeatureAccess aangemaakt
- [x] Middleware geregistreerd in Kernel
- [x] Routes beschermd met middleware
- [x] Blade directive @hasFeature toegevoegd
- [x] Voorbeeld navigatie code gemaakt
- [x] Debug commando gemaakt
- [ ] **JE MOET NOG DOEN**: Eigen sidebar/navigatie aanpassen met @hasFeature checks
- [ ] **JE MOET NOG DOEN**: Testen met organisatie 2 account

## 🎯 Volgende Stappen

1. Pas je eigen navigatie/sidebar aan met `@hasFeature` checks
2. Test inloggen met organisatie 2 account
3. Controleer of alleen actieve features zichtbaar zijn
4. Run het debug commando om te verifiëren: `php artisan organisatie:check-features 2`
5. Check de logs voor eventuele problemen

Succes! 🚀