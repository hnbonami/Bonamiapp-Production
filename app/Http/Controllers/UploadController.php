<?php
namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\Bikefit;
use App\Services\DocumentCompressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    protected $compressionService;

    public function __construct(DocumentCompressionService $compressionService)
    {
        $this->compressionService = $compressionService;
    }
    public function show($uploadId)
    {
        $upload = Upload::findOrFail($uploadId);
        $user = auth()->user();

        // Check toegangsrechten
        if (!$upload->heeftToegang($user)) {
            \Log::warning('Ongeautoriseerde toegang tot upload', [
                'upload_id' => $uploadId,
                'user_id' => $user ? $user->id : null,
                'toegang' => $upload->toegang
            ]);
            
            abort(403, 'Je hebt geen toegang tot dit document.');
        }

        $path = $upload->path;
        if (!Storage::disk('private')->exists($path)) {
            // Toon nu expliciet de view upload_not_found buiten de errors map
            return response()->view('upload_not_found', ['upload' => $upload], 404);
        }
        
        \Log::info('Document succesvol getoond', [
            'upload_id' => $uploadId,
            'user_id' => $user->id,
            'toegang' => $upload->toegang
        ]);
        
        return response()->file(Storage::disk('private')->path($path));
    }

    public function upload(Request $request, $klantId, $bikefitId)
    {
        \Log::info('UploadController@upload aangeroepen', ['klant_id' => $klantId, 'bikefit_id' => $bikefitId, 'user_id' => auth()->id()]);
        
        try {
            // Verhoog time limit voor grote uploads
            set_time_limit(300); // 5 minuten
            ini_set('memory_limit', '256M');
            
            // Verhoogde limiet naar 50MB (51200 KB) + toegangsrechten validatie
            $request->validate([
                'file' => 'required|file|max:51200|mimes:pdf,mp4,mov,avi,jpg,jpeg,png,doc,docx',
                'toegang' => 'required|in:alleen_mezelf,klant,alle_medewerkers,iedereen',
                'naam' => 'nullable|string|max:255',
                'beschrijving' => 'nullable|string|max:1000',
                'is_cover' => 'nullable|boolean'
            ]);

            $bikefit = Bikefit::findOrFail($bikefitId);
            $file = $request->file('file');
            
            // Probeer bestand te comprimeren indien te groot (alleen voor afbeeldingen)
            $originalSize = $file->getSize();
            $mimeType = $file->getMimeType();
            
            \Log::info('Upload details', [
                'grootte' => $this->compressionService->formatFileSize($originalSize),
                'mime_type' => $mimeType
            ]);
            
            $fileToUpload = $file;
            
            // Alleen comprimeren voor afbeeldingen > 5MB
            if (str_starts_with($mimeType, 'image/') && $originalSize > 5242880) {
                \Log::info('Probeer afbeelding te comprimeren...');
                
                $compressedFile = $this->compressionService->compressIfNeeded($file, 20480);
                
                if ($compressedFile !== false && $compressedFile !== $file) {
                    $fileToUpload = $compressedFile;
                    \Log::info('Afbeelding gecomprimeerd', [
                        'van' => $this->compressionService->formatFileSize($originalSize),
                        'naar' => $this->compressionService->formatFileSize($fileToUpload->getSize())
                    ]);
                }
            }
            
            // Sla bestand op
            $path = $fileToUpload->store('uploads/' . $bikefit->id, 'private');

            $upload = new Upload([
                'user_id' => auth()->id(),
                'klant_id' => $klantId,
                'bikefit_id' => $bikefitId,
                'path' => $path,
                'size' => $fileToUpload->getSize(),
                'toegang' => $request->input('toegang', 'alle_medewerkers'),
                'naam' => $request->input('naam'),
                'beschrijving' => $request->input('beschrijving'),
                'is_cover' => $request->boolean('is_cover', false),
            ]);
            $upload->save();
            
            \Log::info('Upload succesvol aangemaakt', [
                'upload_id' => $upload->id,
                'finale_grootte' => $this->compressionService->formatFileSize($fileToUpload->getSize())
            ]);

            // Redirect terug naar de editpagina met een hash naar de succesmelding
            return redirect()
                ->to(route('bikefit.edit', ['klant' => $klantId, 'bikefit' => $bikefitId]) . '#upload-success')
                ->with('upload_success', true)
                ->with('upload_link', route('uploads.show', $upload));
                
        } catch (\Exception $e) {
            \Log::error('Fout bij uploaden bestand', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->back()
                ->withErrors(['file' => 'Er is een fout opgetreden bij het uploaden: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $upload = Upload::findOrFail($id);
        $user = auth()->user();

        // Check of gebruiker mag verwijderen (uploader of admin)
        if ($upload->user_id !== $user->id && $user->role !== 'admin') {
            \Log::warning('Ongeautoriseerde poging tot verwijderen upload', [
                'upload_id' => $id,
                'user_id' => $user->id
            ]);
            
            abort(403, 'Je hebt geen rechten om dit document te verwijderen.');
        }

        // Verwijder fysieke bestand
        if (Storage::disk('private')->exists($upload->path)) {
            Storage::disk('private')->delete($upload->path);
        }

        $upload->delete();

        \Log::info('Upload verwijderd', [
            'upload_id' => $id,
            'user_id' => $user->id
        ]);

        return redirect()->back()->with('success', 'Document succesvol verwijderd.');
    }
}
