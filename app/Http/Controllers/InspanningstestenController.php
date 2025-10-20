<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inspanningstest;
use App\Models\Klant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InspanningstestenController extends Controller
{
    /**
     * Toon het inspanningstesten import formulier
     */
    public function showImport()
    {
        return view('admin.inspanningstesten-import');
    }

    /**
     * Download Excel template voor inspanningstesten import
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Header rij met alle inspanningstest velden
            $headers = [
                'klant_email',
                'klant_naam',
                'datum',
                'testtype',
                'leeftijd',
                'gewicht_kg',
                'lengte_cm',
                'geslacht',
                'rusthartslag',
                'max_hartslag',
                'vo2max',
                'watt_kg_ratio',
                'ftp_watt',
                'anaerobe_drempel_watt',
                'anaerobe_drempel_hartslag',
                'aerobe_drempel_watt',
                'aerobe_drempel_hartslag',
                'zone1_hartslag_min',
                'zone1_hartslag_max',
                'zone1_watt_min',
                'zone1_watt_max',
                'zone2_hartslag_min',
                'zone2_hartslag_max',
                'zone2_watt_min',
                'zone2_watt_max',
                'zone3_hartslag_min',
                'zone3_hartslag_max',
                'zone3_watt_min',
                'zone3_watt_max',
                'zone4_hartslag_min',
                'zone4_hartslag_max',
                'zone4_watt_min',
                'zone4_watt_max',
                'zone5_hartslag_min',
                'zone5_hartslag_max',
                'zone5_watt_min',
                'zone5_watt_max',
                'lactaat_zone1',
                'lactaat_zone2',
                'lactaat_zone3',
                'lactaat_zone4',
                'lactaat_zone5',
                'cadans_gemiddeld',
                'cadans_optimaal',
                'testduur_minuten',
                'afstand_km',
                'trainingsdoel',
                'ervaring_jaren',
                'trainingsuren_week',
                'opmerkingen',
                'aanbevelingen'
            ];
            
            // Zet headers in de eerste rij
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }
            
            // Voeg een voorbeeld rij toe
            $exampleData = [
                'jan.jansen@example.com',
                'Jan Jansen',
                date('Y-m-d'),
                'VO2max test',
                '35',
                '75',
                '180',
                'Man',
                '45',
                '185',
                '55',
                '4.2',
                '315',
                '300',
                '165',
                '270',
                '145',
                '0', '122', '0', '189',
                '123', '140', '190', '216',
                '141', '158', '217', '252',
                '159', '176', '253', '288',
                '177', '185', '289', '315',
                '1.2', '2.1', '3.5', '6.2', '10.5',
                '85', '90',
                '45', '25',
                'Verhogen duurvermogen',
                '5', '10',
                'Goede uitgangspositie',
                'Focus op zone 2 en 3 training'
            ];
            
            $col = 'A';
            foreach ($exampleData as $value) {
                $sheet->setCellValue($col . '2', $value);
                $col++;
            }
            
            // Maak Excel bestand
            $writer = new Xlsx($spreadsheet);
            $filename = 'inspanningstesten_import_template_' . date('Y-m-d') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            \Log::error('Inspanningstesten template download failed: ' . $e->getMessage());
            return back()->with('error', 'Template kon niet worden gedownload: ' . $e->getMessage());
        }
    }

    /**
     * Import inspanningstesten vanuit Excel bestand
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
            ]);
            
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            if (empty($rows)) {
                return back()->with('error', 'Het Excel bestand is leeg');
            }
            
            // Eerste rij bevat headers
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];
            
            // Loop door alle data rijen (skip header)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Skip lege rijen
                if (empty(array_filter($row))) {
                    continue;
                }
                
                try {
                    // Map kolommen naar data
                    $data = [];
                    foreach ($headers as $index => $header) {
                        if (isset($row[$index])) {
                            $data[$header] = $row[$index];
                        }
                    }
                    
                    // Zoek klant op basis van email OF naam
                    $klant = null;
                    if (!empty($data['klant_email'])) {
                        $klant = \App\Models\Klant::where('email', $data['klant_email'])->first();
                    }
                    
                    if (!$klant && !empty($data['klant_naam'])) {
                        $klant = \App\Models\Klant::where(\DB::raw('CONCAT(voornaam, " ", naam)'), 'LIKE', '%' . $data['klant_naam'] . '%')->first();
                    }
                    
                    if (!$klant) {
                        $skippedCount++;
                        $errors[] = "Rij " . ($i + 1) . ": Klant niet gevonden";
                        continue;
                    }
                    
                    // Bereid inspanningstest data voor met CORRECTE veldnamen
                    $testData = [
                        'klant_id' => $klant->id,
                        'user_id' => auth()->id(),
                        'datum' => !empty($data['datum']) ? $data['datum'] : now()->format('Y-m-d'),
                        'testtype' => $data['testtype'] ?? 'VO2max test',
                        'leeftijd' => $data['leeftijd'] ?? null,
                        'gewicht_kg' => $data['gewicht_kg'] ?? null,
                        'lengte_cm' => $data['lengte_cm'] ?? null,
                        'geslacht' => $data['geslacht'] ?? null,
                        'rusthartslag' => $data['rusthartslag'] ?? null,
                        'max_hartslag' => $data['max_hartslag'] ?? null,
                        'vo2max' => $data['vo2max'] ?? null,
                        'watt_kg_ratio' => $data['watt_kg_ratio'] ?? null,
                        'ftp_watt' => $data['ftp_watt'] ?? null,
                        'anaerobe_drempel_watt' => $data['anaerobe_drempel_watt'] ?? null,
                        'anaerobe_drempel_hartslag' => $data['anaerobe_drempel_hartslag'] ?? null,
                        'aerobe_drempel_watt' => $data['aerobe_drempel_watt'] ?? null,
                        'aerobe_drempel_hartslag' => $data['aerobe_drempel_hartslag'] ?? null,
                        'zone1_hartslag_min' => $data['zone1_hartslag_min'] ?? null,
                        'zone1_hartslag_max' => $data['zone1_hartslag_max'] ?? null,
                        'zone1_watt_min' => $data['zone1_watt_min'] ?? null,
                        'zone1_watt_max' => $data['zone1_watt_max'] ?? null,
                        'zone2_hartslag_min' => $data['zone2_hartslag_min'] ?? null,
                        'zone2_hartslag_max' => $data['zone2_hartslag_max'] ?? null,
                        'zone2_watt_min' => $data['zone2_watt_min'] ?? null,
                        'zone2_watt_max' => $data['zone2_watt_max'] ?? null,
                        'zone3_hartslag_min' => $data['zone3_hartslag_min'] ?? null,
                        'zone3_hartslag_max' => $data['zone3_hartslag_max'] ?? null,
                        'zone3_watt_min' => $data['zone3_watt_min'] ?? null,
                        'zone3_watt_max' => $data['zone3_watt_max'] ?? null,
                        'zone4_hartslag_min' => $data['zone4_hartslag_min'] ?? null,
                        'zone4_hartslag_max' => $data['zone4_hartslag_max'] ?? null,
                        'zone4_watt_min' => $data['zone4_watt_min'] ?? null,
                        'zone4_watt_max' => $data['zone4_watt_max'] ?? null,
                        'zone5_hartslag_min' => $data['zone5_hartslag_min'] ?? null,
                        'zone5_hartslag_max' => $data['zone5_hartslag_max'] ?? null,
                        'zone5_watt_min' => $data['zone5_watt_min'] ?? null,
                        'zone5_watt_max' => $data['zone5_watt_max'] ?? null,
                        'lactaat_zone1' => $data['lactaat_zone1'] ?? null,
                        'lactaat_zone2' => $data['lactaat_zone2'] ?? null,
                        'lactaat_zone3' => $data['lactaat_zone3'] ?? null,
                        'lactaat_zone4' => $data['lactaat_zone4'] ?? null,
                        'lactaat_zone5' => $data['lactaat_zone5'] ?? null,
                        'cadans_gemiddeld' => $data['cadans_gemiddeld'] ?? null,
                        'cadans_optimaal' => $data['cadans_optimaal'] ?? null,
                        'testduur_minuten' => $data['testduur_minuten'] ?? null,
                        'afstand_km' => $data['afstand_km'] ?? null,
                        'trainingsdoel' => $data['trainingsdoel'] ?? null,
                        'ervaring_jaren' => $data['ervaring_jaren'] ?? null,
                        'trainingsuren_week' => $data['trainingsuren_week'] ?? null,
                        'opmerkingen' => $data['opmerkingen'] ?? null,
                        'aanbevelingen' => $data['aanbevelingen'] ?? null,
                    ];
                    
                    // Maak inspanningstest aan
                    \App\Models\Inspanningstest::create($testData);
                    $importedCount++;
                    
                } catch (\Exception $e) {
                    $skippedCount++;
                    $errors[] = "Rij " . ($i + 1) . ": " . $e->getMessage();
                    \Log::error("Inspanningstest import error rij $i: " . $e->getMessage());
                }
            }
            
            $message = "Import voltooid! $importedCount inspanningstesten geïmporteerd";
            if ($skippedCount > 0) {
                $message .= ", $skippedCount rijen overgeslagen";
            }
            
            if (!empty($errors)) {
                $message .= ". Fouten: " . implode('; ', array_slice($errors, 0, 5));
            }
            
            \Log::info("Inspanningstesten import: $importedCount succesvol, $skippedCount overgeslagen");
            
            return redirect()->route('inspanningstesten.import.form')->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Inspanningstesten import failed: ' . $e->getMessage());
            return back()->with('error', 'Import mislukt: ' . $e->getMessage());
        }
    }

    /**
     * Export alle inspanningstesten naar Excel
     */
    public function export()
    {
        try {
            $testen = \App\Models\Inspanningstest::with('klant')->orderBy('datum', 'desc')->get();
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Headers
            $headers = [
                'ID', 'Klant Naam', 'Klant Email', 'Datum', 'Testtype', 'Leeftijd', 'Gewicht (kg)', 'Lengte (cm)',
                'Geslacht', 'Rusthartslag', 'Max Hartslag', 'VO2max', 'Watt/kg Ratio', 'FTP Watt',
                'Anaerobe Drempel Watt', 'Anaerobe Drempel Hartslag', 'Aerobe Drempel Watt', 'Aerobe Drempel Hartslag',
                'Zone 1 HS Min', 'Zone 1 HS Max', 'Zone 1 Watt Min', 'Zone 1 Watt Max',
                'Zone 2 HS Min', 'Zone 2 HS Max', 'Zone 2 Watt Min', 'Zone 2 Watt Max',
                'Zone 3 HS Min', 'Zone 3 HS Max', 'Zone 3 Watt Min', 'Zone 3 Watt Max',
                'Zone 4 HS Min', 'Zone 4 HS Max', 'Zone 4 Watt Min', 'Zone 4 Watt Max',
                'Zone 5 HS Min', 'Zone 5 HS Max', 'Zone 5 Watt Min', 'Zone 5 Watt Max',
                'Lactaat Zone 1', 'Lactaat Zone 2', 'Lactaat Zone 3', 'Lactaat Zone 4', 'Lactaat Zone 5',
                'Cadans Gemiddeld', 'Cadans Optimaal', 'Testduur (min)', 'Afstand (km)',
                'Trainingsdoel', 'Ervaring (jaren)', 'Trainingsuren/week', 'Opmerkingen', 'Aanbevelingen'
            ];
            
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }
            
            // Data rijen
            $row = 2;
            foreach ($testen as $test) {
                $sheet->setCellValue('A' . $row, $test->id);
                $sheet->setCellValue('B' . $row, $test->klant ? $test->klant->voornaam . ' ' . $test->klant->naam : '');
                $sheet->setCellValue('C' . $row, $test->klant ? $test->klant->email : '');
                $sheet->setCellValue('D' . $row, $test->datum);
                $sheet->setCellValue('E' . $row, $test->testtype);
                $sheet->setCellValue('F' . $row, $test->leeftijd);
                $sheet->setCellValue('G' . $row, $test->gewicht_kg);
                $sheet->setCellValue('H' . $row, $test->lengte_cm);
                $sheet->setCellValue('I' . $row, $test->geslacht);
                $sheet->setCellValue('J' . $row, $test->rusthartslag);
                $sheet->setCellValue('K' . $row, $test->max_hartslag);
                $sheet->setCellValue('L' . $row, $test->vo2max);
                $sheet->setCellValue('M' . $row, $test->watt_kg_ratio);
                $sheet->setCellValue('N' . $row, $test->ftp_watt);
                $sheet->setCellValue('O' . $row, $test->anaerobe_drempel_watt);
                $sheet->setCellValue('P' . $row, $test->anaerobe_drempel_hartslag);
                $sheet->setCellValue('Q' . $row, $test->aerobe_drempel_watt);
                $sheet->setCellValue('R' . $row, $test->aerobe_drempel_hartslag);
                $sheet->setCellValue('S' . $row, $test->zone1_hartslag_min);
                $sheet->setCellValue('T' . $row, $test->zone1_hartslag_max);
                $sheet->setCellValue('U' . $row, $test->zone1_watt_min);
                $sheet->setCellValue('V' . $row, $test->zone1_watt_max);
                $sheet->setCellValue('W' . $row, $test->zone2_hartslag_min);
                $sheet->setCellValue('X' . $row, $test->zone2_hartslag_max);
                $sheet->setCellValue('Y' . $row, $test->zone2_watt_min);
                $sheet->setCellValue('Z' . $row, $test->zone2_watt_max);
                $sheet->setCellValue('AA' . $row, $test->zone3_hartslag_min);
                $sheet->setCellValue('AB' . $row, $test->zone3_hartslag_max);
                $sheet->setCellValue('AC' . $row, $test->zone3_watt_min);
                $sheet->setCellValue('AD' . $row, $test->zone3_watt_max);
                $sheet->setCellValue('AE' . $row, $test->zone4_hartslag_min);
                $sheet->setCellValue('AF' . $row, $test->zone4_hartslag_max);
                $sheet->setCellValue('AG' . $row, $test->zone4_watt_min);
                $sheet->setCellValue('AH' . $row, $test->zone4_watt_max);
                $sheet->setCellValue('AI' . $row, $test->zone5_hartslag_min);
                $sheet->setCellValue('AJ' . $row, $test->zone5_hartslag_max);
                $sheet->setCellValue('AK' . $row, $test->zone5_watt_min);
                $sheet->setCellValue('AL' . $row, $test->zone5_watt_max);
                $sheet->setCellValue('AM' . $row, $test->lactaat_zone1);
                $sheet->setCellValue('AN' . $row, $test->lactaat_zone2);
                $sheet->setCellValue('AO' . $row, $test->lactaat_zone3);
                $sheet->setCellValue('AP' . $row, $test->lactaat_zone4);
                $sheet->setCellValue('AQ' . $row, $test->lactaat_zone5);
                $sheet->setCellValue('AR' . $row, $test->cadans_gemiddeld);
                $sheet->setCellValue('AS' . $row, $test->cadans_optimaal);
                $sheet->setCellValue('AT' . $row, $test->testduur_minuten);
                $sheet->setCellValue('AU' . $row, $test->afstand_km);
                $sheet->setCellValue('AV' . $row, $test->trainingsdoel);
                $sheet->setCellValue('AW' . $row, $test->ervaring_jaren);
                $sheet->setCellValue('AX' . $row, $test->trainingsuren_week);
                $sheet->setCellValue('AY' . $row, $test->opmerkingen);
                $sheet->setCellValue('AZ' . $row, $test->aanbevelingen);
                $row++;
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'inspanningstesten_export_' . date('Y-m-d_His') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            \Log::error('Inspanningstesten export failed: ' . $e->getMessage());
            return back()->with('error', 'Export mislukt: ' . $e->getMessage());
        }
    }
}