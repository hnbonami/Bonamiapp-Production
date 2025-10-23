<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KlantImportController extends Controller
{
    /**
     * Toon de klanten import pagina
     */
    public function showImportForm()
    {
        return view('klanten.import');
    }

    /**
     * Verwerk de klanten Excel import
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|max:10240'
        ]);

        try {
            $organisatieId = auth()->user()->organisatie_id;
            $file = $request->file('excel_file');
            
            \Log::info('📥 Klanten import gestart', [
                'user_id' => auth()->id(),
                'organisatie_id' => $organisatieId,
                'filename' => $file->getClientOriginalName()
            ]);

            // Gebruik PhpSpreadsheet
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Verwijder header
            $header = array_shift($rows);
            
            $imported = 0;
            $errors = [];
            
            foreach ($rows as $index => $row) {
                try {
                    if (empty(array_filter($row))) continue;
                    
                    $klantData = [
                        'organisatie_id' => $organisatieId,
                        'naam' => $row[0] ?? null,
                        'email' => $row[1] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    if ($klantData['naam'] || $klantData['email']) {
                        \App\Models\Klant::create($klantData);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Rij " . ($index + 2) . ": " . $e->getMessage();
                }
            }
            
            \Log::info('✅ Klanten import voltooid', [
                'imported' => $imported,
                'errors' => count($errors)
            ]);
            
            if (count($errors) > 0) {
                return redirect()->back()
                    ->with('warning', "Import: {$imported} klanten, " . count($errors) . " fouten.");
            }
            
            return redirect()->back()
                ->with('success', "Klanten import succesvol! {$imported} klanten geïmporteerd.");
                
        } catch (\Exception $e) {
            \Log::error('❌ Klanten import gefaald', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Import gefaald: ' . $e->getMessage());
        }
    }
}