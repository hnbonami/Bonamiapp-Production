# 📊 Dashboard Widget Systeem - Complete Feature Set

## ✅ Wat is er aangemaakt:

### 1. **Database Structuur**
- `dashboard_widgets` tabel - alle widget data
- `dashboard_user_layouts` tabel - persoonlijke layouts per gebruiker

### 2. **Models**
- `DashboardWidget` - widget functionaliteit
- `DashboardUserLayout` - user-specific layouts

### 3. **Controllers**
- `DashboardController` - alle CRUD operaties
- `DashboardStatsController` - **NIEUW**: Live data & real-time statistieken

### 4. **Policy**
- `DashboardWidgetPolicy` - authorization per role

### 5. **Views & Components**
- `dashboard/index.blade.php` - hoofd dashboard met Gridstack
- `dashboard/create.blade.php` - widget aanmaken
- `components/calendar-widget.blade.php` - **NIEUW**: Kalender met afspraken
- `components/quick-stat.blade.php` - **NIEUW**: Mini grafieken/sparklines

### 6. **JavaScript Features**
- `public/js/darkmode.js` - **NIEUW**: Dark mode met auto-detect
- Gridstack drag & drop
- Chart.js grafieken
- Real-time updates

### 7. **Seeder**
- `DashboardWidgetSeeder` - standaard widgets

---

## 🚀 Installatie Stappen

### Stap 1: Migrations Draaien
```bash
php artisan migrate
```

### Stap 2: Seeder Draaien (optioneel)
```bash
php artisan db:seed --class=DashboardWidgetSeeder
```

### Stap 3: Routes Toevoegen
Voeg toe aan `routes/web.php`:

```php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardStatsController;

// Dashboard routes - nieuw widget systeem
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/widgets/create', [DashboardController::class, 'create'])->name('dashboard.widgets.create');
    Route::post('/dashboard/widgets', [DashboardController::class, 'store'])->name('dashboard.widgets.store');
    Route::post('/dashboard/widgets/layout', [DashboardController::class, 'updateLayout'])->name('dashboard.widgets.updateLayout');
    Route::post('/dashboard/widgets/{widget}/toggle', [DashboardController::class, 'toggleVisibility'])->name('dashboard.widgets.toggle');
    Route::delete('/dashboard/widgets/{widget}', [DashboardController::class, 'destroy'])->name('dashboard.widgets.destroy');
    
    // Live stats API endpoints
    Route::get('/dashboard/stats/live', [DashboardStatsController::class, 'getLiveStats'])->name('dashboard.stats.live');
    Route::get('/dashboard/stats/widget', [DashboardStatsController::class, 'getWidgetData'])->name('dashboard.stats.widget');
    Route::get('/dashboard/calendar/events', [DashboardStatsController::class, 'getCalendarEvents'])->name('dashboard.calendar.events');
});
```

### Stap 4: Dark Mode Script Toevoegen
Voeg toe aan je `layouts/app.blade.php` voor de `</body>` tag:

```html
<!-- Dark Mode Script -->
<script src="{{ asset('js/darkmode.js') }}"></script>

<!-- Dark Mode Toggle Button (in navbar) -->
<button id="dark-mode-toggle" style="background:transparent;border:none;cursor:pointer;padding:0.5em;" aria-label="Toggle dark mode">
    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" fill="currentColor"/>
    </svg>
</button>
```
Voeg toe aan `app/Providers/AuthServiceProvider.php`:

```php
use App\Models\DashboardWidget;
use App\Policies\DashboardWidgetPolicy;

protected $policies = [
    DashboardWidget::class => DashboardWidgetPolicy::class,
];
```

### Stap 5: Navigatie Link Toevoegen
Voeg link toe in je navigatie menu (bijv. `layouts/app.blade.php`):

```html
<a href="{{ route('dashboard.index') }}" class="nav-link">
    📊 Dashboard
</a>
```

---

## 🎨 Features

### Voor KLANTEN:
- ✅ Widgets bekijken
- ✅ Widgets verslepen (drag & drop)
- ❌ Widgets resizen (uitgezet voor klanten)
- ❌ Widgets aanmaken
- ❌ Widgets verwijderen

### Voor MEDEWERKERS:
- ✅ Widgets bekijken
- ✅ Widgets verslepen
- ✅ Widgets resizen
- ✅ Widgets aanmaken
- ✅ Eigen widgets verwijderen
- ✅ Visibility instellen (iedereen/medewerkers/alleen ik)

### Voor ADMINS:
- ✅ Alle medewerker rechten
- ✅ Alle widgets verwijderen (ook van anderen)
- ✅ Alle widgets binnen organisatie beheren

### Voor SUPER ADMINS:
- ✅ Alle rechten
- ✅ Widgets over organisaties heen beheren

---

## 📦 Widget Types

1. **📝 Text Widget**
   - Vrije tekst
   - Markdown support
   - Welkomstberichten, tips, etc.

2. **📈 Metric Widget**
   - Grote getallen
   - KPI's tonen
   - Statistieken
   - **NIEUW**: Met trend indicators (+/- %)
   - **NIEUW**: Automatische metrics uit database:
     
     **Voor Medewerkers:**
     - 🚴 Mijn Bikefits - Aantal bikefits door jou uitgevoerd
     - 💪 Mijn Inspanningstests - Aantal inspanningstests door jou
     - 👥 Mijn Klanten - Aantal klanten toegewezen aan jou
     - 💰 Mijn Omzet (Deze Maand) - Jouw omzet deze maand
     - 📊 Mijn Omzet (Dit Kwartaal) - Jouw omzet dit kwartaal
     
     **Voor Admins (extra):**
     - 👥 Totaal Klanten - Alle klanten in organisatie
     - 🚴 Totaal Bikefits - Alle bikefits in organisatie
     - ✨ Nieuwe Klanten (Deze Maand) - Recent toegevoegde klanten
     - 💰 Organisatie Omzet (Deze Maand) - Totale omzet deze maand
     - 📈 Organisatie Omzet (Dit Kwartaal) - Totale omzet dit kwartaal
     - 👨‍💼 Actieve Medewerkers - Aantal actieve staff
     
   - **Privacy**: Medewerkers zien alleen hun eigen data
   - **Live Updates**: Data wordt automatisch bijgewerkt

3. **🖼️ Image Widget**
   - Upload afbeeldingen
   - Logo's, foto's, etc.

4. **🔘 Button Widget**
   - Link naar delen van app
   - Quick actions
   - Shortcuts

5. **📊 Chart Widget**
   - Line, Bar, Pie, Doughnut
   - Gebruikt Chart.js
   - **NIEUW**: Live data uit database

6. **📅 Calendar Widget** ✨ **NIEUW**
   - Maandoverzicht
   - Afspraken per dag
   - Navigatie tussen maanden
   - Klikbare events

7. **⚡ Quick Stats** ✨ **NIEUW**
   - Mini sparkline grafieken
   - Trend visualisatie
   - Compacte statistieken
   - Real-time updates

---

## ✨ NIEUWE FEATURES

### 🔴 Live Data & Real-time Updates
- Automatische refresh van statistieken
- Live counter updates
- Recent activity feed
- Database-driven metrics

**Gebruik:**
```php
// In je widget content:
{{ $liveStats['total_klanten'] }}
{{ $liveStats['nieuwe_klanten_deze_week'] }}
```

**API Endpoints:**
- `/dashboard/stats/live` - Alle live statistieken
- `/dashboard/stats/widget?type=klanten_trend` - Specifieke widget data

### 📅 Calendar Widget
Toont kalender met afspraken en events.

**Gebruik in dashboard:**
```php
@if($widget->type === 'calendar')
    <x-calendar-widget :widgetId="$widget->id" />
@endif
```

**Features:**
- ✅ Maandweergave met navigatie
- ✅ Highlight vandaag
- ✅ Klikbare dagen
- ✅ Events lijst
- ✅ Auto-refresh (5 min)

### ⚡ Quick Stats met Sparklines
Mini grafieken voor snelle statistiek overzicht.

**Gebruik:**
```php
<x-quick-stat 
    title="Nieuwe Klanten"
    value="{{ $stats['nieuwe_klanten'] }}"
    trend="12"
    :trendUp="true"
    :sparklineData="[10, 15, 12, 18, 20, 25, 22]"
    color="#c8e1eb"
/>
```

**Features:**
- ✅ Canvas-based sparklines
- ✅ Trend indicators (↑↓)
- ✅ Percentage change
- ✅ Custom kleuren
- ✅ Responsive

### 🌙 Dark Mode ✨ **GEÏMPLEMENTEERD**
Volledig dark mode systeem voor de hele applicatie!

**Features:**
- ✅ Automatische system preference detectie
- ✅ Manual toggle button in navbar
- ✅ LocalStorage persistentie
- ✅ Keyboard shortcut (Ctrl/Cmd + Shift + D)
- ✅ Smooth transitions
- ✅ Werkt op ALLE pagina's
- ✅ Bonami brand colors aangepast voor dark mode
- ✅ Charts, widgets, forms volledig supported

**Installatie:** Zie `DARK_MODE_INSTALL.md` voor volledige instructies.

**Quick start:**
1. Dark mode files zijn al aangemaakt
2. Voeg `<x-dark-mode-toggle />` toe aan je navbar
3. Klaar! Test met maan-icon of `Ctrl+Shift+D`

**Features:**
- ✅ Toggle button in navbar
- ✅ System preference detectie
- ✅ LocalStorage persistentie
- ✅ Smooth transitions
- ✅ Widget-aware
- ✅ Custom dark theme voor Bonami colors

**Activeren:**
1. Include `darkmode.js` in layout
2. Voeg toggle button toe
3. Auto-detect werkt direct!

**Keyboard shortcut:** `Ctrl/Cmd + Shift + D` (optioneel toe te voegen)

---

## 🛠️ Technische Details

### Libraries Gebruikt:
- **Gridstack.js** - Drag & drop grid systeem
- **Chart.js** - Grafieken rendering
- **Tailwind CSS** - Styling

### Database Schema:
```sql
dashboard_widgets:
- id
- type (chart/text/image/button/metric)
- title
- content
- chart_type, chart_data
- image_path
- button_text, button_url
- background_color, text_color
- grid_x, grid_y, grid_width, grid_height
- visibility (everyone/medewerkers/only_me)
- created_by
- is_active

dashboard_user_layouts:
- id
- user_id
- widget_id
- grid_x, grid_y, grid_width, grid_height
- is_visible
```

---

## 🔧 Aanpassingen

### Custom Kleuren Toevoegen:
Edit in `dashboard/create.blade.php`:
```html
<input type="color" name="background_color" value="#jouwkleur">
```

### Extra Button Routes:
Edit in `dashboard/create.blade.php` de `<select name="button_url">` dropdown

### Custom Widget Types:
1. Voeg type toe in `DashboardWidget` model constanten
2. Update validation in `DashboardController`
3. Voeg rendering toe in `dashboard/index.blade.php`

---

## 🐛 Troubleshooting

### Widgets worden niet opgeslagen:
- Check CSRF token
- Check browser console voor JS errors
- Verify `Storage::disk('public')` is correct geconfigureerd

### Drag & drop werkt niet:
- Check of Gridstack.js correct is geladen
- Open browser console voor errors
- Verify role permissions (klanten mogen niet resizen)

### Charts worden niet getoond:
- Check of Chart.js is geladen
- Verify chart_data is valid JSON
- Check browser console voor errors

---

## 🚀 Volgende Stappen

### Nog te implementeren:
1. **Live data voor charts** - Connect naar Analytics data
2. **Widget templates** - Vooraf gemaakte widgets
3. **Export/Import layouts** - Deel layouts tussen users
4. **Widget scheduling** - Toon widgets op specifieke tijden
5. **Notifications widget** - Real-time meldingen
6. **Calendar widget** - Afspraken overzicht
7. **Weather widget** - Lokale weer info

### Suggesties:
- [ ] Dark mode support
- [ ] Mobile responsive verbeteren
- [ ] Widget presets (templates)
- [ ] Undo/Redo functionaliteit
- [ ] Keyboard shortcuts
- [ ] Widget zoekfunctie
- [ ] Widget categorieën

---

## 📝 Credits

Gebouwd voor Bonami Sportcoaching
Laravel 12 + Gridstack.js + Chart.js + TailwindCSS

**Laatste update:** {{ now()->format('d-m-Y') }}
**Versie:** 1.0.0