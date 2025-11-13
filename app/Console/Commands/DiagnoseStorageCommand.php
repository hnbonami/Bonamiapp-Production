<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DiagnoseStorageCommand extends Command
{
    protected $signature = 'storage:diagnose';
    protected $description = 'Diagnosticeer storage configuratie en permissies';

    public function handle()
    {
        $this->info('=== Storage Diagnostics ===');
        $this->newLine();

        // Check storage path
        $storagePath = storage_path('app/public');
        $this->info("Storage path: {$storagePath}");
        $this->info("Storage path bestaat: " . (File::exists($storagePath) ? 'JA' : 'NEE'));
        
        if (File::exists($storagePath)) {
            $this->info("Storage path is writable: " . (File::isWritable($storagePath) ? 'JA' : 'NEE'));
            $this->info("Permissies: " . substr(sprintf('%o', fileperms($storagePath)), -4));
        }
        
        $this->newLine();
        
        // Check public symlink
        $publicLink = public_path('storage');
        $this->info("Public storage link: {$publicLink}");
        $this->info("Symlink bestaat: " . (File::exists($publicLink) ? 'JA' : 'NEE'));
        
        if (File::exists($publicLink)) {
            $this->info("Is symlink: " . (is_link($publicLink) ? 'JA' : 'NEE'));
            if (is_link($publicLink)) {
                $this->info("Link target: " . readlink($publicLink));
            }
        }
        
        $this->newLine();
        
        // Check avatars directory
        $avatarsPath = storage_path('app/public/avatars');
        $this->info("Avatars path: {$avatarsPath}");
        $this->info("Avatars directory bestaat: " . (File::exists($avatarsPath) ? 'JA' : 'NEE'));
        
        if (File::exists($avatarsPath)) {
            $this->info("Avatars is writable: " . (File::isWritable($avatarsPath) ? 'JA' : 'NEE'));
            $this->info("Aantal bestanden: " . count(File::files($avatarsPath)));
            $this->info("Permissies: " . substr(sprintf('%o', fileperms($avatarsPath)), -4));
        }
        
        $this->newLine();
        
        // Check disk configuration
        $this->info("Default disk: " . config('filesystems.default'));
        $this->info("Public disk root: " . config('filesystems.disks.public.root'));
        $this->info("Public disk url: " . config('filesystems.disks.public.url'));
        
        $this->newLine();
        
        // Test write
        $this->info("Test write naar storage...");
        try {
            Storage::disk('public')->put('test.txt', 'test');
            $this->info("✓ Write test geslaagd");
            Storage::disk('public')->delete('test.txt');
        } catch (\Exception $e) {
            $this->error("✗ Write test gefaald: " . $e->getMessage());
        }
        
        $this->newLine();
        $this->info('=== Diagnostics compleet ===');
        
        return 0;
    }
}
