<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Testzadel;
use Illuminate\Support\Facades\DB;

class FixEmptyOnderdeelType extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-empty-onderdeel-type';

    /**
     * The console command description.
     */
    protected $description = 'Fix testzadels met lege string onderdeel_type';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing testzadels met lege string onderdeel_type...');

        try {
            // Update alle lege strings naar correcte waarden met directe DB query
            $this->info('🔍 Zoeken naar lege string onderdeel_type records...');
            
            // Update lege strings direct in database
            $updatedCount = DB::table('testzadels')
                             ->where('onderdeel_type', '')
                             ->orWhereNull('onderdeel_type')
                             ->update(['onderdeel_type' => 'testzadel']);
            
            $this->info("✅ {$updatedCount} records geüpdatet van lege string naar 'testzadel'");
            
            // Verificatie
            $this->info('🔍 Verificatie na directe database update:');
            
            $nullCount = DB::table('testzadels')->whereNull('onderdeel_type')->count();
            $emptyCount = DB::table('testzadels')->where('onderdeel_type', '')->count();
            
            $this->line("NULL onderdeel_type: {$nullCount}");
            $this->line("Empty string onderdeel_type: {$emptyCount}");
            
            if ($nullCount === 0 && $emptyCount === 0) {
                $this->info('✅ Alle problematische onderdeel_type records zijn gerepareerd!');
            } else {
                $this->warn('⚠️ Er zijn nog steeds problematische records');
            }
            
            // Toon huidige distributie
            $this->info('📊 Huidige onderdeel_type distributie na fix:');
            
            $distribution = DB::table('testzadels')
                             ->select('onderdeel_type', DB::raw('COUNT(*) as count'))
                             ->groupBy('onderdeel_type')
                             ->get();
                             
            foreach ($distribution as $item) {
                $type = $item->onderdeel_type ?: 'NULL/EMPTY';
                $this->line("- {$type}: {$item->count} testzadels");
            }
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing empty onderdeel_type: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}