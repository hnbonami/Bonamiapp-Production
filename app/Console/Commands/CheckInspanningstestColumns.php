<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckInspanningstestColumns extends Command
{
    protected $signature = 'db:check-inspanningstest';
    protected $description = 'Controleer welke kolommen er in de inspanningstests tabel zitten';

    public function handle()
    {
        $this->info('🔍 Controleren inspanningstests tabel structuur...');
        
        if (!Schema::hasTable('inspanningstests')) {
            $this->error('❌ Tabel "inspanningstests" bestaat niet!');
            return 1;
        }
        
        // Haal alle kolommen op
        $columns = Schema::getColumnListing('inspanningstests');
        
        $this->info("\n📊 Gevonden kolommen (" . count($columns) . "):");
        $this->table(['Kolom Naam'], array_map(fn($col) => [$col], $columns));
        
        // Check specifieke nieuwe velden
        $nieuweVelden = [
            'vetpercentage',
            'complete_ai_analyse',
            'trainingszones_data',
            'zones_methode',
            'zones_aantal',
            'zones_eenheid'
        ];
        
        $this->info("\n🔎 Status nieuwe velden:");
        foreach ($nieuweVelden as $veld) {
            $exists = Schema::hasColumn('inspanningstests', $veld);
            $status = $exists ? '✅ Bestaat al' : '❌ Ontbreekt';
            $this->line("  {$veld}: {$status}");
        }
        
        return 0;
    }
}
