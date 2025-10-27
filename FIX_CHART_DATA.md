# 🔧 Fix Chart Data Script

## Probleem
Chart widgets hebben `null` als chart_data waardoor ze niet laden.

## Oplossing
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

## Of via SQL direct:

```sql
-- Fix null chart_data
UPDATE dashboard_widgets 
SET chart_data = '{"chart_type":"diensten","scope":"auto","periode":"laatste-30-dagen"}'
WHERE type = 'chart' 
AND (chart_data IS NULL OR chart_data = '');

-- Check resultaat
SELECT id, title, chart_data 
FROM dashboard_widgets 
WHERE type = 'chart';
```

## Na het fixen:
```bash
# Clear cache
php artisan cache:clear
php artisan view:clear

# Refresh de pagina
```

✅ Charts zouden nu moeten laden!
