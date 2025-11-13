<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixStoragePermissionsCommand extends Command
{
    protected $signature = 'storage:fix-permissions';
    protected $description = 'Fix storage permissies en creëer ontbrekende directories';

    public function handle()
    {
        $this->info('=== Storage Permissies Herstellen ===');
        $this->newLine();

        // Directories die moeten bestaan
        $directories = [
            storage_path('app/public'),
            storage_path('app/public/avatars'),
            storage_path('app/public/documents'),
            storage_path('app/public/bikefits'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                $this->info("Creëer directory: {$directory}");
                File::makeDirectory($directory, 0755, true);
            } else {
                $this->info("Directory bestaat al: {$directory}");
            }
            
            // Set permissies
            chmod($directory, 0755);
            $this->info("Permissies gezet naar 0755");
        }

        $this->newLine();

        // Forceer symlink recreatie
        $publicLink = public_path('storage');
        
        if (File::exists($publicLink)) {
            $this->info("Verwijder bestaande storage symlink...");
            if (is_link($publicLink)) {
                unlink($publicLink);
            } else {
                File::deleteDirectory($publicLink);
            }
        }

        $this->info("Creëer nieuwe storage symlink...");
        $target = storage_path('app/public');
        
        if (windows_os()) {
            $this->call('storage:link');
        } else {
            symlink($target, $publicLink);
        }
        
        $this->info("✓ Symlink gecreëerd: {$publicLink} -> {$target}");

        $this->newLine();
        $this->info('=== Klaar! ===');
        $this->newLine();
        $this->info('Run nu: php artisan storage:diagnose');
        
        return 0;
    }
}
