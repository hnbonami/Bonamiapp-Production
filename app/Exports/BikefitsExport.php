<?php

namespace App\Exports;

use App\Models\Bikefit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BikefitsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Bikefit::with('klant', 'user')->orderBy('datum', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Klant Naam',
            'Klant Email',
            'Datum',
            'Testtype',
            'Type Fitting',
            'Fietsmerk',
            'Kadermaat',
            'Bouwjaar',
            'Frametype',
            'Lengte (cm)',
            'Binnenbeenlengte (cm)',
            'Armlengte (cm)',
            'Romplengte (cm)',
            'Schouderbreedte (cm)',
            'Zadel-Trapas Hoek',
            'Zadel-Trapas Afstand',
            'Stuur-Trapas Hoek',
            'Stuur-Trapas Afstand',
            'Zadel Lengte Center Top',
            'Aanpassingen Zadel',
            'Aanpassingen Setback',
            'Aanpassingen Reach',
            'Aanpassingen Drop',
            'Stuurpen Aanpassing',
            'Stuurpen Pre',
            'Stuurpen Post',
            'Type Zadel',
            'Zadeltil',
            'Zadelbreedte',
            'Nieuw Testzadel',
            'Rotatie Aanpassingen',
            'Inclinatie Aanpassingen',
            'Ophoging Links',
            'Ophoging Rechts',
            'Algemene Klachten',
            'Beenlengteverschil',
            'Beenlengteverschil Details',
            'Lenigheid Hamstrings',
            'Steunzolen',
            'Steunzolen Reden',
            'Schoenmaat',
            'Voetbreedte',
            'Voetpositie',
            'Straight Leg Raise Links',
            'Straight Leg Raise Rechts',
            'Knieflexie Links',
            'Knieflexie Rechts',
            'Heup Endorotatie Links',
            'Heup Endorotatie Rechts',
            'Heup Exorotatie Links',
            'Heup Exorotatie Rechts',
            'Enkeldorsiflexie Links',
            'Enkeldorsiflexie Rechts',
            'One Leg Squat Links',
            'One Leg Squat Rechts',
            'Opmerkingen',
            'Interne Opmerkingen',
            'Uitgevoerd door',
            'Aangemaakt op'
        ];
    }

    public function map($bikefit): array
    {
        return [
            $bikefit->id,
            $bikefit->klant->naam ?? 'Onbekend',
            $bikefit->klant->email ?? '',
            $bikefit->datum ? $bikefit->datum->format('Y-m-d') : '',
            $bikefit->testtype,
            $bikefit->type_fitting,
            $bikefit->fietsmerk,
            $bikefit->kadermaat,
            $bikefit->bouwjaar,
            $bikefit->frametype,
            $bikefit->lengte_cm,
            $bikefit->binnenbeenlengte_cm,
            $bikefit->armlengte_cm,
            $bikefit->romplengte_cm,
            $bikefit->schouderbreedte_cm,
            $bikefit->zadel_trapas_hoek,
            $bikefit->zadel_trapas_afstand,
            $bikefit->stuur_trapas_hoek,
            $bikefit->stuur_trapas_afstand,
            $bikefit->zadel_lengte_center_top,
            $bikefit->aanpassingen_zadel,
            $bikefit->aanpassingen_setback,
            $bikefit->aanpassingen_reach,
            $bikefit->aanpassingen_drop,
            $bikefit->aanpassingen_stuurpen_aan ? 'Ja' : 'Nee',
            $bikefit->aanpassingen_stuurpen_pre,
            $bikefit->aanpassingen_stuurpen_post,
            $bikefit->type_zadel,
            $bikefit->zadeltil,
            $bikefit->zadelbreedte,
            $bikefit->nieuw_testzadel,
            $bikefit->rotatie_aanpassingen,
            $bikefit->inclinatie_aanpassingen,
            $bikefit->ophoging_li,
            $bikefit->ophoging_re,
            $bikefit->algemene_klachten,
            $bikefit->beenlengteverschil ? 'Ja' : 'Nee',
            $bikefit->beenlengteverschil_cm,
            $bikefit->lenigheid_hamstrings,
            $bikefit->steunzolen ? 'Ja' : 'Nee',
            $bikefit->steunzolen_reden,
            $bikefit->schoenmaat,
            $bikefit->voetbreedte,
            $bikefit->voetpositie,
            $bikefit->straight_leg_raise_links,
            $bikefit->straight_leg_raise_rechts,
            $bikefit->knieflexie_links,
            $bikefit->knieflexie_rechts,
            $bikefit->heup_endorotatie_links,
            $bikefit->heup_endorotatie_rechts,
            $bikefit->heup_exorotatie_links,
            $bikefit->heup_exorotatie_rechts,
            $bikefit->enkeldorsiflexie_links,
            $bikefit->enkeldorsiflexie_rechts,
            $bikefit->one_leg_squat_links,
            $bikefit->one_leg_squat_rechts,
            $bikefit->opmerkingen,
            $bikefit->interne_opmerkingen,
            $bikefit->user->name ?? 'Onbekend',
            $bikefit->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as a header
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFCCCCCC']
                ]
            ],
        ];
    }
}