<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DashboardWidget;

class UpdateWidgetZichtbaarheidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update alle bestaande widgets naar 'iedereen' zodat ze gedeeld worden
        $updated = DashboardWidget::query()->update([
            'zichtbaarheid' => 'iedereen'
        ]);

        $this->command->info("✅ {$updated} widgets bijgewerkt naar zichtbaarheid 'iedereen'");
    }
}
