<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailMigrationService;

class MigrateEmailSystemCommand extends Command
{
    /**
     * De naam en signature van het artisan command.
     *
     * @var string
     */
    protected $signature = 'email:migrate 
                            {--dry-run : Voer een droge run uit zonder wijzigingen}
                            {--templates : Migreer alleen email templates}
                            {--triggers : Migreer alleen email triggers}
                            {--test : Test het nieuwe email systeem}';

    /**
     * De beschrijving van het console command.
     *
     * @var string
     */
    protected $description = 'Migreer oude email systemen naar het nieuwe Email Admin systeem';

    /**
     * Voer het console command uit.
     */
    public function handle()
    {
        $this->info('🚀 Start Email Systeem Migratie voor Bonami Sportcoaching');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE - Geen wijzigingen worden doorgevoerd');
            $this->newLine();
        }

        $migrationService = app(EmailMigrationService::class);

        // Backup waarschuwing
        if (!$this->option('dry-run') && !$this->confirm('Heb je een backup gemaakt van de database en email templates?')) {
            $this->error('❌ Maak eerst een backup voordat je de migratie start!');
            return Command::FAILURE;
        }

        try {
            if ($this->option('templates') || !$this->hasSpecificOptions()) {
                $this->info('📧 Migreer Email Templates...');
                $this->migrateTemplates($migrationService);
            }

            if ($this->option('triggers') || !$this->hasSpecificOptions()) {
                $this->info('⚡ Migreer Email Triggers...');
                $this->migrateTriggers($migrationService);
            }

            if ($this->option('test') || !$this->hasSpecificOptions()) {
                $this->info('🧪 Test Nieuwe Email Systeem...');
                $this->testEmailSystem($migrationService);
            }

            if (!$this->hasSpecificOptions()) {
                $this->info('🎛️ Update Controllers...');
                $this->updateControllers($migrationService);
            }

            $this->newLine();
            $this->info('✅ Email systeem migratie succesvol voltooid!');
            $this->info('📝 Controleer de logs voor details: storage/logs/laravel.log');
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Migratie gefaald: ' . $e->getMessage());
            $this->error('🔧 Controleer de logs voor meer details.');
            
            return Command::FAILURE;
        }
    }

    /**
     * Migreer email templates
     */
    private function migrateTemplates(EmailMigrationService $service): void
    {
        if ($this->option('dry-run')) {
            $this->line('  [DRY RUN] Zou email templates migreren...');
            return;
        }

        $migrated = $service->migrateTemplates();
        
        if (!empty($migrated)) {
            $this->info('  ✅ Templates gemigreerd: ' . implode(', ', $migrated));
        } else {
            $this->warn('  ⚠️  Geen templates gemigreerd');
        }
    }

    /**
     * Migreer email triggers
     */
    private function migrateTriggers(EmailMigrationService $service): void
    {
        if ($this->option('dry-run')) {
            $this->line('  [DRY RUN] Zou email triggers migreren...');
            return;
        }

        $migrated = $service->migrateTriggers();
        
        if (!empty($migrated)) {
            $this->info('  ✅ Triggers gemigreerd: ' . implode(', ', $migrated));
        } else {
            $this->warn('  ⚠️  Geen triggers gemigreerd');
        }
    }

    /**
     * Test het nieuwe email systeem
     */
    private function testEmailSystem(EmailMigrationService $service): void
    {
        $results = $service->testNewEmailSystem();
        
        foreach ($results as $test => $result) {
            if ($result === 'OK') {
                $this->info("  ✅ {$test}: {$result}");
            } else {
                $this->error("  ❌ {$test}: {$result}");
            }
        }
    }

    /**
     * Update controllers (informatief)
     */
    private function updateControllers(EmailMigrationService $service): void
    {
        if ($this->option('dry-run')) {
            $this->line('  [DRY RUN] Zou controllers updaten...');
            return;
        }

        $replaced = $service->replaceControllerEmailCalls();
        
        $this->warn('  ⚠️  Controllers vereisen handmatige update:');
        foreach ($replaced as $controller) {
            $this->line("    - {$controller}");
        }
        
        $this->newLine();
        $this->info('💡 Tip: Gebruik de nieuwe EmailAdmin service in plaats van directe Mail:: calls');
    }

    /**
     * Check of er specifieke opties zijn gegeven
     */
    private function hasSpecificOptions(): bool
    {
        return $this->option('templates') || 
               $this->option('triggers') || 
               $this->option('test');
    }
}