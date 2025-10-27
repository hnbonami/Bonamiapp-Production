# ✅ DASHBOARD ROL & TOEGANG - COMPLETE FIX

## 🎯 Wat is er aangepakt?

Ik heb een **complete security overhaul** gedaan van het dashboard systeem met focus op:

### 1. **Organisatie Filtering** ⚡
- Elk widget heeft nu `organisatie_id` 
- Users zien alleen widgets van hun eigen organisatie
- Super admin ziet **alleen** organisatie ID 1

### 2. **Policy-based Authorization** 🔒
- Nieuwe `DashboardWidgetPolicy` voor alle rechten
- Checks op view, create, update, delete, drag, resize
- Geregistreerd in `AuthServiceProvider`

### 3. **Rol-gebaseerde Rechten** 👥

| Rol | Drag & Drop | Resize | Create | Edit | Delete |
|-----|-------------|--------|--------|------|--------|
| **Klant** | ✅ Ja | ❌ Nee | ❌ Nee | ❌ Nee | ❌ Nee |
| **Medewerker** | ✅ Ja | ✅ Eigen | ✅ Ja | ✅ Eigen | ✅ Eigen |
| **Admin** | ✅ Ja | ✅ Alles | ✅ Ja | ✅ Alles | ✅ Alles |
| **Super Admin** | ✅ Ja (org 1) | ✅ Alles (org 1) | ✅ Ja (org 1) | ✅ Alles (org 1) | ✅ Alles (org 1) |

### 4. **JavaScript Permissions** 🎮
- Per widget worden drag/resize rechten dynamisch gezet
- Gridstack krijgt `noMove` en `noResize` flags
- Console logging voor debugging

---

## 📁 Aangemaakte/Geüpdatete Files

### ✅ Nieuwe Files:
1. `app/Policies/DashboardWidgetPolicy.php` - Volledige autorisatie
2. `database/migrations/2024_01_20_000001_add_organisatie_id_to_dashboard_widgets.php` - Database update
3. `resources/views/dashboard/edit.blade.php` - Widget bewerken pagina
4. `DASHBOARD_ROLES_FIX.md` - Uitgebreide documentatie
5. `ROUTES_EXAMPLE.md` - Voorbeeld routes

### ✅ Geüpdatete Files:
1. `app/Models/DashboardWidget.php` - Scopes + helper methods
2. `app/Http/Controllers/DashboardController.php` - Policy checks + filtering
3. `resources/views/dashboard/index.blade.php` - Per-widget rechten in JS
4. `app/Providers/AuthServiceProvider.php` - Policy registratie
5. `database/seeders/DashboardWidgetSeeder.php` - Organisatie_id support

---

## 🚀 Installatie (VERPLICHT!)

### Stap 1: Run migration
```bash
php artisan migrate
```

### Stap 2: Update bestaande widgets
```bash
php artisan tinker
```

```php
// Voeg organisatie_id toe aan bestaande widgets
DB::table('dashboard_widgets')->whereNull('organisatie_id')->get()->each(function($widget) {
    $user = DB::table('users')->find($widget->created_by);
    if ($user) {
        DB::table('dashboard_widgets')
            ->where('id', $widget->id)
            ->update(['organisatie_id' => $user->organisatie_id]);
    }
});

// Verificatie
DB::table('dashboard_widgets')->select('id', 'title', 'organisatie_id')->get();
```

### Stap 3: Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Stap 4: Routes toevoegen
Voeg deze routes toe aan `routes/web.php`:

```php
use App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/widgets/create', [DashboardController::class, 'create'])->name('dashboard.widgets.create');
    Route::post('/dashboard/widgets', [DashboardController::class, 'store'])->name('dashboard.widgets.store');
    Route::get('/dashboard/widgets/{widget}/edit', [DashboardController::class, 'edit'])->name('dashboard.widgets.edit');
    Route::put('/dashboard/widgets/{widget}', [DashboardController::class, 'update'])->name('dashboard.widgets.update');
    Route::post('/dashboard/widgets/layout', [DashboardController::class, 'updateLayout'])->name('dashboard.widgets.updateLayout');
    Route::delete('/dashboard/widgets/{widget}', [DashboardController::class, 'destroy'])->name('dashboard.widgets.destroy');
});
```

---

## 🧪 Test Checklist

### ✅ Klant Testen:
- [ ] Kan widgets zien van eigen organisatie
- [ ] Kan widgets drag & droppen
- [ ] Kan widgets **NIET** resizen (geen handles)
- [ ] Ziet **GEEN** edit/delete knoppen
- [ ] Ziet **GEEN** "Widget toevoegen" knop

### ✅ Medewerker Testen:
- [ ] Kan eigen widgets aanmaken
- [ ] Kan eigen widgets bewerken/verwijderen
- [ ] Kan eigen widgets resizen
- [ ] Kan widgets van anderen **NIET** bewerken
- [ ] Kan alle widgets drag & droppen

### ✅ Admin Testen:
- [ ] Kan alle widgets binnen organisatie bewerken
- [ ] Kan alle widgets verwijderen
- [ ] Ziet widgets van andere organisaties **NIET**
- [ ] Kan widgets aanmaken

### ✅ Super Admin Testen:
- [ ] Ziet **ALLEEN** widgets van organisatie 1
- [ ] Kan widgets van org 2+ **NIET** zien (403 error)
- [ ] Nieuw aangemaakte widgets krijgen organisatie_id = 1

---

## 🔍 Debugging Tips

### Console Logs (Browser F12):
```javascript
// Kijk naar deze logs:
🔐 Widget permissions: [{id: 1, canResize: true, canDrag: true}, ...]
🔧 Fixing widget sizes from database...
Widget 1: Forcing 4x3 at (0,0) {canResize: true, canDrag: true}
✅ All widgets configured with correct permissions!
```

### Database Check:
```bash
php artisan tinker
>>> DashboardWidget::with('organisatie')->get(['id','title','organisatie_id'])
```

### Policy Check:
```php
// In tinker:
$user = User::find(1);
$widget = DashboardWidget::find(1);

Gate::allows('update', $widget) // true/false
Gate::allows('resize', $widget) // true/false
```

---

## 🎨 Visuele Indicatoren

### Klant:
- Geen resize handles
- Geen edit/delete knoppen
- Alleen minimize/maximize knop

### Medewerker (eigen widget):
- Resize handles zichtbaar
- Edit + delete knoppen
- Drag cursor op header

### Medewerker (andermans widget):
- Geen resize handles
- Geen edit/delete knoppen
- Wel drag & drop mogelijk

### Admin:
- Alles zichtbaar voor alle widgets
- Volledige controle

---

## ⚠️ Belangrijke Notes

1. **Super admin beperking**: Super admin mag ALLEEN widgets zien/bewerken van organisatie ID 1. Dit is een security feature.

2. **Klanten mogen niet resizen**: Dit is bewust om de layout consistent te houden. Ze mogen wel drag & droppen.

3. **Medewerkers eigen widgets**: Medewerkers kunnen alleen hun eigen widgets bewerken/verwijderen, tenzij ze admin zijn.

4. **Organisatie isolatie**: Widgets zijn volledig geïsoleerd per organisatie. Geen cross-organisatie toegang.

---

## 📊 Database Schema

```sql
dashboard_widgets:
  - id
  - type (text, metric, image, button, chart)
  - title
  - content
  - created_by (FK users.id)
  - organisatie_id (FK organisaties.id) ⚡ NIEUW
  - grid_x, grid_y, grid_width, grid_height
  - visibility (everyone, medewerkers, only_me)
  - is_active
  - timestamps
```

---

## 🐛 Troubleshooting

### Probleem: "Policy not found"
**Oplossing:**
```bash
php artisan cache:clear
php artisan optimize:clear
```

### Probleem: Widgets van andere organisaties zichtbaar
**Oplossing:**
```bash
php artisan tinker
>>> DB::table('dashboard_widgets')->whereNull('organisatie_id')->count()
# Moet 0 zijn, anders run stap 2 uit installatie
```

### Probleem: Resize werkt niet
**Oplossing:** Check browser console voor permissions log. Moet `canResize: true` tonen.

### Probleem: Super admin ziet alles
**Oplossing:**
```php
// Check user organisatie_id
User::where('role', 'superadmin')->first()->organisatie_id
// Moet 1 zijn
```

---

## ✨ Wat werkt nu?

✅ **Organisatie filtering** - Widgets per organisatie geïsoleerd  
✅ **Super admin beperking** - Alleen organisatie 1  
✅ **Policy checks** - Op alle controller actions  
✅ **Drag & drop rechten** - Per widget dynamisch  
✅ **Resize rechten** - Klanten kunnen niet resizen  
✅ **Edit/delete knoppen** - Alleen bij toestemming  
✅ **JavaScript permissions** - Real-time checks  
✅ **Logging** - Alle acties gelogd  

---

## 📚 Extra Resources

- **Volledige documentatie**: Zie `DASHBOARD_ROLES_FIX.md`
- **Route voorbeelden**: Zie `ROUTES_EXAMPLE.md`
- **Policy logic**: Zie `app/Policies/DashboardWidgetPolicy.php`

---

**🎉 Klaar voor productie!**

Test alles grondig met alle rollen voordat je live gaat.

Bij vragen of problemen, check eerst de logs in `storage/logs/laravel.log`.
