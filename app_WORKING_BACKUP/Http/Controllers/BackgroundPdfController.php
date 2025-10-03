<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackgroundPdfController extends Controller
{
    // API endpoint: converteer PDF naar PNG's en geef array terug
    public function convert(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20000',
        ]);
        $file = $request->file('pdf');
        $filename = 'background_' . time() . '.pdf';
        $file->move(public_path('backgrounds'), $filename);
        $pdfPath = public_path('backgrounds/' . $filename);
        $outputDir = public_path('backgrounds');
        $cmd = "python3 -c 'from pdf2image import convert_from_path; images = convert_from_path(\"$pdfPath\", dpi=200); [img.save(f\"$outputDir/background_page_{i+1}.png\", \"PNG\") for i, img in enumerate(images)]'";
        exec($cmd);
        $images = [];
        for ($i = 1; $i <= 20; $i++) {
            $imgPath = 'backgrounds/background_page_' . $i . '.png';
            if (file_exists(public_path($imgPath))) {
                $images[] = $imgPath;
            }
        }
        return response()->json(['images' => $images]);
    }
    // Upload een PDF als achtergrond voor rapporten
    public function upload(Request $request)
    {
        $request->validate([
            'background_pdf' => 'required|file|mimes:pdf|max:20000',
        ]);
        $file = $request->file('background_pdf');
        $targetPath = public_path('backgrounds/background.pdf');
        if (!file_exists(public_path('backgrounds'))){
            mkdir(public_path('backgrounds'), 0775, true);
        }
        $file->move(public_path('backgrounds'), 'background.pdf');
        return redirect()->back()->with('success', 'Achtergrond PDF succesvol geüpload!');
    }
}
