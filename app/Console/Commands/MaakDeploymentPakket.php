<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class MaakDeploymentPakket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deployment:pakket {--migrations-only : Alleen migrations exporteren}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Maak een deployment pakket voor upload naar One.com';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📦 DEPLOYMENT PAKKET GENERATOR - Bonami Sportcoaching');
        $this->newLine();

        $exportPath = storage_path('app/deployment');
        
        // Maak export directory
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }

        // Stap 1: Analyseer migrations
        $this->info('🔍 Analyseer migrations...');
        $migrations = $this->getActieveMigrations();
        
        $this->info("   ✅ Actieve migrations: " . count($migrations['actief']));
        $this->warn("   ⚠️  Backup/disabled bestanden: " . count($migrations['disabled']));
        $this->newLine();

        // Stap 2: Genereer SQL voor online database
        $this->info('📝 Genereer SQL deployment script...');
        $sqlScript = $this->genereerDeploymentSQL($migrations['actief']);
        
        $sqlPath = $exportPath . '/deploy-database.sql';
        File::put($sqlPath, $sqlScript);
        $this->info("   ✅ SQL script: $sqlPath");
        $this->newLine();

        // Stap 3: Maak migrations overzicht
        $this->info('📋 Genereer migrations overzicht...');
        $overzicht = $this->maakMigrationsOverzicht($migrations);
        
        $overzichtPath = $exportPath . '/migrations-overzicht.txt';
        File::put($overzichtPath, $overzicht);
        $this->info("   ✅ Overzicht: $overzichtPath");
        $this->newLine();

        // Stap 4: Kopieer alleen actieve migrations
        $this->info('📁 Kopieer actieve migrations...');
        $migrationExportPath = $exportPath . '/migrations';
        
        if (is_dir($migrationExportPath)) {
            File::deleteDirectory($migrationExportPath);
        }
        mkdir($migrationExportPath, 0755, true);
        
        $gekopieerd = 0;
        foreach ($migrations['actief'] as $migration) {
            $source = database_path('migrations/' . $migration);
            $dest = $migrationExportPath . '/' . $migration;
            
            if (File::exists($source)) {
                File::copy($source, $dest);
                $gekopieerd++;
            }
        }
        
        $this->info("   ✅ Gekopieerd: $gekopieerd migrations");
        $this->newLine();

        // Stap 5: Genereer deployment instructies
        $this->info('📖 Genereer deployment instructies...');
        $instructies = $this->genereerDeploymentInstructies($migrations);
        
        $instructiesPath = $exportPath . '/DEPLOYMENT-INSTRUCTIES.md';
        File::put($instructiesPath, $instructies);
        $this->info("   ✅ Instructies: $instructiesPath");
        $this->newLine();

        // Stap 6: Maak ZIP pakket (optioneel)
        if (class_exists('ZipArchive')) {
            $this->info('🗜️  Maak ZIP pakket...');
            $zipPath = storage_path('app/bonami-deployment-' . now()->format('Y-m-d-His') . '.zip');
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                // Voeg alle deployment bestanden toe
                $files = File::allFiles($exportPath);
                foreach ($files as $file) {
                    $relativePath = str_replace($exportPath . '/', '', $file->getPathname());
                    $zip->addFile($file->getPathname(), 'deployment/' . $relativePath);
                }
                
                $zip->close();
                $this->info("   ✅ ZIP pakket: $zipPath");
            }
        }

        $this->newLine();
        $this->info('✅ DEPLOYMENT PAKKET KLAAR!');
        $this->newLine();
        $this->info("📂 Locatie: $exportPath");
        $this->newLine();
        
        $this->warn('⚠️  BELANGRIJK:');
        $this->line('1. Maak EERST een backup van je online database via TablePlus');
        $this->line('2. Upload de /migrations map via FileZilla');
        $this->line('3. Run op online server: php artisan migrate --pretend (check eerst!)');
        $this->line('4. Run op online server: php artisan migrate --force');
        $this->newLine();
        
        return Command::SUCCESS;
    }

    /**
     * Haal actieve en disabled migrations op
     */
    private function getActieveMigrations(): array
    {
        $migrationPath = database_path('migrations');
        $alleFiles = collect(File::files($migrationPath))
            ->map(fn($file) => $file->getFilename())
            ->sort()
            ->values();

        $actief = [];
        $disabled = [];

        foreach ($alleFiles as $file) {
            // Skip backup, temp en disabled bestanden
            if (
                str_contains($file, '.tmp') ||
                str_contains($file, '.bak') ||
                str_contains($file, '.disabled') ||
                str_contains($file, '.backup') ||
                str_contains($file, '.sql') ||
                str_contains($file, ' 2.') // Dubbele bestanden
            ) {
                $disabled[] = $file;
            } else {
                $actief[] = $file;
            }
        }

        return [
            'actief' => $actief,
            'disabled' => $disabled
        ];
    }

    /**
     * Genereer SQL deployment script
     */
    private function genereerDeploymentSQL($migrations): string
    {
        $sql = "-- ============================================\n";
        $sql .= "-- Bonami Sportcoaching - Database Deployment\n";
        $sql .= "-- Gegenereerd: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- ============================================\n\n";
        
        $sql .= "-- ⚠️  BELANGRIJK: MAAK EERST EEN BACKUP!\n";
        $sql .= "-- Dit script is een referentie.\n";
        $sql .= "-- Gebruik: php artisan migrate --force op de server\n\n";
        
        $sql .= "-- ============================================\n";
        $sql .= "-- VERIFICATIE QUERIES\n";
        $sql .= "-- ============================================\n\n";
        
        $sql .= "-- Check huidige migrations\n";
        $sql .= "SELECT COUNT(*) as uitgevoerde_migrations FROM migrations;\n\n";
        
        $sql .= "-- Check alle tabellen\n";
        $sql .= "SHOW TABLES;\n\n";
        
        $sql .= "-- ============================================\n";
        $sql .= "-- MIGRATION OVERZICHT\n";
        $sql .= "-- ============================================\n\n";
        
        $sql .= "-- Totaal actieve migrations: " . count($migrations) . "\n";
        $sql .= "-- Deze migrations moeten uitgevoerd worden via:\n";
        $sql .= "-- php artisan migrate --force\n\n";
        
        foreach ($migrations as $index => $migration) {
            $sql .= "-- " . ($index + 1) . ". " . str_replace('.php', '', $migration) . "\n";
        }
        
        return $sql;
    }

    /**
     * Maak migrations overzicht
     */
    private function maakMigrationsOverzicht($migrations): string
    {
        $txt = "BONAMI SPORTCOACHING - MIGRATIONS OVERZICHT\n";
        $txt .= "==========================================\n";
        $txt .= "Gegenereerd: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        $txt .= "ACTIEVE MIGRATIONS (" . count($migrations['actief']) . ")\n";
        $txt .= "-------------------------------------------\n";
        foreach ($migrations['actief'] as $migration) {
            $txt .= "✓ " . str_replace('.php', '', $migration) . "\n";
        }
        
        $txt .= "\n\nBACKUP/DISABLED BESTANDEN (" . count($migrations['disabled']) . ")\n";
        $txt .= "-------------------------------------------\n";
        $txt .= "(Deze worden NIET geüpload)\n\n";
        foreach ($migrations['disabled'] as $migration) {
            $txt .= "✗ $migration\n";
        }
        
        $txt .= "\n\nRECOMMENDATIE\n";
        $txt .= "-------------------------------------------\n";
        $txt .= "Upload ALLEEN de actieve migrations naar online.\n";
        $txt .= "Verwijder lokaal de backup/disabled bestanden als ze niet meer nodig zijn.\n";
        
        return $txt;
    }

    /**
     * Genereer deployment instructies
     */
    private function genereerDeploymentInstructies($migrations): string
    {
        $md = "# 🚀 Deployment Instructies - Bonami Sportcoaching\n\n";
        $md .= "**Gegenereerd:** " . now()->format('Y-m-d H:i:s') . "\n";
        $md .= "**Actieve migrations:** " . count($migrations['actief']) . "\n\n";
        
        $md .= "## ⚠️ VOOR JE BEGINT\n\n";
        $md .= "### 1. Maak Online Backup\n";
        $md .= "**Via TablePlus:**\n";
        $md .= "1. Verbind met online database\n";
        $md .= "2. Rechtermuisknop → Export → Structure + Data\n";
        $md .= "3. Sla op als: `bonami_backup_" . now()->format('Y-m-d') . ".sql`\n\n";
        
        $md .= "**Via One.com Control Panel:**\n";
        $md .= "1. Login op One.com\n";
        $md .= "2. Ga naar MySQL Databases\n";
        $md .= "3. Klik op je database → Export\n";
        $md .= "4. Download backup bestand\n\n";
        
        $md .= "## 📤 UPLOAD BESTANDEN\n\n";
        $md .= "### 2. Upload Migrations via FileZilla\n\n";
        $md .= "**Locatie op je Mac:**\n";
        $md .= "```\n";
        $md .= storage_path('app/deployment/migrations') . "\n";
        $md .= "```\n\n";
        
        $md .= "**Upload naar server:**\n";
        $md .= "```\n";
        $md .= "/domains/jouwdomain.nl/database/migrations/\n";
        $md .= "```\n\n";
        
        $md .= "### 3. Upload Applicatie Bestanden\n\n";
        $md .= "**Upload deze mappen (overschrijf):**\n";
        $md .= "- ✅ `/app/Console/Commands` (nieuwe commands)\n";
        $md .= "- ✅ `/app/Http/Controllers`\n";
        $md .= "- ✅ `/app/Models`\n";
        $md .= "- ✅ `/app/Services`\n";
        $md .= "- ✅ `/config`\n";
        $md .= "- ✅ `/resources/views`\n";
        $md .= "- ✅ `/routes`\n";
        $md .= "- ✅ `composer.json` en `composer.lock`\n\n";
        
        $md .= "**NIET uploaden:**\n";
        $md .= "- ❌ `.env` (handmatig vergelijken)\n";
        $md .= "- ❌ `/storage` (bevat cache/logs)\n";
        $md .= "- ❌ `/public/uploads` (klantdata)\n";
        $md .= "- ❌ `/vendor` (regenereren)\n\n";
        
        $md .= "## 🌐 ONLINE SERVER ACTIES\n\n";
        $md .= "### 4. Via One.com Web Terminal\n\n";
        $md .= "**Login op One.com:**\n";
        $md .= "1. Ga naar 'Advanced' → 'SSH Access'\n";
        $md .= "2. Klik 'Open Web Terminal'\n\n";
        
        $md .= "**Navigeer naar je site:**\n";
        $md .= "```bash\n";
        $md .= "cd domains/jouwdomain.nl\n";
        $md .= "```\n\n";
        
        $md .= "### 5. Update Dependencies\n";
        $md .= "```bash\n";
        $md .= "composer install --no-dev --optimize-autoloader\n";
        $md .= "```\n\n";
        
        $md .= "### 6. Check Migrations (PREVIEW)\n";
        $md .= "```bash\n";
        $md .= "# Zie SQL zonder uit te voeren\n";
        $md .= "php artisan migrate --pretend\n\n";
        
        $md .= "# Of gebruik de veilige tool\n";
        $md .= "php artisan migrate:veilig --check --backup\n";
        $md .= "```\n\n";
        
        $md .= "### 7. Voer Migrations Uit\n";
        $md .= "```bash\n";
        $md .= "# Met veiligheidscheck\n";
        $md .= "php artisan migrate:veilig --backup\n\n";
        
        $md .= "# Of standaard (als je zeker bent)\n";
        $md .= "php artisan migrate --force\n";
        $md .= "```\n\n";
        
        $md .= "### 8. Clear en Rebuild Caches\n";
        $md .= "```bash\n";
        $md .= "# Clear alles\n";
        $md .= "php artisan config:clear\n";
        $md .= "php artisan cache:clear\n";
        $md .= "php artisan view:clear\n";
        $md .= "php artisan route:clear\n\n";
        
        $md .= "# Rebuild voor productie\n";
        $md .= "php artisan config:cache\n";
        $md .= "php artisan route:cache\n";
        $md .= "php artisan view:cache\n";
        $md .= "```\n\n";
        
        $md .= "## ✅ VERIFICATIE\n\n";
        $md .= "### 9. Test Online Applicatie\n";
        $md .= "- [ ] Login werkt\n";
        $md .= "- [ ] Klanten overzicht\n";
        $md .= "- [ ] Bikefit aanmaken\n";
        $md .= "- [ ] PDF generatie\n";
        $md .= "- [ ] Testzadel systeem\n";
        $md .= "- [ ] Upload functionaliteit\n\n";
        
        $md .= "### 10. Check Database\n";
        $md .= "**Via TablePlus:**\n";
        $md .= "1. Verifieer nieuwe tabellen aanwezig\n";
        $md .= "2. Check migrations tabel (zou " . count($migrations['actief']) . " entries moeten hebben)\n";
        $md .= "3. Controleer of bestaande data intact is\n\n";
        
        $md .= "## 🆘 ROLLBACK (indien nodig)\n\n";
        $md .= "**Als iets misgaat:**\n\n";
        $md .= "```bash\n";
        $md .= "# Laatste migration terugdraaien\n";
        $md .= "php artisan migrate:rollback --step=1\n\n";
        
        $md .= "# Of hele batch\n";
        $md .= "php artisan migrate:rollback\n";
        $md .= "```\n\n";
        
        $md .= "**Database volledig herstellen:**\n";
        $md .= "1. Importeer backup SQL via TablePlus\n";
        $md .= "2. ALLEEN als laatste redmiddel!\n\n";
        
        $md .= "## 📞 HULP NODIG?\n\n";
        $md .= "- One.com Support: support@one.com\n";
        $md .= "- Check logs: `storage/logs/laravel.log`\n";
        $md .= "- Migration status: `php artisan migrate:status`\n";
        
        return $md;
    }
}
