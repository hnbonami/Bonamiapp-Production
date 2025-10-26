<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class DebugUsersDisplay extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:debug-users-display';

    /**
     * The console command description.
     */
    protected $description = 'Debug wat er wordt getoond in de users lijst';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Debugging users display...');

        try {
            // Simuleer exact wat de UserManagementController doet
            $this->info('📊 All users in database:');
            $allUsers = User::all();
            foreach ($allUsers as $user) {
                $roleIcon = $user->role === 'admin' ? '👑' : (in_array($user->role, ['medewerker', 'stagiair']) ? '👤' : '🧑‍💼');
                $this->line("{$roleIcon} ID: {$user->id} | Email: {$user->email} | Role: {$user->role} | Name: {$user->name}");
            }

            $this->info('');
            $this->info('📋 Role counts:');
            $adminCount = $allUsers->where('role', 'admin')->count();
            $medewerkerCount = $allUsers->where('role', 'medewerker')->count();
            $klantCount = $allUsers->where('role', 'klant')->count();
            
            $this->line("👑 Admins: {$adminCount}");
            $this->line("👤 Medewerkers: {$medewerkerCount}");
            $this->line("🧑‍💼 Klanten: {$klantCount}");
            $this->line("🏢 Totaal medewerkers (admin + medewerker): " . ($adminCount + $medewerkerCount));

            $this->info('');
            $this->info('🔍 Simulate UserManagementController query:');
            
            // Simuleer de query zoals in de controller
            $query = User::query();
            $users = $query->orderBy('created_at', 'desc')->paginate(15);
            
            $this->line("Paginated users count: " . $users->count());
            $this->line("Total users in paginator: " . $users->total());
            
            $this->info('Users in eerste pagina:');
            foreach ($users as $user) {
                $roleIcon = $user->role === 'admin' ? '👑' : (in_array($user->role, ['medewerker', 'stagiair']) ? '👤' : '🧑‍💼');
                $this->line("  {$roleIcon} {$user->email} ({$user->role}) - {$user->name}");
            }

            $this->info('');
            $this->info('🔍 Check filters - are there any hidden users?');
            $hiddenUsers = User::where('status', '!=', 'active')->get();
            if ($hiddenUsers->count() > 0) {
                $this->warn("⚠️ Found {$hiddenUsers->count()} users with non-active status:");
                foreach ($hiddenUsers as $user) {
                    $this->line("  {$user->email} ({$user->role}) - Status: " . ($user->status ?? 'NULL'));
                }
            } else {
                $this->info("✅ No hidden users found");
            }

            $this->info('');
            $this->info('🔍 Check if there are users without voornaam/achternaam:');
            $incompleteUsers = User::where('role', '!=', 'klant')
                                  ->where(function($q) {
                                      $q->whereNull('voornaam')
                                        ->orWhereNull('achternaam');
                                  })->get();
            
            if ($incompleteUsers->count() > 0) {
                $this->warn("⚠️ Found {$incompleteUsers->count()} medewerkers with incomplete data:");
                foreach ($incompleteUsers as $user) {
                    $this->line("  {$user->email} ({$user->role}) - Voornaam: " . ($user->voornaam ?? 'NULL') . " | Achternaam: " . ($user->achternaam ?? 'NULL'));
                }
            } else {
                $this->info("✅ All medewerkers have complete data");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error debugging users display: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}