<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VeiligeMigratie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:veilig {--check : Alleen checken, niet uitvoeren} {--backup : Maak backup van database structuur}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Voer migrations veilig uit met checks en backup opties';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🛡️  VEILIGE MIGRATIE TOOL - Bonami Sportcoaching');
        $this->newLine();

        // Stap 1: Check database connectie
        if (!$this->checkDatabaseConnectie()) {
            return Command::FAILURE;
        }

        // Stap 2: Maak backup van structuur indien gevraagd
        if ($this->option('backup')) {
            $this->maakStructuurBackup();
        }

        // Stap 3: Check welke migrations pending zijn
        $pendingMigrations = $this->getPendingMigrations();
        
        if (empty($pendingMigrations)) {
            $this->info('✅ Geen nieuwe migrations om uit te voeren!');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  Pending migrations: " . count($pendingMigrations));
        foreach ($pendingMigrations as $migration) {
            $this->line("   • $migration");
        }
        $this->newLine();

        // Stap 4: Toon SQL zonder uit te voeren
        if ($this->option('check')) {
            $this->info('🔍 SQL Preview (--pretend mode):');
            $this->call('migrate', ['--pretend' => true]);
            $this->newLine();
            $this->info('💡 Verwijder --check flag om daadwerkelijk uit te voeren');
            return Command::SUCCESS;
        }

        // Stap 5: Vraag bevestiging
        if (!$this->confirm('Wil je deze migrations uitvoeren?', false)) {
            $this->info('❌ Migratie geannuleerd');
            return Command::SUCCESS;
        }

        // Stap 6: Voer migrations uit
        $this->info('🚀 Start migratie...');
        $this->newLine();

        try {
            $this->call('migrate', ['--force' => true]);
            $this->newLine();
            $this->info('✅ Migratie succesvol voltooid!');
            
            // Stap 7: Verificatie
            $this->verificeerMigratie();
            
        } catch (\Exception $e) {
            $this->error('❌ Fout tijdens migratie: ' . $e->getMessage());
            $this->warn('💡 Check de error en probeer opnieuw, of rollback met: php artisan migrate:rollback');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Check database connectie
     */
    private function checkDatabaseConnectie(): bool
    {
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $this->info("✅ Database connectie OK: $dbName");
            $this->newLine();
            return true;
        } catch (\Exception $e) {
            $this->error('❌ Geen database connectie: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Haal pending migrations op
     */
    private function getPendingMigrations(): array
    {
        $ran = DB::table('migrations')->pluck('migration')->toArray();
        $migrations = [];
        
        foreach (glob(database_path('migrations/*.php')) as $file) {
            $migration = str_replace('.php', '', basename($file));
            if (!in_array($migration, $ran)) {
                $migrations[] = $migration;
            }
        }
        
        sort($migrations);
        return $migrations;
    }

    /**
     * Maak backup van database structuur
     */
    private function maakStructuurBackup(): void
    {
        $this->info('💾 Maak backup van database structuur...');
        
        try {
            $tabellen = DB::select('SHOW TABLES');
            $backupData = [
                'datum' => now()->format('Y-m-d H:i:s'),
                'database' => DB::connection()->getDatabaseName(),
                'tabellen' => []
            ];
            
            foreach ($tabellen as $tabel) {
                $tabelNaam = array_values((array)$tabel)[0];
                $kolommen = DB::select("DESCRIBE `$tabelNaam`");
                $backupData['tabellen'][$tabelNaam] = $kolommen;
            }
            
            $backupPath = storage_path('app/backups/database-structure-' . now()->format('Y-m-d-His') . '.json');
            
            if (!is_dir(dirname($backupPath))) {
                mkdir(dirname($backupPath), 0755, true);
            }
            
            file_put_contents($backupPath, json_encode($backupData, JSON_PRETTY_PRINT));
            $this->info("✅ Backup opgeslagen: $backupPath");
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->warn('⚠️  Backup maken mislukt: ' . $e->getMessage());
            $this->newLine();
        }
    }

    /**
     * Verificeer migratie resultaat
     */
    private function verificeerMigratie(): void
    {
        $this->newLine();
        $this->info('🔍 Verificatie:');
        
        try {
            $tabellen = DB::select('SHOW TABLES');
            $this->info('   • Totaal tabellen: ' . count($tabellen));
            
            $migrations = DB::table('migrations')->count();
            $this->info('   • Uitgevoerde migrations: ' . $migrations);
            
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Verificatie gedeeltelijk mislukt');
        }
    }
}
