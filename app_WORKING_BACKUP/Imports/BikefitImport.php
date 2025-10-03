<?php

namespace App\Imports;

use App\Models\Bikefit;
use App\Models\Klant;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class BikefitImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['klant_email']) && empty($row['klant_naam'])) {
            return null;
        }

        // Find klant by email first, then by name
        $klant = null;
        if (!empty($row['klant_email'])) {
            $klant = Klant::where('email', $row['klant_email'])->first();
        }
        if (!$klant && !empty($row['klant_naam'])) {
            $klant = Klant::where('naam', $row['klant_naam'])->first();
        }

        if (!$klant) {
            // Skip this row if klant not found
            \Log::warning('Klant niet gevonden voor bikefit import', [
                'klant_email' => $row['klant_email'] ?? 'N/A',
                'klant_naam' => $row['klant_naam'] ?? 'N/A'
            ]);
            return null;
        }

        // Parse datum if exists
        $datum = null;
        if (!empty($row['datum'])) {
            try {
                if (is_numeric($row['datum'])) {
                    // Excel serial date
                    $datum = Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($row['datum'] - 2);
                } else {
                    // Text date
                    $datum = Carbon::parse($row['datum']);
                }
            } catch (\Exception $e) {
                $datum = now(); // Default to today if date parsing fails
            }
        } else {
            $datum = now();
        }

        return new Bikefit([
            'klant_id' => $klant->id,
            'user_id' => auth()->id(), // Current user who imports
            'datum' => $datum,
            'testtype' => $row['testtype'] ?? 'standaard bikefit',
            'type_fitting' => $row['type_fitting'] ?? '',
            
            // Fietsgegevens
            'fietsmerk' => $row['fietsmerk'] ?? '',
            'kadermaat' => $row['kadermaat'] ?? '',
            'bouwjaar' => is_numeric($row['bouwjaar'] ?? null) ? $row['bouwjaar'] : null,
            'frametype' => $row['frametype'] ?? '',
            
            // Lichaamsmaten
            'lengte_cm' => is_numeric($row['lengte_cm'] ?? null) ? $row['lengte_cm'] : null,
            'binnenbeenlengte_cm' => is_numeric($row['binnenbeenlengte_cm'] ?? null) ? $row['binnenbeenlengte_cm'] : null,
            'armlengte_cm' => is_numeric($row['armlengte_cm'] ?? null) ? $row['armlengte_cm'] : null,
            'romplengte_cm' => is_numeric($row['romplengte_cm'] ?? null) ? $row['romplengte_cm'] : null,
            'schouderbreedte_cm' => is_numeric($row['schouderbreedte_cm'] ?? null) ? $row['schouderbreedte_cm'] : null,
            
            // Zitpositie metingen
            'zadel_trapas_hoek' => is_numeric($row['zadel_trapas_hoek'] ?? null) ? $row['zadel_trapas_hoek'] : null,
            'zadel_trapas_afstand' => is_numeric($row['zadel_trapas_afstand'] ?? null) ? $row['zadel_trapas_afstand'] : null,
            'stuur_trapas_hoek' => is_numeric($row['stuur_trapas_hoek'] ?? null) ? $row['stuur_trapas_hoek'] : null,
            'stuur_trapas_afstand' => is_numeric($row['stuur_trapas_afstand'] ?? null) ? $row['stuur_trapas_afstand'] : null,
            'zadel_lengte_center_top' => is_numeric($row['zadel_lengte_center_top'] ?? null) ? $row['zadel_lengte_center_top'] : null,
            
            // Aanpassingen
            'aanpassingen_zadel' => is_numeric($row['aanpassingen_zadel'] ?? null) ? $row['aanpassingen_zadel'] : null,
            'aanpassingen_setback' => is_numeric($row['aanpassingen_setback'] ?? null) ? $row['aanpassingen_setback'] : null,
            'aanpassingen_reach' => is_numeric($row['aanpassingen_reach'] ?? null) ? $row['aanpassingen_reach'] : null,
            'aanpassingen_drop' => is_numeric($row['aanpassingen_drop'] ?? null) ? $row['aanpassingen_drop'] : null,
            
            // Stuurpen aanpassingen
            'aanpassingen_stuurpen_aan' => in_array(strtolower($row['aanpassingen_stuurpen_aan'] ?? ''), ['1', 'ja', 'yes', 'true']) ? 1 : 0,
            'aanpassingen_stuurpen_pre' => is_numeric($row['aanpassingen_stuurpen_pre'] ?? null) ? $row['aanpassingen_stuurpen_pre'] : null,
            'aanpassingen_stuurpen_post' => is_numeric($row['aanpassingen_stuurpen_post'] ?? null) ? $row['aanpassingen_stuurpen_post'] : null,
            
            // Zadel
            'type_zadel' => $row['type_zadel'] ?? '',
            'zadeltil' => is_numeric($row['zadeltil'] ?? null) ? $row['zadeltil'] : null,
            'zadelbreedte' => is_numeric($row['zadelbreedte'] ?? null) ? $row['zadelbreedte'] : null,
            'nieuw_testzadel' => $row['nieuw_testzadel'] ?? '',
            
            // Schoenplaatjes
            'rotatie_aanpassingen' => $row['rotatie_aanpassingen'] ?? '',
            'inclinatie_aanpassingen' => $row['inclinatie_aanpassingen'] ?? '',
            'ophoging_li' => is_numeric($row['ophoging_li'] ?? null) ? $row['ophoging_li'] : null,
            'ophoging_re' => is_numeric($row['ophoging_re'] ?? null) ? $row['ophoging_re'] : null,
            
            // Anamnese
            'algemene_klachten' => $row['algemene_klachten'] ?? '',
            'beenlengteverschil' => in_array(strtolower($row['beenlengteverschil'] ?? ''), ['1', 'ja', 'yes', 'true']) ? 1 : 0,
            'beenlengteverschil_cm' => $row['beenlengteverschil_cm'] ?? '',
            'lenigheid_hamstrings' => $row['lenigheid_hamstrings'] ?? '',
            'steunzolen' => in_array(strtolower($row['steunzolen'] ?? ''), ['1', 'ja', 'yes', 'true']) ? 1 : 0,
            'steunzolen_reden' => $row['steunzolen_reden'] ?? '',
            
            // Voetmeting
            'schoenmaat' => is_numeric($row['schoenmaat'] ?? null) ? $row['schoenmaat'] : null,
            'voetbreedte' => is_numeric($row['voetbreedte'] ?? null) ? $row['voetbreedte'] : null,
            'voetpositie' => in_array($row['voetpositie'] ?? '', ['neutraal', 'pronatie', 'supinatie']) ? $row['voetpositie'] : null,
            
            // Mobiliteit
            'straight_leg_raise_links' => $row['straight_leg_raise_links'] ?? '',
            'straight_leg_raise_rechts' => $row['straight_leg_raise_rechts'] ?? '',
            'knieflexie_links' => $row['knieflexie_links'] ?? '',
            'knieflexie_rechts' => $row['knieflexie_rechts'] ?? '',
            'heup_endorotatie_links' => $row['heup_endorotatie_links'] ?? '',
            'heup_endorotatie_rechts' => $row['heup_endorotatie_rechts'] ?? '',
            'heup_exorotatie_links' => $row['heup_exorotatie_links'] ?? '',
            'heup_exorotatie_rechts' => $row['heup_exorotatie_rechts'] ?? '',
            'enkeldorsiflexie_links' => $row['enkeldorsiflexie_links'] ?? '',
            'enkeldorsiflexie_rechts' => $row['enkeldorsiflexie_rechts'] ?? '',
            'one_leg_squat_links' => $row['one_leg_squat_links'] ?? '',
            'one_leg_squat_rechts' => $row['one_leg_squat_rechts'] ?? '',
            
            // Opmerkingen
            'opmerkingen' => $row['opmerkingen'] ?? '',
            'interne_opmerkingen' => $row['interne_opmerkingen'] ?? '',
            
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'klant_email' => 'nullable|email',
            'klant_naam' => 'nullable|string',
            'testtype' => 'nullable|string',
            'lengte_cm' => 'nullable|numeric|min:100|max:250',
            'binnenbeenlengte_cm' => 'nullable|numeric|min:50|max:150',
            'schoenmaat' => 'nullable|integer|min:35|max:50',
            'voetbreedte' => 'nullable|numeric|min:6|max:13',
            'voetpositie' => 'nullable|in:neutraal,pronatie,supinatie',
        ];
    }

    public function batchSize(): int
    {
        return 50; // Process 50 rows at a time
    }

    public function chunkSize(): int
    {
        return 50; // Read 50 rows at a time
    }
}