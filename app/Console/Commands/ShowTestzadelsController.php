<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ShowTestzadelsController extends Command
{
    protected $signature = 'bonami:show-testzadels-controller';
    protected $description = 'Toon TestzadelsController update method';

    public function handle(): int
    {
        $controllerPath = app_path('Http/Controllers/TestzadelsController.php');
        
        if (!File::exists($controllerPath)) {
            $this->error('TestzadelsController niet gevonden');
            return Command::FAILURE;
        }
        
        $content = File::get($controllerPath);
        
        // Zoek naar update method
        if (preg_match('/public function update\(.*?\)\s*\{(.*?)(?=public function|\}$)/s', $content, $matches)) {
            $this->info('📝 Update method gevonden:');
            $this->line($matches[0]);
        } else {
            $this->warn('Update method niet gevonden');
        }
        
        return Command::SUCCESS;
    }
}