<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class CheckRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:check-routes';

    /**
     * The console command description.
     */
    protected $description = 'Check routes related to users and admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking routes...');

        $routes = Route::getRoutes();

        $this->info('📋 Routes related to users and admin:');

        foreach ($routes as $route) {
            $uri = $route->uri();
            $name = $route->getName();
            $action = $route->getActionName();

            // Filter routes that contain 'user' or 'admin'
            if (str_contains($uri, 'user') || str_contains($uri, 'admin') || str_contains($uri, 'medewerker')) {
                $this->line("URI: {$uri}");
                $this->line("  Name: " . ($name ?: 'no name'));
                $this->line("  Action: {$action}");
                $this->line("  Methods: " . implode(',', $route->methods()));
                $this->line("---");
            }
        }

        return Command::SUCCESS;
    }
}