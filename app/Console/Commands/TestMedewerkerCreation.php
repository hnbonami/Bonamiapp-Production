<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\InvitationToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestMedewerkerCreation extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:test-medewerker-creation';

    /**
     * The console command description.
     */
    protected $description = 'Test het aanmaken van een nieuwe medewerker';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Testing medewerker creation...');

        try {
            $email = 'test-medewerker-' . now()->timestamp . '@bonami.nl';
            $this->info("📧 Creating medewerker with email: {$email}");

            // Simuleer exact wat de controller doet
            $validated = [
                'voornaam' => 'Test',
                'achternaam' => 'Medewerker',
                'email' => $email,
                'telefoon' => '0123456789',
                'rol' => 'medewerker'
            ];

            $this->info('🔄 Creating user record...');
            
            // Generate a temporary password
            $temporaryPassword = Str::random(12);
            
            // Create user record
            $user = User::create([
                'name' => $validated['voornaam'] . ' ' . $validated['achternaam'],
                'voornaam' => $validated['voornaam'],
                'achternaam' => $validated['achternaam'],
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => $validated['rol'],
                'telefoon' => $validated['telefoon'] ?? null,
                'email_verified_at' => now(),
            ]);

            $this->info("✅ User created with ID: {$user->id}");

            // Create invitation token
            $token = Str::random(60);
            
            InvitationToken::create([
                'email' => $validated['email'],
                'token' => $token,
                'temporary_password' => $temporaryPassword,
                'type' => 'medewerker',
                'expires_at' => now()->addDays(7),
                'created_by' => 1 // Assume admin user with ID 1
            ]);

            $this->info("✅ Invitation token created");

            // Verify user exists
            $verifyUser = User::where('email', $validated['email'])->first();
            if ($verifyUser) {
                $this->info("✅ VERIFICATION: User exists in database");
                $this->info("   - ID: {$verifyUser->id}");
                $this->info("   - Email: {$verifyUser->email}");
                $this->info("   - Role: {$verifyUser->role}");
                $this->info("   - Name: {$verifyUser->name}");
            } else {
                $this->error("❌ VERIFICATION FAILED: User not found in database");
                return Command::FAILURE;
            }

            $this->info('🎉 Medewerker creation test SUCCESSFUL!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}