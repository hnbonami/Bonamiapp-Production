<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Maak admin user alleen aan als deze nog niet bestaat
        if (!\App\Models\User::where('email', 'info@bonami-sportcoaching.be')->exists()) {
            User::factory()->create([
                'name' => 'Admin Gebruiker',
                'email' => 'info@bonami-sportcoaching.be',
                'role' => 'admin',
                'password' => bcrypt('password'),
            ]);
        }

        // Seed Performance Pulse default branding (organisatie ID 1)
        $this->call(PerformancePulseDefaultBrandingSeeder::class);

        // Activeer alle standaard sjablonen van organisatie 1
        \DB::table('sjablonen')
            ->where('organisatie_id', 1)
            ->update(['is_actief' => 1]);
        
        \Log::info('✅ Standaard sjablonen geactiveerd', [
            'count' => \DB::table('sjablonen')->where('organisatie_id', 1)->where('is_actief', 1)->count()
        ]);
    }
}
