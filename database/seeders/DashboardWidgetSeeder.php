<?php

namespace Database\Seeders;

use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Database\Seeder;

class DashboardWidgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Haal eerste admin user op (of maak er een aan)
        $admin = User::where('role', 'admin')->first() 
            ?? User::where('role', 'superadmin')->first()
            ?? User::first();

        if (!$admin) {
            $this->command->error('Geen users gevonden. Run eerst UserSeeder.');
            return;
        }

        $organisatieId = $admin->organisatie_id ?? 1;

        // Standaard widgets aanmaken
        $widgets = [
            [
                'type' => 'text',
                'title' => 'Welkom bij Bonami Dashboard',
                'content' => 'Welkom op je persoonlijke dashboard! Hier vind je een overzicht van al je belangrijke statistieken en informatie.',
                'background_color' => '#c8e1eb',
                'text_color' => '#111',
                'grid_x' => 0,
                'grid_y' => 0,
                'grid_width' => 6,
                'grid_height' => 3,
                'visibility' => 'everyone',
                'created_by' => $admin->id,
                'organisatie_id' => $organisatieId,
                'is_active' => true,
            ],
            [
                'type' => 'metric',
                'title' => 'Totaal Klanten',
                'content' => '0', // Wordt dynamisch bijgewerkt
                'background_color' => '#3b82f6',
                'text_color' => '#fff',
                'grid_x' => 6,
                'grid_y' => 0,
                'grid_width' => 3,
                'grid_height' => 3,
                'visibility' => 'medewerkers',
                'created_by' => $admin->id,
                'organisatie_id' => $organisatieId,
                'is_active' => true,
            ],
            [
                'type' => 'metric',
                'title' => 'Bikefits Deze Maand',
                'content' => '0', // Wordt dynamisch bijgewerkt
                'background_color' => '#10b981',
                'text_color' => '#fff',
                'grid_x' => 9,
                'grid_y' => 0,
                'grid_width' => 3,
                'grid_height' => 3,
                'visibility' => 'medewerkers',
                'created_by' => $admin->id,
                'organisatie_id' => $organisatieId,
                'is_active' => true,
            ],
            [
                'type' => 'button',
                'title' => 'Nieuwe Klant Toevoegen',
                'button_text' => '➕ Klant Toevoegen',
                'button_url' => '/klanten/create',
                'background_color' => '#f59e0b',
                'text_color' => '#fff',
                'grid_x' => 0,
                'grid_y' => 3,
                'grid_width' => 4,
                'grid_height' => 2,
                'visibility' => 'medewerkers',
                'created_by' => $admin->id,
                'organisatie_id' => $organisatieId,
                'is_active' => true,
            ],
            [
                'type' => 'button',
                'title' => 'Nieuwe Bikefit',
                'button_text' => '🚴 Bikefit Toevoegen',
                'button_url' => '/bikefits/create',
                'background_color' => '#8b5cf6',
                'text_color' => '#fff',
                'grid_x' => 4,
                'grid_y' => 3,
                'grid_width' => 4,
                'grid_height' => 2,
                'visibility' => 'medewerkers',
                'created_by' => $admin->id,
                'organisatie_id' => $organisatieId,
                'is_active' => true,
            ],
            [
                'type' => 'chart',
                'title' => 'Diensten Verdeling',
                'chart_type' => 'doughnut',
                'chart_data' => json_encode([
                    'chart_type' => 'diensten',
                    'scope' => 'auto',
                    'periode' => 'laatste-30-dagen'
                ]),
                'background_color' => '#fff',
                'text_color' => '#111',
                'grid_x' => 0,
                'grid_y' => 5,
                'grid_width' => 6,
                'grid_height' => 4,
                'visibility' => 'medewerkers',
                'created_by' => $admin->id,
                'organisatie_id' => $organisatieId,
                'is_active' => true,
            ],
            [
                'type' => 'chart',
                'title' => 'Omzet Trend',
                'chart_type' => 'line',
                'chart_data' => json_encode([
                    'chart_type' => 'omzet',
                    'scope' => 'auto',
                    'periode' => 'laatste-90-dagen'
                ]),
                'background_color' => '#fff',
                'text_color' => '#111',
                'grid_x' => 6,
                'grid_y' => 5,
                'grid_width' => 6,
                'grid_height' => 4,
                'visibility' => 'medewerkers',
                'created_by' => $admin->id,
                'organisatie_id' => $organisatieId,
                'is_active' => true,
            ],
        ];

        foreach ($widgets as $widgetData) {
            DashboardWidget::updateOrCreate(
                [
                    'title' => $widgetData['title'],
                    'organisatie_id' => $organisatieId
                ],
                $widgetData
            );
        }

        $this->command->info('✅ Dashboard widgets aangemaakt voor organisatie ' . $organisatieId);
    }
}