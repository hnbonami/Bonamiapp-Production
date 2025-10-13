<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DebugUsersTable extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:debug-users';

    /**
     * The console command description.
     */
    protected $description = 'Debug users table structure and data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Debugging users table...');

        try {
            // Check table structure
            $this->info('📋 Users table columns:');
            $columns = Schema::getColumnListing('users');
            foreach ($columns as $column) {
                $this->line("  - {$column}");
            }

            // Check all users data
            $this->info('');
            $this->info('👥 All users in database:');
            $users = DB::table('users')->get();
            
            foreach ($users as $user) {
                $this->line("ID: {$user->id} | Email: {$user->email} | Role: {$user->role} | Name: {$user->name}");
                $this->line("  Voornaam: " . ($user->voornaam ?? 'NULL') . " | Achternaam: " . ($user->achternaam ?? 'NULL'));
                $this->line("  Created: {$user->created_at}");
                $this->line("---");
            }

            // Check role distribution
            $this->info('📊 Role distribution:');
            $roleStats = DB::table('users')
                          ->select('role', DB::raw('COUNT(*) as count'))
                          ->groupBy('role')
                          ->get();
            
            foreach ($roleStats as $stat) {
                $this->line("  {$stat->role}: {$stat->count}");
            }

            // Check medewerkers specifically
            $this->info('');
            $this->info('🔧 Medewerkers (role != klant):');
            $medewerkers = DB::table('users')->where('role', '!=', 'klant')->get();
            
            foreach ($medewerkers as $medewerker) {
                $this->line("  {$medewerker->email} ({$medewerker->role}) - Name: {$medewerker->name}");
                $this->line("    Voornaam: " . ($medewerker->voornaam ?? 'NULL') . " | Achternaam: " . ($medewerker->achternaam ?? 'NULL'));
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}