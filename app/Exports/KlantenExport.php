<?php
namespace App\Exports;

use App\Models\Klant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KlantenExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Klant::orderBy('naam')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Naam',
            'Email',
            'Telefoon',
            'Adres',
            'Postcode',
            'Plaats',
            'Geboortedatum',
            'Geslacht',
            'Lengte (cm)',
            'Gewicht (kg)',
            'Sport',
            'Niveau',
            'Doelen',
            'Medische Info',
            'Opmerkingen',
            'Aangemaakt op',
            'Laatst bijgewerkt'
        ];
    }

    public function map($klant): array
    {
        return [
            $klant->id,
            $klant->naam,
            $klant->email,
            $klant->telefoon ?? '',
            '', // adres
            '', // postcode
            '', // plaats
            $klant->geboortedatum ? (is_string($klant->geboortedatum) ? $klant->geboortedatum : $klant->geboortedatum->format('d-m-Y')) : '',
            $klant->geslacht ?? '',
            '', // lengte
            '', // gewicht
            $klant->sport ?? '',
            $klant->niveau ?? '',
            $klant->doelen ?? '',
            $klant->medische_geschiedenis ?? '',
            '', // opmerkingen
            $klant->created_at ? $klant->created_at->format('d-m-Y H:i') : '',
            $klant->updated_at ? $klant->updated_at->format('d-m-Y H:i') : '',
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
