<?php

namespace App\Http\Controllers;

use App\Services\DocumentCompressionService;
use Illuminate\Http\Request;
use App\Models\Klant;

class DocumentController extends Controller
{
    // ...bestaande methodes...

    public function uploadDocument(Request $request, Klant $klant)
    {
        $request->validate([
            'document' => 'required|file|max:10240', // 10MB
            'type' => 'required|string',
        ]);

        // Gebruik compressie service
        $compressionService = new DocumentCompressionService();
        $file = $request->file('document');

        // Probeer te comprimeren als te groot
        if (!$compressionService->validateSize($file, 10240)) {
            $compressedFile = $compressionService->compressIfNeeded($file, 10240);
            
            if ($compressedFile === false) {
                return back()->with('error', 'Bestand is te groot en kon niet worden gecomprimeerd. Max: ' . 
                    $compressionService->formatFileSize(10240 * 1024));
            }
            
            $file = $compressedFile;
        }

        // Sla document op
        $path = $file->store('documents', 'public');

        // ...bestaande code voor database opslag...

        return back()->with('success', 'Document succesvol geüpload');
    }
}