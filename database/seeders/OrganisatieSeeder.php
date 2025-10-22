<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organisatie;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OrganisatieSeeder extends Seeder
{
    /**
     * Maak de eerste organisatie aan (Bonami Sportcoaching) en een superadmin gebruiker
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Maak Bonami Sportcoaching organisatie aan
            $bonami = Organisatie::create([
                'naam' => 'Bonami Sportcoaching',
                'email' => 'info@bonamisportcoaching.nl',
                'telefoon' => null,
                'adres' => null,
                'postcode' => null,
                'plaats' => null,
                'status' => 'actief',
                'trial_eindigt_op' => null,
                'maandelijkse_prijs' => 0,
                'notities' => 'Originele organisatie - eigenaar van het platform',
            ]);

            $this->command->info('✓ Bonami Sportcoaching organisatie aangemaakt (ID: ' . $bonami->id . ')');

            // Update bestaande users om ze aan Bonami te koppelen
            // Behoud hun huidige role (admin, medewerker, klant)
            $aantalUsers = User::whereNull('organisatie_id')->update([
                'organisatie_id' => $bonami->id,
            ]);

            $this->command->info("✓ {$aantalUsers} bestaande gebruikers gekoppeld aan Bonami");

            // Update bestaande klanten om ze aan Bonami te koppelen
            $aantalKlanten = DB::table('klanten')
                ->whereNull('organisatie_id')
                ->update(['organisatie_id' => $bonami->id]);

            $this->command->info("✓ {$aantalKlanten} bestaande klanten gekoppeld aan Bonami");

            // Maak een superadmin aan (of update bestaande admin gebruiker)
            $eersteAdmin = User::where('role', 'admin')->first();
            
            if ($eersteAdmin) {
                $eersteAdmin->update(['role' => 'superadmin']);
                $this->command->info("✓ Bestaande admin ({$eersteAdmin->email}) is nu superadmin");
            } else {
                $superadmin = User::create([
                    'name' => 'Super Admin',
                    'email' => 'admin@bonamisportcoaching.nl',
                    'password' => Hash::make('password'), // Verander dit!
                    'organisatie_id' => $bonami->id,
                    'role' => 'superadmin',
                ]);
                $this->command->info('✓ Superadmin gebruiker aangemaakt (email: admin@bonamisportcoaching.nl)');
            }
            
            $this->command->warn('⚠ Vergeet niet het wachtwoord te wijzigen!');
        });
    }
}
