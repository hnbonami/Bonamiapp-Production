# 🔧 Dashboard Widget Fixes

## Fix 1: Chart Data (NULL probleem)

### Probleem
Chart widgets hebben `null` als chart_data waardoor ze niet laden.

### Oplossing
Run dit in `php artisan tinker`:

```php
// Fix alle chart widgets met null chart_data
DB::table('dashboard_widgets')
    ->where('type', 'chart')
    ->whereNull('chart_data')
    ->update([
        'chart_data' => json_encode([
            'chart_type' => 'diensten',
            'scope' => 'auto',
            'periode' => 'laatste-30-dagen'
        ])
    ]);

// Verificatie
$widgets = DB::table('dashboard_widgets')
    ->where('type', 'chart')
    ->select('id', 'title', 'chart_data')
    ->get();

foreach ($widgets as $widget) {
    echo "Widget {$widget->id}: {$widget->title} - ";
    $data = json_decode($widget->chart_data, true);
    echo isset($data['chart_type']) ? "✅ OK ({$data['chart_type']})" : "❌ NULL";
    echo "\n";
}
```

---

## Fix 2: Visibility Rechten

### Probleem
Widgets met `visibility = 'only_me'` worden getoond aan iedereen.

### Oplossing
De scope in het model is nu aangepast. Geen database changes nodig!

**Test:**
```php
// In tinker - test visibility filtering
$user = User::find(X); // Vervang X met een user ID
$widgets = DashboardWidget::visibleFor($user)->get();

echo "User {$user->name} ({$user->role}) ziet {$widgets->count()} widgets:\n";
foreach ($widgets as $w) {
    echo "- {$w->title} (visibility: {$w->visibility})\n";
}
```

**Verwacht gedrag:**
- **Klant**: Ziet alleen widgets met `visibility = 'everyone'`
- **Medewerker**: Ziet `everyone` + `medewerkers` + eigen `only_me` widgets
- **Admin**: Ziet `everyone` + `medewerkers` + eigen `only_me` widgets

---

## Fix 3: Medewerker 'Everyone' Widgets

### Probleem
Medewerkers kunnen widgets met `visibility = 'everyone'` aanmaken, maar dit zou alleen voor admins moeten zijn.

### Oplossing 1: Fix bestaande widgets
```php
// In tinker: Zoek widgets van medewerkers met 'everyone' visibility
use App\Models\User;
use App\Models\DashboardWidget;

$medewerkers = User::where('role', 'medewerker')->pluck('id');

$widgets = DashboardWidget::whereIn('created_by', $medewerkers)
    ->where('visibility', 'everyone')
    ->get();

echo "Gevonden: {$widgets->count()} widgets van medewerkers met 'everyone' visibility\n";

// Optioneel: Fix ze naar 'medewerkers'
$widgets->each(function($w) {
    $w->update(['visibility' => 'medewerkers']);
    echo "Fixed: {$w->title} (ID: {$w->id})\n";
});
```

### Oplossing 2: Via SQL
```sql
-- Fix medewerker widgets met 'everyone' visibility
UPDATE dashboard_widgets 
SET visibility = 'medewerkers'
WHERE visibility = 'everyone' 
AND created_by IN (
    SELECT id FROM users WHERE role = 'medewerker'
);
```

---

## Volledige Cleanup Script

```php
// Run in tinker voor complete cleanup

echo "🔧 Starting Dashboard Widgets Cleanup...\n\n";

// 1. Fix chart_data
echo "1️⃣ Fixing chart_data...\n";
$fixed = DB::table('dashboard_widgets')
    ->where('type', 'chart')
    ->whereNull('chart_data')
    ->update([
        'chart_data' => json_encode([
            'chart_type' => 'diensten',
            'scope' => 'auto',
            'periode' => 'laatste-30-dagen'
        ])
    ]);
echo "   ✅ Fixed {$fixed} chart widgets\n\n";

// 2. Fix medewerker 'everyone' visibility
echo "2️⃣ Fixing medewerker visibility...\n";
$medewerkerIds = User::where('role', 'medewerker')->pluck('id');
$fixed = DashboardWidget::whereIn('created_by', $medewerkerIds)
    ->where('visibility', 'everyone')
    ->update(['visibility' => 'medewerkers']);
echo "   ✅ Fixed {$fixed} medewerker widgets\n\n";

// 3. Verificatie
echo "3️⃣ Verification...\n";
$stats = [
    'total' => DashboardWidget::count(),
    'everyone' => DashboardWidget::where('visibility', 'everyone')->count(),
    'medewerkers' => DashboardWidget::where('visibility', 'medewerkers')->count(),
    'only_me' => DashboardWidget::where('visibility', 'only_me')->count(),
    'charts_ok' => DashboardWidget::where('type', 'chart')->whereNotNull('chart_data')->count(),
];

echo "   📊 Stats:\n";
echo "   - Total widgets: {$stats['total']}\n";
echo "   - Everyone: {$stats['everyone']}\n";
echo "   - Medewerkers: {$stats['medewerkers']}\n";
echo "   - Only Me: {$stats['only_me']}\n";
echo "   - Charts OK: {$stats['charts_ok']}\n";

echo "\n✅ Cleanup completed!\n";
```

---

## Na het fixen:
```bash
# Clear cache
php artisan cache:clear
php artisan view:clear

# Refresh de pagina
```

✅ Alle widgets zouden nu correct moeten werken!
