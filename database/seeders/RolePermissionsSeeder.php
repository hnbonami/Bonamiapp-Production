<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionsSeeder extends Seeder
{
    public function run()
    {
        // Definieer alle beschikbare tabs
        $tabs = [
            'dashboard',
            'klanten', 
            'medewerkers',
            'instagram',
            'nieuwsbrief',
            'sjablonen',
            'testzadels',
            'admin'
        ];

        // Definieer alle test types
        $testTypes = [
            'bikefit',
            'inspanningstest_fietsen',
            'inspanningstest_lopen', 
            'voedingsadvies',
            'zadeldrukmeting',
            'maatbepaling'
        ];

        // Admin krijgt toegang tot alles
        foreach ($tabs as $tab) {
            DB::table('role_permissions')->insert([
                'role_name' => 'admin',
                'tab_name' => $tab,
                'can_access' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        foreach ($testTypes as $testType) {
            DB::table('role_test_permissions')->insert([
                'role_name' => 'admin',
                'test_type' => $testType,
                'can_access' => true,
                'can_create' => true,
                'can_edit' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Medewerker krijgt beperkte toegang
        $medewerkerTabs = ['dashboard', 'klanten', 'instagram', 'nieuwsbrief', 'sjablonen', 'testzadels'];
        foreach ($medewerkerTabs as $tab) {
            DB::table('role_permissions')->insert([
                'role_name' => 'medewerker',
                'tab_name' => $tab,
                'can_access' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        foreach ($testTypes as $testType) {
            DB::table('role_test_permissions')->insert([
                'role_name' => 'medewerker',
                'test_type' => $testType,
                'can_access' => true,
                'can_create' => true,
                'can_edit' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Klant krijgt alleen dashboard toegang
        DB::table('role_permissions')->insert([
            'role_name' => 'klant',
            'tab_name' => 'dashboard',
            'can_access' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Klant kan alleen eigen tests bekijken
        foreach ($testTypes as $testType) {
            DB::table('role_test_permissions')->insert([
                'role_name' => 'klant',
                'test_type' => $testType,
                'can_access' => true,
                'can_create' => false,
                'can_edit' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}