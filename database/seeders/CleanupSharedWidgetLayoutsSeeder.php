<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DashboardWidget;
use Illuminate\Support\Facades\DB;

class CleanupSharedWidgetLayoutsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Haal alle gedeelde widgets op
        $gedeeldeWidgets = DashboardWidget::whereIn('zichtbaarheid', ['alle_medewerkers', 'iedereen'])->get();
        
        $deletedCount = 0;
        
        foreach ($gedeeldeWidgets as $widget) {
            // Verwijder alle user-specifieke layouts voor gedeelde widgets
            $deleted = DB::table('dashboard_widget_layouts')
                ->where('widget_id', $widget->id)
                ->delete();
            
            $deletedCount += $deleted;
            
            $this->command->info("🗑️  Widget {$widget->id} ({$widget->title}): {$deleted} persoonlijke layouts verwijderd");
        }

        $this->command->info("✅ Totaal {$deletedCount} persoonlijke layouts verwijderd voor gedeelde widgets");
        $this->command->info("💡 Gedeelde widgets gebruiken nu alleen de widget defaults (4x3)");
    }
}
