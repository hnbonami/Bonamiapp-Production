<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PersonalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Haal alle gebruikers op die nog geen personal_data hebben
        $users = User::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('personal_data')
                  ->whereRaw('personal_data.user_id = users.id');
        })->get();

        foreach ($users as $user) {
            DB::table('personal_data')->insert([
                'user_id' => $user->id,
                'voornaam' => $user->name ?? '',
                'achternaam' => '',
                'email' => $user->email ?? '',
                'telefoon' => '',
                'geboortedatum' => null,
                'gewicht' => null,
                'lengte' => null,
                'medische_info' => null,
                'doelstellingen' => null,
                'sport_ervaring' => null,
                'opmerkingen' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info('Personal data records aangemaakt voor ' . $users->count() . ' gebruikers.');
    }
}