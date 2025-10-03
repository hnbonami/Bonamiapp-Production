<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffNote;
use App\Models\User;

class DashboardTilesSeeder extends Seeder
{
    public function run()
    {
        // Vind de eerste admin user
        $adminUser = User::where('role', 'admin')->first() ?? User::first();
        
        if (!$adminUser) {
            $this->command->error('Geen admin user gevonden!');
            return;
        }

        // Check alleen of de basis tegels bestaan, niet alle tegels
        $basicTilesExist = StaffNote::where('user_id', $adminUser->id)
            ->where('title', 'Totaal Klanten')
            ->exists();
            
        if ($basicTilesExist) {
            // Voeg alleen nieuwe tegels toe die nog niet bestaan
            $newTiles = [
                'Totaal Afspraken Grafiek' => [
                    'title' => 'Totaal Afspraken Grafiek',
                    'content' => '<p>Bar chart van totaal aantal afspraken per maand.</p>',
                    'type' => 'mixed',
                    'tile_size' => 'medium',
                    'background_color' => '#fff7ed',
                    'text_color' => '#ea580c',
                    'priority' => 'medium',
                    'sort_order' => 7,
                    'visibility' => 'staff',
                    'is_pinned' => false,
                    'user_id' => $adminUser->id,
                ]
            ];
            
            foreach ($newTiles as $title => $tileData) {
                if (!StaffNote::where('title', $title)->where('user_id', $adminUser->id)->exists()) {
                    StaffNote::create($tileData);
                    $this->command->info("Nieuwe tegel '{$title}' toegevoegd");
                } else {
                    $this->command->info("Tegel '{$title}' bestaat al");
                }
            }
            return;
        }

        // Bestaande dashboard tegels data
        $tiles = [
            [
                'title' => 'Totaal Klanten',
                'content' => '<p>Live overzicht van het totaal aantal klanten in het systeem.</p>',
                'type' => 'note',
                'tile_size' => 'small',
                'background_color' => '#fef3e2',
                'text_color' => '#ea580c',
                'priority' => 'medium',
                'sort_order' => 1,
                'visibility' => 'staff',
                'is_pinned' => true,
            ],
            [
                'title' => 'Nieuwe Klanten Dit Jaar',
                'content' => '<p>Aantal nieuwe klanten die zich dit jaar hebben aangemeld.</p>',
                'type' => 'note',
                'tile_size' => 'small',
                'background_color' => '#e0f2fe',
                'text_color' => '#0284c7',
                'priority' => 'medium',
                'sort_order' => 2,
                'visibility' => 'staff',
                'is_pinned' => true,
            ],
            [
                'title' => 'Afspraken Huidige Maand',
                'content' => '<p>Overzicht van alle afspraken (bikefits + inspanningstesten) voor deze maand.</p>',
                'type' => 'note',
                'tile_size' => 'small',
                'background_color' => '#dcfce7',
                'text_color' => '#16a34a',
                'priority' => 'high',
                'sort_order' => 3,
                'visibility' => 'staff',
                'is_pinned' => true,
            ],
            [
                'title' => 'Afspraken Dit Jaar',
                'content' => '<p>Totaal overzicht van alle afspraken voor het huidige jaar.</p>',
                'type' => 'note',
                'tile_size' => 'small',
                'background_color' => '#ede9fe',
                'text_color' => '#6d28d9',
                'priority' => 'medium',
                'sort_order' => 4,
                'visibility' => 'staff',
                'is_pinned' => true,
            ],
            [
                'title' => 'Inspanningstesten Dit Jaar',
                'content' => '<p>Aantal uitgevoerde inspanningstesten in het huidige jaar.</p>',
                'type' => 'note',
                'tile_size' => 'small',
                'background_color' => '#fce7f3',
                'text_color' => '#be185d',
                'priority' => 'medium',
                'sort_order' => 5,
                'visibility' => 'staff',
                'is_pinned' => true,
            ],
            [
                'title' => 'Bikefits Dit Jaar',
                'content' => '<p>Aantal uitgevoerde bikefits in het huidige jaar.</p>',
                'type' => 'note',
                'tile_size' => 'small',
                'background_color' => '#e6fffa',
                'text_color' => '#0d9488',
                'priority' => 'medium',
                'sort_order' => 6,
                'visibility' => 'staff',
                'is_pinned' => true,
            ],
            [
                'title' => 'Totaal Afspraken Grafiek',
                'content' => '<p>Bar chart van totaal aantal afspraken per maand.</p>',
                'type' => 'mixed',
                'tile_size' => 'medium',
                'background_color' => '#fff7ed',
                'text_color' => '#ea580c',
                'priority' => 'medium',
                'sort_order' => 7,
                'visibility' => 'staff',
                'is_pinned' => false,
            ],
            [
                'title' => 'Statistieken Overzicht',
                'content' => '<p>Uitgebreide grafieken en statistieken voor bikefits en inspanningstesten per maand.</p>',
                'type' => 'mixed',
                'tile_size' => 'banner',
                'background_color' => '#f8fafc',
                'text_color' => '#1e293b',
                'priority' => 'low',
                'sort_order' => 8,
                'visibility' => 'staff',
                'is_pinned' => false,
            ],
            [
                'title' => 'Testzadel Dashboard',
                'content' => '<p>Overzicht en beheer van testzadel gerelateerde functionaliteiten.</p>',
                'type' => 'mixed',
                'tile_size' => 'medium',
                'background_color' => '#f1f5f9',
                'text_color' => '#334155',
                'priority' => 'low',
                'sort_order' => 9,
                'visibility' => 'staff',
                'is_pinned' => false,
            ]
        ];

        foreach ($tiles as $tileData) {
            // Check if tile already exists
            $existingTile = StaffNote::where('title', $tileData['title'])
                ->where('user_id', $adminUser->id)
                ->first();
                
            if (!$existingTile) {
                StaffNote::create(array_merge($tileData, [
                    'user_id' => $adminUser->id,
                ]));
                
                $this->command->info("Dashboard tegel '{$tileData['title']}' toegevoegd");
            } else {
                $this->command->info("Dashboard tegel '{$tileData['title']}' bestaat al");
            }
        }
    }
}