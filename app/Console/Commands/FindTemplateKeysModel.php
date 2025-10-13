<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FindTemplateKeysModel extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:find-template-keys-model';

    /**
     * The console command description.
     */
    protected $description = 'Zoek naar het juiste model/tabel voor template keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Zoeken naar template keys model/tabel...');

        try {
            // Check verschillende mogelijke tabel namen
            $possibleTables = [
                'template_keys',
                'templatekeys', 
                'sjabloon_keys',
                'sjabloonkeys',
                'bikefit_template_keys',
                'bikefit_keys',
                'keys'
            ];

            $this->info('📋 Beschikbare tabellen:');
            $tables = DB::select('SHOW TABLES');
            $tableNames = array_map(function($table) {
                return array_values((array)$table)[0];
            }, $tables);
            
            foreach ($tableNames as $table) {
                $this->line("- {$table}");
            }

            $this->info('');
            $this->info('🔍 Zoeken naar template keys gerelateerde tabellen...');
            
            foreach ($possibleTables as $table) {
                if (Schema::hasTable($table)) {
                    $this->info("✅ Gevonden: {$table}");
                    
                    // Toon kolommen
                    $columns = DB::select("SHOW COLUMNS FROM {$table}");
                    $this->line("   Kolommen:");
                    foreach ($columns as $column) {
                        $this->line("   - {$column->Field} ({$column->Type})");
                    }
                    
                    // Toon aantal records
                    $count = DB::table($table)->count();
                    $this->line("   Records: {$count}");
                    
                    if ($count > 0) {
                        // Toon eerste paar records
                        $this->line("   Eerste 3 records:");
                        $records = DB::table($table)->limit(3)->get();
                        foreach ($records as $record) {
                            $recordArray = (array)$record;
                            $this->line("   " . json_encode($recordArray, JSON_UNESCAPED_UNICODE));
                        }
                    }
                    
                    $this->line('');
                } else {
                    $this->line("❌ Niet gevonden: {$table}");
                }
            }

            // Zoek ook naar tabellen die 'key' bevatten
            $this->info('🔍 Tabellen die "key" bevatten:');
            foreach ($tableNames as $table) {
                if (stripos($table, 'key') !== false) {
                    $this->info("🔑 Gevonden: {$table}");
                }
            }

            // Zoek naar tabellen die 'template' bevatten
            $this->info('');
            $this->info('📄 Tabellen die "template" of "sjabloon" bevatten:');
            foreach ($tableNames as $table) {
                if (stripos($table, 'template') !== false || stripos($table, 'sjabloon') !== false) {
                    $this->info("📄 Gevonden: {$table}");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error finding template keys model: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}