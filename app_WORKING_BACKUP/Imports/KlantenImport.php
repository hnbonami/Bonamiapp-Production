<?php

namespace App\Imports;

use App\Models\Klant;
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

        return new Klant([
            'naam' => $row['naam'] ?? '',
            'email' => $row['email'] ?? '',
            'telefoon' => $row['telefoon'] ?? '',
            'adres' => $row['adres'] ?? '',
            'postcode' => $row['postcode'] ?? '',
            'plaats' => $row['plaats'] ?? '',
            'geboortedatum' => $geboortedatum,
            'geslacht' => $row['geslacht'] ?? '',
            'lengte_cm' => is_numeric($row['lengte_cm'] ?? null) ? $row['lengte_cm'] : null,
            'gewicht_kg' => is_numeric($row['gewicht_kg'] ?? null) ? $row['gewicht_kg'] : null,
            'sport' => $row['sport'] ?? '',
            'niveau' => $row['niveau'] ?? '',
            'doelen' => $row['doelen'] ?? '',
            'medische_info' => $row['medische_info'] ?? '',
            'opmerkingen' => $row['opmerkingen'] ?? '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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