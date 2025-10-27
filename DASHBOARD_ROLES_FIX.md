# 🔒 Dashboard Rol & Toegang Update - CRITICAL FIX

## ⚠️ Wat is er gefixed?

Deze update lost alle rol-gebaseerde toegangsproblemen op in het dashboard systeem:

1. **Organisatie filtering** - Widgets worden nu correct gefilterd per organisatie
2. **Super admin beperking** - Super admin ziet alleen organisatie ID 1
3. **Policy implementation** - Volledige autorisatie via Laravel Policy
4. **Drag & drop rechten** - Per rol en per widget correct ingesteld
5. **Resize rechten** - Klanten kunnen NIET resizen, alleen drag & droppen

---

## 🚀 Installatie Stappen

### 1. Run de migration
```bash
php artisan migrate
```

Dit voegt `organisatie_id` toe aan de `dashboard_widgets` tabel.

### 2. Update bestaande widgets (eenmalig)
```bash
php artisan tinker
```

```php
// Voeg organisatie_id toe aan bestaande widgets op basis van creator
DB::table('dashboard_widgets')->whereNull('organisatie_id')->get()->each(function($widget) {
    $user = DB::table('users')->find($widget->created_by);
    if ($user) {
        DB::table('dashboard_widgets')
            ->where('id', $widget->id)
            ->update(['organisatie_id' => $user->organisatie_id]);
    }
});

// Check resultaat
DB::table('dashboard_widgets')->select('id', 'title', 'organisatie_id', 'created_by')->get();
```

### 3. Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🔑 Rollen & Rechten Matrix

| Rol | Widgets Zien | Drag & Drop | Resize | Aanmaken | Bewerken | Verwijderen |
|-----|--------------|-------------|--------|----------|----------|-------------|
| **Klant** | Eigen org | ✅ JA | ❌ NEE | ❌ NEE | ❌ NEE | ❌ NEE |
| **Medewerker** | Eigen org | ✅ JA | ✅ Eigen | ✅ JA | ✅ Eigen | ✅ Eigen |
| **Admin** | Eigen org | ✅ JA | ✅ Alles | ✅ JA | ✅ Alles | ✅ Alles |
| **Super Admin** | Org ID 1 | ✅ JA | ✅ Alles | ✅ JA | ✅ Alles | ✅ Alles |

### Toelichting:
- **Eigen** = Alleen widgets die ze zelf hebben aangemaakt
- **Alles** = Alle widgets binnen hun organisatie
- **Org ID 1** = Super admin ziet alleen widgets van organisatie 1

---

## 📁 Aangepaste Files

### Nieuwe Files:
1. `app/Policies/DashboardWidgetPolicy.php` - Volledige autorisatie logica
2. `database/migrations/2024_01_20_000001_add_organisatie_id_to_dashboard_widgets.php` - Database update

### Geüpdatete Files:
1. `app/Models/DashboardWidget.php` - Scopes + helper methods
2. `app/Http/Controllers/DashboardController.php` - Policy checks + organisatie filtering
3. `resources/views/dashboard/index.blade.php` - Per-widget rechten in JavaScript
4. `app/Providers/AuthServiceProvider.php` - Policy registratie

---

## 🧪 Test Scenario's

### Test 1: Klant mag alleen drag & droppen
```
1. Log in als klant
2. Probeer widget te verslepen → WERKT
3. Probeer widget te resizen → WERKT NIET (geen handles zichtbaar)
4. Probeer widget te bewerken → WERKT NIET (geen edit knop)
5. Probeer widget toe te voegen → WERKT NIET (geen knop zichtbaar)
```

### Test 2: Medewerker mag eigen widgets beheren
```
1. Log in als medewerker
2. Maak nieuwe widget aan → WERKT
3. Bewerk eigen widget → WERKT
4. Verwijder eigen widget → WERKT
5. Probeer widget van andere medewerker te bewerken → WERKT NIET
```

### Test 3: Admin mag alles binnen organisatie
```
1. Log in als admin
2. Zie alle widgets van eigen organisatie → WERKT
3. Bewerk widget van medewerker → WERKT
4. Verwijder widget van medewerker → WERKT
5. Probeer widget van andere organisatie te zien → WERKT NIET
```

### Test 4: Super admin ziet alleen org 1
```
1. Log in als super admin
2. Zie alleen widgets van organisatie ID 1 → WERKT
3. Probeer widget van organisatie 2 te openen → WERKT NIET (403)
4. Maak widget aan → Wordt aangemaakt in organisatie 1
```

---

## 🐛 Troubleshooting

### Widgets worden niet gefilterd per organisatie
```bash
# Check of organisatie_id is gevuld
php artisan tinker
>>> DB::table('dashboard_widgets')->whereNull('organisatie_id')->count()
# Moet 0 zijn

# Vul aan indien nodig (zie stap 2 hierboven)
```

### "Policy not found" error
```bash
# Clear cache en registreer policy opnieuw
php artisan cache:clear
php artisan optimize:clear

# Check of policy is geregistreerd
php artisan route:list | grep dashboard
```

### Resize werkt niet voor medewerkers
```bash
# Check console in browser:
# F12 → Console → Kijk naar "Widget permissions" log
# Moet tonen: canResize: true voor eigen widgets
```

### Super admin ziet widgets van andere organisaties
```bash
# Check user organisatie_id
php artisan tinker
>>> User::where('role', 'superadmin')->first()->organisatie_id
# Moet 1 zijn

# Update indien nodig
>>> User::where('role', 'superadmin')->update(['organisatie_id' => 1])
```

---

## 📊 Database Schema

### dashboard_widgets tabel
```sql
- id
- type
- title
- content
- created_by (user_id)
- organisatie_id (⚡ NIEUW - foreign key naar organisaties)
- is_active
- grid_x, grid_y, grid_width, grid_height
- created_at, updated_at
```

### dashboard_user_layouts tabel
```sql
- id
- user_id
- widget_id
- grid_x, grid_y, grid_width, grid_height
- is_visible
- created_at, updated_at
```

---

## 🔐 Security Checklist

- [x] Widgets gefilterd per organisatie
- [x] Super admin beperkt tot organisatie 1
- [x] Policy checks op alle controller methods
- [x] Drag rechten per widget gecheckt
- [x] Resize rechten per widget gecheckt
- [x] Edit/Delete knoppen alleen bij toestemming
- [x] API endpoints beschermd met policy
- [x] Logging van alle acties

---

## 📝 Code Voorbeelden

### Check of user widget mag resizen (Model)
```php
$widget = DashboardWidget::find(1);
if ($widget->canBeResizedBy(auth()->user())) {
    // User mag resizen
}
```

### Check via Policy (Controller)
```php
$this->authorize('update', $widget);
$this->authorize('resize', $widget);
```

### Scope voor organisatie filtering
```php
// Haal alleen widgets van user's organisatie op
$widgets = DashboardWidget::visibleFor(auth()->user())->get();
```

---

## ⚡ Performance Tips

1. **Eager loading**: Widgets worden geladen met `->with('creator')` om N+1 queries te voorkomen
2. **Index op organisatie_id**: Migration voegt automatisch index toe
3. **Caching**: Overweeg caching van layouts per user (toekomstige update)

---

## 🎯 Volgende Stappen

### Optionele Verbeteringen:
1. **Widget templates** - Vooraf gemaakte widgets voor nieuwe organisaties
2. **Widget delen** - Widgets delen tussen medewerkers
3. **Widget export/import** - Layouts exporteren en importeren
4. **Audit logging** - Wie heeft wat aangepast tracking
5. **Widget scheduling** - Tijdelijke widgets (bijv. vakantie bericht)

---

## 📞 Support

Bij problemen:
1. Check eerst de logs: `storage/logs/laravel.log`
2. Enable query logging tijdelijk voor debugging
3. Check browser console voor JavaScript errors
4. Gebruik `php artisan tinker` om data te inspecteren

---

**Laatste update:** {{ now()->format('d-m-Y H:i') }}  
**Versie:** 2.0.0 - Security & Roles Update
