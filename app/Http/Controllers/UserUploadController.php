<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\UserUpload;
use App\Models\Bikefit;
use App\Models\BikefitImage;
use App\Models\Upload;

class UserUploadController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:102400', // max 100MB (blijft hoger voor user uploads)
                'compress' => 'sometimes|boolean',
                // Optional bikefit attachment
                'bikefit_id' => 'nullable|exists:bikefits,id',
                'caption' => 'nullable|string',
                'position' => 'nullable|integer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation failures for easier debugging
            try { Log::warning('Upload validation failed', ['errors' => $e->errors(), 'ip' => $request->ip()]); } catch (\Throwable $__log) { /* ignore logging errors */ }
            return response()->json(['ok' => false, 'errors' => $e->errors()], 422);
        }

    $user = $request->user();
    $file = $request->file('file');
    $originalName = $file->getClientOriginalName();
    $mime = $file->getClientMimeType();
    $size = $file->getSize();

    // Log an upload attempt (helps trace which files/users hit limits)
    try { Log::info('User upload attempt', ['user_id' => $user?->id, 'name' => $originalName, 'size' => $size, 'mime' => $mime, 'ip' => $request->ip()]); } catch (\Throwable $__log) { /* ignore logging errors */ }

        // Store original file under private/uploads/{user_id}/ (correct pad, geen dubbele 'private/')
        try {
            $path = $file->storeAs('uploads/' . $user->id, time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $originalName), 'private');
        } catch (\Throwable $ex) {
            // Log storage failure with exception details
            try { Log::error('Upload storage failed', ['user_id' => $user?->id, 'name' => $originalName, 'size' => $size, 'exception' => $ex->getMessage()]); } catch (\Throwable $__log) { /* ignore logging errors */ }
            return response()->json(['ok' => false, 'message' => 'Opslag fout: ' . $ex->getMessage()], 500);
        }

        // Controleer of bestand echt bestaat na opslag
        if (!Storage::disk('local')->exists($path)) {
            Log::error('Upload bestand niet gevonden na opslag', ['user_id' => $user->id, 'path' => $path]);
            return response()->json(['ok' => false, 'message' => 'Bestand niet gevonden na upload. Controleer schrijfrechten van storage/app/uploads.'], 500);
        }

        $compressed = false;
        $compressed_path = null;
        $compressed_size = null;

        if ($request->boolean('compress')) {
            // Simple gzip compression - note: not ideal for already compressed files; may increase CPU load.
            try {
                $contents = Storage::get($path);
                $gz = gzencode($contents, 6);
                if ($gz !== false) {
                    $compressed_path = $path . '.gz';
                    Storage::put($compressed_path, $gz);
                    $compressed = true;
                    $compressed_size = strlen($gz);
                }
            } catch (\Throwable $ex) {
                // compression failure shouldn't fail the whole request - log it
                try { Log::warning('Upload compression failed', ['user_id' => $user?->id, 'path' => $path, 'exception' => $ex->getMessage()]); } catch (\Throwable $__log) { /* ignore logging errors */ }
                $compressed = false;
            }
        }

        // Sla upload op in de uploads-tabel zodat de download-link altijd werkt
        $upload = Upload::create([
            'user_id' => $user->id,
            'path' => $path,
            'size' => $size,
            'compressed' => $compressed,
        ]);

        try { Log::info('Upload saved', ['upload_id' => $upload->id, 'user_id' => $user->id, 'path' => $path, 'size' => $size, 'compressed' => $compressed]); } catch (\Throwable $__log) { /* ignore logging errors */ }

        $resp = ['ok' => true, 'upload_id' => $upload->id, 'path' => $path];

        // If a bikefit_id was provided, create a BikefitImage record automatically.
        if ($request->filled('bikefit_id')) {
            try {
                $bikefit = Bikefit::find($request->input('bikefit_id'));
                if ($bikefit) {
                    // If this is the first image for the bikefit, mark as cover
                    $isCover = $bikefit->images()->count() === 0 ? 1 : 0;
                    // Allow overriding via request
                    if ($request->has('is_cover')) {
                        $isCover = $request->boolean('is_cover') ? 1 : 0;
                        if ($isCover) {
                            // unset previous covers
                            $bikefit->images()->update(['is_cover' => 0]);
                        }
                    }

                    $img = $bikefit->images()->create([
                        'path' => $path,
                        'caption' => $request->input('caption'),
                        'position' => $request->input('position') ?? $bikefit->images()->count(),
                        'is_cover' => $isCover,
                    ]);
                    $resp['bikefit_image'] = $img;
                }
            } catch (\Throwable $__e) {
                try { Log::warning('Bikefit image attach failed', ['exception' => $__e->getMessage(), 'bikefit_id' => $request->input('bikefit_id'), 'path' => $path]); } catch (\Throwable $__log) { }
            }
        }

        return response()->json($resp);
    }

    public function destroy($id)
    {
        $upload = Upload::findOrFail($id);
        Storage::disk('private')->delete($upload->path); // Verwijder het bestand van de schijf
        $upload->delete(); // Verwijder de database-entry

        return redirect()->back()->with('success', 'Bestand succesvol verwijderd.');
    }
}
