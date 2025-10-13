<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckTestzadelsSchema extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:check-testzadels-schema';

    /**
     * The console command description.
     */
    protected $description = 'Check testzadels tabel schema en kolommen';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Controleren testzadels tabel schema...');

        try {
            // Check if table exists
            if (!Schema::hasTable('testzadels')) {
                $this->error('❌ testzadels tabel bestaat niet!');
                return Command::FAILURE;
            }

            $this->info('✅ testzadels tabel bestaat');

            // Get all columns
            $columns = DB::select("SHOW COLUMNS FROM testzadels");
            
            $this->info('📋 Alle kolommen in testzadels tabel:');
            
            $tableData = [];
            foreach ($columns as $column) {
                $tableData[] = [
                    'Field' => $column->Field,
                    'Type' => $column->Type,
                    'Null' => $column->Null,
                    'Key' => $column->Key ?: '',
                    'Default' => $column->Default ?: '',
                    'Extra' => $column->Extra ?: ''
                ];
            }
            
            $this->table(['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'], $tableData);

            // Check specific columns we expect
            $expectedColumns = [
                'id', 'klant_id', 'bikefit_id', 'onderdeel_type', 'status',
                'zadel_merk', 'zadel_model', 'zadel_type', 'zadel_breedte',
                'uitleen_datum', 'verwachte_retour_datum', 'werkelijke_retour_datum',
                'automatisch_mailtje', 'opmerkingen', 'created_at', 'updated_at'
            ];

            $this->info('');
            $this->info('🔍 Verwachte kolommen check:');
            
            $existingColumns = collect($columns)->pluck('Field')->toArray();
            
            foreach ($expectedColumns as $expectedCol) {
                if (in_array($expectedCol, $existingColumns)) {
                    $this->line("✅ {$expectedCol} - EXISTS");
                } else {
                    $this->line("❌ {$expectedCol} - MISSING");
                }
            }

            // Check for old columns that might cause issues
            $oldColumns = ['naam', 'merk', 'model', 'type', 'beschrijving', 'prijs', 'afbeelding', 'beschikbaar', 'breedte_mm'];
            
            $this->info('');
            $this->info('⚠️ Oude kolommen check (kunnen problemen veroorzaken):');
            
            foreach ($oldColumns as $oldCol) {
                if (in_array($oldCol, $existingColumns)) {
                    $this->line("⚠️ {$oldCol} - EXISTS (oude kolom)");
                } else {
                    $this->line("✅ {$oldCol} - NOT EXISTS (goed)");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error checking schema: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}