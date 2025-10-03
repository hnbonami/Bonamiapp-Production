<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Klant;
use Illuminate\Support\Facades\Hash;

class ResetDatabase extends Command
{
    protected $signature = 'db:reset-fresh';
    protected $description = 'Reset database en maak nieuwe admin aan';

    public function handle()
    {
        $this->info('🔄 Database wordt gereset...');
        
        // Truncate alle tabellen
        $this->call('migrate:fresh');
        
        $this->info('✅ Database gereset');
        
        // Maak nieuwe admin user aan
        $admin = User::create([
            'name' => 'Admin Bonami',
            'email' => 'admin@bonami.be',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'user_type' => 'admin',
            'klant_id' => null
        ]);
        
        $this->info("✅ Admin aangemaakt: admin@bonami.be / admin123");
        
        // Maak een voorbeeld klant aan
        $klant = Klant::create([
            'naam' => 'Jan Janssens',
            'email' => 'jan@example.com',
            'telefoon' => '0123456789',
            'geboortedatum' => '1990-01-01',
            'geslacht' => 'M',
            'lengte_cm' => 180,
            'gewicht_kg' => 75.5,
            'beroep' => 'Software Developer',
            'sportervaring' => 'Recreatief fietsen, 3x per week'
        ]);
        
        // Maak user voor deze klant
        $klantUser = User::create([
            'name' => 'Jan Janssens',
            'email' => 'jan@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'user_type' => 'klant',
            'klant_id' => $klant->id
        ]);
        
        $this->info("✅ Voorbeeld klant aangemaakt: jan@example.com / password");
        
        $this->info('🎉 Database reset voltooid!');
        $this->info('');
        $this->info('Login gegevens:');
        $this->info('Admin: admin@bonami.be / admin123');
        $this->info('Klant: jan@example.com / password');
        
        return 0;
    }
}