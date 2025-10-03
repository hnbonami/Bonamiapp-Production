<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Klant;
use App\Models\Bikefit;

class PdfController extends Controller
{
    public function generatePdf(Request $request)
    {
        $htmlContent = view('bikefit.generate-report', [
            'klant' => $request->klant,
            'bikefit' => $request->bikefit,
            'mobility_table' => $request->mobility_table,
            'body_measurements' => $request->body_measurements,
            'template_html' => $request->template_html,
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream('bikefit_report.pdf', ['Attachment' => false]);
    }

    // Herstel de showPdfPreview-methode om de juiste layout en inhoud te tonen
    public function showPdfPreview(Klant $klant, Bikefit $bikefit)
    {
        $mobility_table = $this->generateMobilityTable($klant, $bikefit); // Voeg hier de juiste logica toe
        $body_measurements = $this->generateBodyMeasurements($klant, $bikefit); // Voeg hier de juiste logica toe
        $template_html = $this->generateTemplateHtml($klant, $bikefit); // Voeg hier de juiste logica toe

        return view('bikefit.generate-report', [
            'klant' => $klant,
            'bikefit' => $bikefit,
            'mobility_table' => $mobility_table,
            'body_measurements' => $body_measurements,
            'template_html' => $template_html,
        ]);
    }

    public function downloadPdf(Klant $klant, Bikefit $bikefit)
    {
        $pdfPath = storage_path('app/public/reports/' . $klant->id . '/bikefit_' . $bikefit->id . '_report.pdf');

        if (!file_exists($pdfPath)) {
            abort(404, 'PDF not found.');
        }

        return response()->download($pdfPath);
    }

    // Voeg de ontbrekende methode toe om de mobility table te genereren
    private function generateMobilityTable(Klant $klant, Bikefit $bikefit)
    {
        // Voeg hier de logica toe om de mobility table te genereren
        return '<table><tr><td>Voorbeeld data</td></tr></table>'; // Placeholder
    }

    // Voeg de ontbrekende methode toe om de body measurements te genereren
    private function generateBodyMeasurements(Klant $klant, Bikefit $bikefit)
    {
        // Voeg hier de logica toe om de body measurements te genereren
        return '<div>Voorbeeld body measurements</div>'; // Placeholder
    }

    // Voeg de ontbrekende methode toe om de template HTML te genereren
    private function generateTemplateHtml(Klant $klant, Bikefit $bikefit)
    {
        // Voeg hier de logica toe om de template HTML te genereren
        return '<div>Voorbeeld template HTML</div>'; // Placeholder
    }
}