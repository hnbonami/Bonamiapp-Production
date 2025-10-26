<?php

namespace App\Imports;

use App\Models\Klant;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class KlantenImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['naam']) && empty($row['email'])) {
            return null;
        }

        // Parse geboortedatum if exists
        $geboortedatum = null;
        if (!empty($row['geboortedatum'])) {
            try {
                // Try different date formats
                if (is_numeric($row['geboortedatum'])) {
                    // Excel serial date
                    $geboortedatum = Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($row['geboortedatum'] - 2);
                } else {
                    // Text date
                    $geboortedatum = Carbon::parse($row['geboortedatum']);
                }
            } catch (\Exception $e) {
                $geboortedatum = null;
            }
        }

        // Maak klant aan
        $klant = Klant::create([
            'organisatie_id' => auth()->user()->organisatie_id,
            'naam' => $row['naam'],
            'voornaam' => $row['voornaam'] ?? null,
            'achternaam' => $row['achternaam'] ?? null,
            'email' => $row['email'],
            'telefoon' => $row['telefoon'] ?? null,
            'adres' => $row['adres'] ?? null,
            'postcode' => $row['postcode'] ?? null,
            'woonplaats' => $row['woonplaats'] ?? null,
            'geboortedatum' => isset($row['geboortedatum']) ? $this->parseDate($row['geboortedatum']) : null,
            'notities' => $row['notities'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // NIEUW: Maak automatisch user account aan
        $this->createUserAccountForKlant($klant);
        
        return $klant;
    }

    /**
     * Maak automatisch een user account aan voor een geïmporteerde klant
     */
    private function createUserAccountForKlant(Klant $klant)
    {
        // Check of er al een user bestaat met dit email
        $existingUser = User::where('email', $klant->email)->first();
        
        if ($existingUser) {
            \Log::info('User bestaat al voor geïmporteerde klant', [
                'klant_id' => $klant->id,
                'email' => $klant->email,
                'existing_user_id' => $existingUser->id
            ]);
            return $existingUser;
        }
        
        // Maak nieuwe user aan
        try {
            $user = User::create([
                'name' => $klant->naam,
                'email' => $klant->email,
                'password' => \Hash::make(\Str::random(16)), // Random wachtwoord
                'role' => 'klant',
                'organisatie_id' => $klant->organisatie_id,
                'status' => 'active',
                'email_verified_at' => null, // Klant moet email verifiëren
            ]);
            
            \Log::info('✅ User account aangemaakt voor geïmporteerde klant', [
                'klant_id' => $klant->id,
                'user_id' => $user->id,
                'email' => $klant->email
            ]);
            
            return $user;
            
        } catch (\Exception $e) {
            \Log::error('❌ Fout bij aanmaken user account voor geïmporteerde klant', [
                'klant_id' => $klant->id,
                'email' => $klant->email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'naam' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefoon' => 'nullable|string|max:255',
            'adres' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'plaats' => 'nullable|string|max:255',
            'geslacht' => 'nullable|in:man,vrouw,anders',
            'lengte_cm' => 'nullable|numeric|min:100|max:250',
            'gewicht_kg' => 'nullable|numeric|min:30|max:200',
            'sport' => 'nullable|string|max:255',
            'niveau' => 'nullable|string|max:255',
        ];
    }

    public function batchSize(): int
    {
        return 100; // Process 100 rows at a time
    }

    public function chunkSize(): int
    {
        return 100; // Read 100 rows at a time
    }
}