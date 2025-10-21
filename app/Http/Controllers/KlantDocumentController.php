<?php

namespace App\Http\Controllers;

use App\Models\Klant;
use App\Models\KlantDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KlantDocumentController extends Controller
{
    /**
     * Upload nieuw document
     */
    public function store(Request $request, Klant $klant)
    {
        try {
            $validated = $request->validate([
                'document' => 'required|file|max:51200', // Max 50MB
                'naam' => 'nullable|string|max:255',
                'beschrijving' => 'nullable|string',
            ]);

            $file = $request->file('document');
            $extension = strtolower($file->getClientOriginalExtension());
            $uniqueName = Str::uuid() . '.' . $extension;

            // Sla bestand op in public storage (werkt altijd)
            $storedPath = Storage::disk('public')->putFileAs(
                'klant_documenten',
                $file,
                $uniqueName
            );

            \Log::info('📁 Bestand opgeslagen', [
                'stored_path' => $storedPath,
                'full_path' => storage_path('app/' . $storedPath),
                'unique_name' => $uniqueName,
                'file_exists' => file_exists(storage_path('app/' . $storedPath))
            ]);

            // Maak database record
            $document = KlantDocument::create([
                'klant_id' => $klant->id,
                'titel' => $validated['naam'] ?? $file->getClientOriginalName(),
                'naam' => $validated['naam'] ?? $file->getClientOriginalName(),
                'beschrijving' => $validated['beschrijving'] ?? null,
                'bestandsnaam' => $file->getClientOriginalName(),
                'opgeslagen_naam' => $uniqueName,
                'bestandstype' => $extension,
                'bestandsgrootte' => $file->getSize(),
                'categorie' => 'Algemeen',
                'upload_datum' => now(),
            ]);

            \Log::info('✅ Document geüpload', [
                'document_id' => $document->id,
                'klant_id' => $klant->id
            ]);

            return redirect()->back()->with('success', 'Document succesvol geüpload!');

        } catch (\Exception $e) {
            \Log::error('❌ Document upload fout: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Fout bij uploaden: ' . $e->getMessage());
        }
    }

    /**
     * Download document
     */
    public function download(Klant $klant, KlantDocument $document)
    {
        // Security check
        if ($document->klant_id !== $klant->id) {
            abort(403, 'Geen toegang tot dit document');
        }

        \Log::info('🔍 Download poging', [
            'document_id' => $document->id,
            'opgeslagen_naam' => $document->opgeslagen_naam,
            'klant_id' => $klant->id
        ]);

        // Check verschillende mogelijke paden
        $possiblePaths = [
            storage_path('app/public/klant_documenten/' . $document->opgeslagen_naam),
            storage_path('app/private/klant_documenten/' . $document->opgeslagen_naam),
            storage_path('app/klant_documenten/' . $document->opgeslagen_naam),
        ];

        \Log::info('📂 Checking paths:', ['paths' => $possiblePaths]);

        $path = null;
        foreach ($possiblePaths as $possiblePath) {
            \Log::info('Checking: ' . $possiblePath . ' - exists: ' . (file_exists($possiblePath) ? 'YES' : 'NO'));
            if (file_exists($possiblePath)) {
                $path = $possiblePath;
                break;
            }
        }

        if (!$path) {
            // Check wat er WEL in de directory staat
            $privateDir = storage_path('app/private/klant_documenten');
            if (is_dir($privateDir)) {
                $files = scandir($privateDir);
                \Log::error('❌ Document niet gevonden, maar directory bestaat wel', [
                    'document_id' => $document->id,
                    'opgeslagen_naam' => $document->opgeslagen_naam,
                    'checked_paths' => $possiblePaths,
                    'files_in_directory' => $files
                ]);
            } else {
                \Log::error('❌ Directory bestaat niet!', [
                    'directory' => $privateDir
                ]);
            }
            
            return redirect()->back()->with('error', 'Document niet gevonden. Controleer de logs voor details.');
        }

        \Log::info('✅ Document gevonden, downloading...', ['path' => $path]);
        return response()->download($path, $document->bestandsnaam);
    }

    /**
     * Toon edit form voor document
     */
    public function edit(Klant $klant, KlantDocument $document)
    {
        // Security check
        if ($document->klant_id !== $klant->id) {
            abort(403, 'Geen toegang tot dit document');
        }

        return view('klanten.documenten.edit', compact('klant', 'document'));
    }

    /**
     * Update document gegevens
     */
    public function update(Request $request, Klant $klant, KlantDocument $document)
    {
        try {
            // Security check
            if ($document->klant_id !== $klant->id) {
                abort(403, 'Geen toegang tot dit document');
            }

            $validated = $request->validate([
                'naam' => 'required|string|max:255',
                'beschrijving' => 'nullable|string',
            ]);

            $document->update([
                'titel' => $validated['naam'],
                'naam' => $validated['naam'],
                'beschrijving' => $validated['beschrijving'],
            ]);

            \Log::info('✏️ Document bijgewerkt', [
                'document_id' => $document->id,
                'klant_id' => $klant->id
            ]);

            return redirect()->route('klanten.show', $klant)->with('success', 'Document bijgewerkt!');

        } catch (\Exception $e) {
            \Log::error('❌ Document update fout: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Fout bij bijwerken: ' . $e->getMessage());
        }
    }

    /**
     * Verwijder document
     */
    public function destroy(Klant $klant, KlantDocument $document)
    {
        try {
            // Security check
            if ($document->klant_id !== $klant->id) {
                abort(403, 'Geen toegang tot dit document');
            }

            // Verwijder bestand van verschillende mogelijke locaties
            $possiblePaths = [
                'public/klant_documenten/' . $document->opgeslagen_naam,
                'private/klant_documenten/' . $document->opgeslagen_naam,
            ];

            foreach ($possiblePaths as $path) {
                if (Storage::disk('local')->exists($path)) {
                    Storage::disk('local')->delete($path);
                    break;
                }
            }

            // Verwijder database record
            $document->delete();

            \Log::info('🗑️ Document verwijderd', ['document_id' => $document->id]);

            return redirect()->back()->with('success', 'Document succesvol verwijderd!');

        } catch (\Exception $e) {
            \Log::error('❌ Document verwijdering fout: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Fout bij verwijderen: ' . $e->getMessage());
        }
    }
}
