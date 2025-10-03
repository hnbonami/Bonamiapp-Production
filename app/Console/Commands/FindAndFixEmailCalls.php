<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindAndFixEmailCalls extends Command
{
    protected $signature = 'email:find-and-fix';
    protected $description = 'Find and automatically fix Mail::send calls';

    public function handle()
    {
        $this->info('🔍 Zoeken naar Mail::send calls in je code...');
        
        $files = $this->findPhpFiles();
        $foundCalls = [];
        
        foreach ($files as $file) {
            $content = File::get($file);
            
            // Check for Mail::send calls
            if (preg_match_all('/Mail::send\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[1] as $index => $match) {
                    $viewName = $match[0];
                    $foundCalls[] = [
                        'file' => $file,
                        'view' => $viewName,
                        'line' => substr_count(substr($content, 0, $match[1]), "\n") + 1
                    ];
                }
            }
        }
        
        if (empty($foundCalls)) {
            $this->info('✅ Geen Mail::send calls gevonden in je code.');
            return;
        }
        
        $this->info('📧 GEVONDEN EMAIL CALLS:');
        $this->info('========================');
        
        foreach ($foundCalls as $call) {
            $this->line("📁 {$call['file']}:{$call['line']}");
            $this->line("   📧 View: {$call['view']}");
            $this->line('');
        }
        
        if ($this->confirm('Wil je deze automatisch laten vervangen door MailHelper::smartSend?')) {
            $this->fixEmailCalls($foundCalls);
        }
        
        return Command::SUCCESS;
    }
    
    private function findPhpFiles()
    {
        $files = [];
        
        // Controllers
        $controllerPath = app_path('Http/Controllers');
        if (is_dir($controllerPath)) {
            $files = array_merge($files, File::allFiles($controllerPath));
        }
        
        // Models
        $modelPath = app_path('Models');
        if (is_dir($modelPath)) {
            $files = array_merge($files, File::allFiles($modelPath));
        }
        
        // Services
        $servicePath = app_path('Services');
        if (is_dir($servicePath)) {
            $files = array_merge($files, File::allFiles($servicePath));
        }
        
        // Commands
        $commandPath = app_path('Console/Commands');
        if (is_dir($commandPath)) {
            $files = array_merge($files, File::allFiles($commandPath));
        }
        
        // Filter to only .php files
        return array_filter($files, function($file) {
            return pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php';
        });
    }
    
    private function fixEmailCalls($calls)
    {
        $this->info('🔧 Vervangen van Mail::send calls...');
        
        foreach ($calls as $call) {
            $filePath = $call['file'];
            $content = File::get($filePath);
            
            // Add use statement if not present
            if (!str_contains($content, 'use App\\Helpers\\MailHelper;')) {
                // Find last use statement or class declaration
                if (preg_match('/^use [^;]+;$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $lastUse = end($matches);
                    $insertPos = $lastUse[1] + strlen($lastUse[0]);
                    $content = substr_replace($content, "\nuse App\\Helpers\\MailHelper;", $insertPos, 0);
                } elseif (preg_match('/^namespace [^;]+;$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $namespace = $matches[0];
                    $insertPos = $namespace[1] + strlen($namespace[0]);
                    $content = substr_replace($content, "\n\nuse App\\Helpers\\MailHelper;", $insertPos, 0);
                }
            }
            
            // Replace Mail::send with MailHelper::smartSend
            $content = preg_replace('/Mail::send\s*\(/', 'MailHelper::smartSend(', $content);
            
            File::put($filePath, $content);
            $this->info("✅ Updated: " . basename($filePath));
        }
        
        $this->info('');
        $this->info('🎉 ALLE EMAIL CALLS ZIJN GEÜPGRADED!');
        $this->info('Nu worden automatisch de nieuwe templates gebruikt als ze bestaan.');
        $this->info('Ga naar /admin/email/logs om te zien welke emails worden verstuurd.');
    }
}