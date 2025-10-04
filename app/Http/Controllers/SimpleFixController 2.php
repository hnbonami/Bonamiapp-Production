<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SimpleFixController extends Controller
{
    public function show()
    {
        return view('simple-fix');
    }

    public function fix(Request $request)
    {
        $action = $request->input('action');

        try {
            switch ($action) {
                case 'create_table':
                    $this->createPersonalDataTable();
                    return back()->with('success', 'Personal_data tabel aangemaakt!');

                case 'add_fields':
                    $this->addAddressFields();
                    return back()->with('success', 'Adres velden toegevoegd!');

                case 'create_user_record':
                    $this->createUserRecord();
                    return back()->with('success', 'Gebruiker record aangemaakt!');

                default:
                    return back()->with('error', 'Onbekende actie');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Fout: ' . $e->getMessage());
        }
    }

    private function createPersonalDataTable()
    {
        DB::statement("
            CREATE TABLE personal_data (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint unsigned NOT NULL,
                voornaam varchar(255) DEFAULT NULL,
                achternaam varchar(255) DEFAULT NULL,
                email varchar(255) DEFAULT NULL,
                telefoon varchar(255) DEFAULT NULL,
                geboortedatum date DEFAULT NULL,
                gewicht decimal(5,2) DEFAULT NULL,
                lengte decimal(5,2) DEFAULT NULL,
                medische_info text DEFAULT NULL,
                doelstellingen text DEFAULT NULL,
                sport_ervaring varchar(255) DEFAULT NULL,
                opmerkingen text DEFAULT NULL,
                adres varchar(255) DEFAULT NULL,
                stad varchar(255) DEFAULT NULL,
                postcode varchar(10) DEFAULT NULL,
                land varchar(255) DEFAULT NULL,
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY personal_data_user_id_unique (user_id),
                KEY personal_data_user_id_index (user_id),
                CONSTRAINT personal_data_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function addAddressFields()
    {
        if (!Schema::hasColumn('personal_data', 'adres')) {
            DB::statement("ALTER TABLE personal_data ADD COLUMN adres varchar(255) DEFAULT NULL AFTER telefoon");
        }
        if (!Schema::hasColumn('personal_data', 'stad')) {
            DB::statement("ALTER TABLE personal_data ADD COLUMN stad varchar(255) DEFAULT NULL AFTER adres");
        }
        if (!Schema::hasColumn('personal_data', 'postcode')) {
            DB::statement("ALTER TABLE personal_data ADD COLUMN postcode varchar(10) DEFAULT NULL AFTER stad");
        }
        if (!Schema::hasColumn('personal_data', 'land')) {
            DB::statement("ALTER TABLE personal_data ADD COLUMN land varchar(255) DEFAULT NULL AFTER postcode");
        }
    }

    private function createUserRecord()
    {
        $user = Auth::user();
        
        DB::table('personal_data')->insert([
            'user_id' => $user->id,
            'voornaam' => $user->name ?? '',
            'achternaam' => '',
            'email' => $user->email ?? '',
            'telefoon' => '',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function testSave(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Update personal_data record
            $updated = DB::table('personal_data')
                ->where('user_id', $user->id)
                ->update([
                    'voornaam' => $request->input('voornaam'),
                    'achternaam' => $request->input('achternaam'),
                    'adres' => $request->input('adres'),
                    'stad' => $request->input('stad'),
                    'updated_at' => now()
                ]);

            if ($updated) {
                return back()->with('success', "✅ Test succesvol! {$updated} record(s) bijgewerkt. Ga nu naar je instellingen pagina en probeer het daar!");
            } else {
                return back()->with('error', '❌ Geen records bijgewerkt. Mogelijk bestaat er geen record voor deze gebruiker.');
            }

        } catch (\Exception $e) {
            return back()->with('error', '❌ Test gefaald: ' . $e->getMessage());
        }
    }
}