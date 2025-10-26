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
        // Haal eerste admin user op
        $admin = User::where('role', 'admin')->orWhere('role', 'super_admin')->first();
        
        if (!$admin) {
            $this->command->warn('Geen admin user gevonden. Widgets worden aangemaakt zonder creator.');
        }

        // 1. Welkom widget (text)
        DashboardWidget::create([
            'type' => 'text',
            'title' => 'Welkom bij Bonami Sportcoaching! 🚴‍♂️',
            'content' => "Welkom op je persoonlijke dashboard!\n\nHier vind je alle belangrijke informatie over je klanten, bikefits en prestaties. Pas dit dashboard aan naar jouw wensen door widgets te verslepen en te vergroten/verkleinen.\n\nSucces!",
            'background_color' => '#e0f2fe',
            'text_color' => '#0c4a6e',
            'grid_x' => 0,
            'grid_y' => 0,
            'grid_width' => 8,
            'grid_height' => 3,
            'visibility' => 'everyone',
            'created_by' => $admin?->id,
            'is_active' => true,
        ]);

        // 2. Totaal klanten metric
        DashboardWidget::create([
            'type' => 'metric',
            'title' => 'Totaal Klanten',
            'content' => \App\Models\Klant::count(),
            'background_color' => '#fef3e2',
            'text_color' => '#ea580c',
            'grid_x' => 8,
            'grid_y' => 0,
            'grid_width' => 4,
            'grid_height' => 3,
            'visibility' => 'medewerkers',
            'created_by' => $admin?->id,
            'is_active' => true,
        ]);

        // 3. Quick action: Nieuwe klant toevoegen
        DashboardWidget::create([
            'type' => 'button',
            'title' => 'Snelle Acties',
            'button_text' => '+ Nieuwe Klant Toevoegen',
            'button_url' => '/klanten/create',
            'background_color' => '#c8e1eb',
            'text_color' => '#111111',
            'grid_x' => 0,
            'grid_y' => 3,
            'grid_width' => 4,
            'grid_height' => 2,
            'visibility' => 'medewerkers',
            'created_by' => $admin?->id,
            'is_active' => true,
        ]);

        // 4. Quick action: Analytics
        DashboardWidget::create([
            'type' => 'button',
            'title' => 'Analytics',
            'button_text' => '📊 Bekijk Analytics Dashboard',
            'button_url' => '/analytics',
            'background_color' => '#dbeafe',
            'text_color' => '#111111',
            'grid_x' => 4,
            'grid_y' => 3,
            'grid_width' => 4,
            'grid_height' => 2,
            'visibility' => 'medewerkers',
            'created_by' => $admin?->id,
            'is_active' => true,
        ]);

        // 5. Actieve medewerkers metric
        DashboardWidget::create([
            'type' => 'metric',
            'title' => 'Actieve Medewerkers',
            'content' => User::where('role', 'medewerker')->where('status', 'Actief')->count(),
            'background_color' => '#d1fae5',
            'text_color' => '#065f46',
            'grid_x' => 8,
            'grid_y' => 3,
            'grid_width' => 4,
            'grid_height' => 2,
            'visibility' => 'medewerkers',
            'created_by' => $admin?->id,
            'is_active' => true,
        ]);

        // 6. Tip widget voor klanten
        DashboardWidget::create([
            'type' => 'text',
            'title' => '💡 Tips & Trucs',
            'content' => "• Sleep widgets om ze te verplaatsen\n• Vergroot of verklein widgets naar wens\n• Klik op het ▼ icoon om een widget in te klappen\n• Vraag je medewerker om extra widgets toe te voegen!",
            'background_color' => '#fef9c3',
            'text_color' => '#713f12',
            'grid_x' => 0,
            'grid_y' => 5,
            'grid_width' => 6,
            'grid_height' => 3,
            'visibility' => 'everyone',
            'created_by' => $admin?->id,
            'is_active' => true,
        ]);

        // 7. Recente activiteit
        DashboardWidget::create([
            'type' => 'text',
            'title' => '📅 Recent',
            'content' => "Bekijk je recente activiteiten:\n\n• Laatste bikefit: " . (\App\Models\Bikefit::latest()->first()?->datum ?? 'Nog geen bikefits') . "\n• Nieuwste klant: " . (\App\Models\Klant::latest()->first()?->naam ?? 'Nog geen klanten'),
            'background_color' => '#f3f4f6',
            'text_color' => '#111827',
            'grid_x' => 6,
            'grid_y' => 5,
            'grid_width' => 6,
            'grid_height' => 3,
            'visibility' => 'medewerkers',
            'created_by' => $admin?->id,
            'is_active' => true,
        ]);

        $this->command->info('✅ ' . DashboardWidget::count() . ' dashboard widgets aangemaakt!');
    }
}