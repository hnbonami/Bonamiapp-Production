<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DashboardWidget;

class FixWidgetGridSizesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update alle widgets met correcte grid waarden (4x3 is standaard)
        $widgets = DashboardWidget::all();
        
        foreach ($widgets as $widget) {
            $widget->update([
                'grid_x' => $widget->grid_x ?? 0,
                'grid_y' => $widget->grid_y ?? 0,
                'grid_width' => ($widget->grid_width && $widget->grid_width > 1) ? $widget->grid_width : 4,  // Min 4 kolommen breed
                'grid_height' => ($widget->grid_height && $widget->grid_height > 1) ? $widget->grid_height : 3, // Min 3 rijen hoog
            ]);
            
            $this->command->info("✅ Widget {$widget->id} ({$widget->title}): {$widget->grid_width}x{$widget->grid_height}");
        }

        $this->command->info("✅ {$widgets->count()} widgets bijgewerkt met correcte grid groottes (min 4x3)");
    }
}
