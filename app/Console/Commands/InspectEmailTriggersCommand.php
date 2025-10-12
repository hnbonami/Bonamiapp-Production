<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InspectEmailTriggersCommand extends Command
{
    /**
     * De naam en signature van het artisan command.
     *
     * @var string
     */
    protected $signature = 'email:inspect-triggers';

    /**
     * De beschrijving van het console command.
     *
     * @var string
     */
    protected $description = 'Inspecteer de email_triggers tabel structuur';

    /**
     * Voer het console command uit.
     */
    public function handle()
    {
        $this->info('🔍 Inspecteer Email Triggers Tabel voor Bonami Sportcoaching');
        $this->newLine();

        if (!Schema::hasTable('email_triggers')) {
            $this->error('❌ email_triggers tabel bestaat niet!');
            return Command::FAILURE;
        }

        $this->info('✅ email_triggers tabel bestaat');
        $this->newLine();

        // Toon alle kolommen
        $columns = Schema::getColumnListing('email_triggers');
        $this->info('📋 Bestaande kolommen:');
        foreach ($columns as $column) {
            $this->line("  - {$column}");
        }

        $this->newLine();

        // Toon aantal records
        $count = DB::table('email_triggers')->count();
        $this->info("📊 Aantal triggers: {$count}");

        if ($count > 0) {
            $this->newLine();
            $this->info('🔍 Eerste 3 triggers:');
            
            $triggers = DB::table('email_triggers')->limit(3)->get();
            foreach ($triggers as $trigger) {
                $this->line("  ID: {$trigger->id}");
                $this->line("  Name: " . ($trigger->name ?? 'NULL'));
                
                // Check specifieke kolommen
                $checks = [
                    'description',
                    'trigger_type', 
                    'trigger_key',
                    'trigger_data',
                    'is_active',
                    'email_template_id'
                ];
                
                foreach ($checks as $check) {
                    if (property_exists($trigger, $check)) {
                        $value = $trigger->$check ?? 'NULL';
                        $this->line("  {$check}: {$value}");
                    } else {
                        $this->line("  {$check}: KOLOM BESTAAT NIET");
                    }
                }
                $this->line("  ---");
            }
        }

        $this->newLine();
        
        // Check welke kolommen ontbreken
        $requiredColumns = [
            'trigger_key',
            'trigger_type',
            'trigger_data',
            'description',
            'conditions',
            'settings',
            'emails_sent',
            'last_run_at',
            'created_by'
        ];
        
        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $columns)) {
                $missingColumns[] = $column;
            }
        }
        
        if (!empty($missingColumns)) {
            $this->warn('⚠️  Ontbrekende kolommen:');
            foreach ($missingColumns as $column) {
                $this->line("  - {$column}");
            }
        } else {
            $this->info('✅ Alle vereiste kolommen aanwezig');
        }

        return Command::SUCCESS;
    }
}