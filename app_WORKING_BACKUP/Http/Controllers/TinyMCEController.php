<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TinyMCEController extends Controller
{
    public function upload(Request $request)
    {
        Log::info('Upload request received', $request->all());
        
        try {
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120', // 5MB max
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                Log::info('Uploading file: ' . $filename);
                
                // Maak directory als het niet bestaat
                if (!Storage::disk('public')->exists('staff-notes-images')) {
                    Storage::disk('public')->makeDirectory('staff-notes-images');
                }
                
                // Sla afbeelding op in storage/app/public/staff-notes-images
                $path = $file->storeAs('staff-notes-images', $filename, 'public');
                
                Log::info('File stored at: ' . $path);
                
                $url = Storage::url($path);
                Log::info('Generated URL: ' . $url);
                
                // Retourneer de URL in CKEditor formaat
                return response()->json([
                    'url' => $url,
                    'location' => $url, // Voor backward compatibility
                    'success' => true
                ]);
            }
            
            return response()->json(['error' => 'Geen bestand ontvangen'], 400);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Ongeldig bestand: ' . implode(', ', $e->errors()['file'])], 400);
        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
            return response()->json(['error' => 'Upload mislukt: ' . $e->getMessage()], 500);
        }
    }
}