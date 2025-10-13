<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class CheckUsersRoute extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:check-users-route';

    /**
     * The console command description.
     */
    protected $description = 'Check welke controller de admin/users route afhandelt';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking admin/users route...');

        try {
            // Zoek de route voor admin/users
            $routes = Route::getRoutes();
            
            foreach ($routes as $route) {
                $uri = $route->uri();
                $methods = $route->methods();
                $action = $route->getAction();
                
                if (str_contains($uri, 'admin/users') || str_contains($uri, 'users') && in_array('GET', $methods)) {
                    $this->info("Route found:");
                    $this->line("  URI: {$uri}");
                    $this->line("  Methods: " . implode(', ', $methods));
                    $this->line("  Name: " . ($route->getName() ?? 'unnamed'));
                    
                    if (isset($action['controller'])) {
                        $this->line("  Controller: {$action['controller']}");
                    } elseif (isset($action['uses'])) {
                        $this->line("  Action: {$action['uses']}");
                    } else {
                        $this->line("  Action: Closure");
                    }
                    $this->line("---");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}