<?php
namespace App\Exports;

use App\Models\Medewerker;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MedewerkersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Medewerker::all()->map(function($m) {
            return [
                'Naam' => $m->naam,
                'E-mailadres' => $m->email,
                'Status' => $m->status,
                'Aangemaakt op' => $m->created_at ? $m->created_at->format('d-m-Y') : '',
            ];
        });
    }
    public function headings(): array
    {
        return [
            'Naam', 'E-mailadres', 'Status', 'Aangemaakt op'
        ];
    }
}
