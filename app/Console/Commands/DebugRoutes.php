<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class DebugRoutes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:debug-routes';

    /**
     * The console command description.
     */
    protected $description = 'Debug alle geregistreerde routes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Debugging alle geregistreerde routes...');

        try {
            $routes = Route::getRoutes();
            
            $this->info('📋 Alle routes:');
            foreach ($routes as $route) {
                $methods = implode('|', $route->methods());
                $uri = $route->uri();
                $name = $route->getName() ?? 'NO_NAME';
                $action = $route->getActionName();

                // Filter op medewerkers routes
                if (str_contains($uri, 'medewerker') || str_contains($action, 'Medewerker') || str_contains($name, 'medewerker')) {
                    $this->line("👤 {$methods} | {$uri} | {$name} | {$action}");
                }
            }

            $this->info('');
            $this->info('🔍 Zoek naar CREATE routes:');
            foreach ($routes as $route) {
                $methods = implode('|', $route->methods());
                $uri = $route->uri();
                $name = $route->getName() ?? 'NO_NAME';
                $action = $route->getActionName();

                if (str_contains($uri, 'create') || str_contains($methods, 'POST')) {
                    if (str_contains($uri, 'medewerker') || str_contains($action, 'Medewerker')) {
                        $this->line("➕ {$methods} | {$uri} | {$name} | {$action}");
                    }
                }
            }

            $this->info('');
            $this->info('🔍 Alle POST routes:');
            foreach ($routes as $route) {
                if (in_array('POST', $route->methods())) {
                    $uri = $route->uri();
                    $name = $route->getName() ?? 'NO_NAME';
                    $action = $route->getActionName();
                    
                    $this->line("📤 POST | {$uri} | {$name} | {$action}");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error debugging routes: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}