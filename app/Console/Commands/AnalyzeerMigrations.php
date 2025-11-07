<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AnalyzeerMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrations:analyseer {--export : Exporteer als SQL bestand}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Analyseer alle migrations en genereer deployment rapport';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Analyseer migrations...');
        $this->newLine();

        $migrationPath = database_path('migrations');
        $migrations = collect(File::files($migrationPath))
            ->map(fn($file) => $file->getFilename())
            ->sort()
            ->values();

        // Haal uitgevoerde migrations op
        $uitgevoerd = \DB::table('migrations')->pluck('migration')->toArray();
        
        $nieuweTabellen = [];
        $nieuweKolommen = [];
        $indexes = [];
        
        $this->info("📋 Totaal migrations: " . $migrations->count());
        $this->info("✅ Uitgevoerd: " . count($uitgevoerd));
        $this->newLine();

        // Analyseer elke migration
        foreach ($migrations as $migration) {
            $migrationName = str_replace('.php', '', $migration);
            $isUitgevoerd = in_array($migrationName, $uitgevoerd);
            
            $status = $isUitgevoerd ? '<fg=green>✓</>' : '<fg=yellow>○</>';
            $this->line("$status $migrationName");
            
            if (!$isUitgevoerd) {
                // Analyseer inhoud van nieuwe migration
                $this->analyseerMigrationBestand($migrationPath . '/' . $migration, $nieuweTabellen, $nieuweKolommen, $indexes);
            }
        }

        $this->newLine();
        $this->info('📊 SAMENVATTING NIEUWE MIGRATIONS:');
        $this->newLine();

        if (count($nieuweTabellen) > 0) {
            $this->warn('🆕 Nieuwe tabellen:');
            foreach ($nieuweTabellen as $tabel) {
                $this->line("   • $tabel");
            }
            $this->newLine();
        }

        if (count($nieuweKolommen) > 0) {
            $this->warn('➕ Nieuwe kolommen:');
            foreach ($nieuweKolommen as $info) {
                $this->line("   • {$info['tabel']}: {$info['kolommen']}");
            }
            $this->newLine();
        }

        if ($this->option('export')) {
            $this->exporteerAlsSQL($nieuweTabellen, $nieuweKolommen);
        }

        $this->info('💡 TIP: Run "php artisan migrate --pretend" om SQL te zien zonder uit te voeren');
        
        return Command::SUCCESS;
    }

    /**
     * Analyseer migration bestand inhoud
     */
    private function analyseerMigrationBestand($bestand, &$nieuweTabellen, &$nieuweKolommen, &$indexes)
    {
        $inhoud = File::get($bestand);
        
        // Detecteer Schema::create (nieuwe tabellen)
        if (preg_match_all("/Schema::create\('([^']+)'/", $inhoud, $matches)) {
            foreach ($matches[1] as $tabel) {
                $nieuweTabellen[] = $tabel;
            }
        }
        
        // Detecteer Schema::table (nieuwe kolommen)
        if (preg_match_all("/Schema::table\('([^']+)'/", $inhoud, $matches)) {
            foreach ($matches[1] as $tabel) {
                // Probeer kolommen te detecteren (basic parsing)
                $nieuweKolommen[] = [
                    'tabel' => $tabel,
                    'kolommen' => 'zie migration bestand voor details'
                ];
            }
        }
    }

    /**
     * Exporteer als SQL bestand
     */
    private function exporteerAlsSQL($tabellen, $kolommen)
    {
        $this->info('📝 Genereer SQL export...');
        
        $sql = "-- Bonami Sportcoaching Database Update\n";
        $sql .= "-- Gegenereerd op: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- BELANGRIJK: Maak eerst een backup!\n\n";
        $sql .= "-- Run migrations met: php artisan migrate --force\n\n";
        
        $exportPath = storage_path('app/migration-updates.sql');
        File::put($exportPath, $sql);
        
        $this->info("✅ SQL export opgeslagen: $exportPath");
    }
}
