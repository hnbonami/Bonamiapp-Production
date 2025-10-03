<?php
namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\Bikefit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function show($uploadId)
    {
        $upload = Upload::findOrFail($uploadId);
        $path = $upload->path;
        if (!Storage::disk('private')->exists($path)) {
            // Toon nu expliciet de view upload_not_found buiten de errors map
            return response()->view('upload_not_found', ['upload' => $upload], 404);
        }
        return response()->file(Storage::disk('private')->path($path));
    }

    public function upload(Request $request, $klantId, $bikefitId)
    {
        \Log::info('UploadController@upload aangeroepen', ['klant_id' => $klantId, 'bikefit_id' => $bikefitId, 'user_id' => auth()->id()]);
        $request->validate([
            'file' => 'required|file|max:15360|mimes:pdf,mp4,mov,avi,jpg,jpeg,png'
        ]);

        $bikefit = Bikefit::findOrFail($bikefitId);
        $file = $request->file('file');
        $path = $file->store('uploads/' . $bikefit->id, 'private');

        $upload = new Upload([
            'user_id' => auth()->id(),
            'path' => $path,
            'size' => $file->getSize(),
            'bikefit_id' => $bikefitId, // Gebruik direct het id uit de route
        ]);
        $upload->save();
        \Log::info('Upload aangemaakt', ['upload_id' => $upload->id, 'bikefit_id' => $upload->bikefit_id, 'path' => $upload->path]);

        // Redirect terug naar de editpagina met een hash naar de succesmelding
        return redirect()
            ->to(route('bikefit.edit', ['klant' => $klantId, 'bikefit' => $bikefitId]) . '#upload-success')
            ->with('upload_success', true)
            ->with('upload_link', route('uploads.show', $upload));
    }

    public function destroy($id)
    {
        $upload = Upload::findOrFail($id);
        $upload->delete();

        return redirect()->back()->with('success', 'Bestand succesvol verwijderd.');
    }
}
