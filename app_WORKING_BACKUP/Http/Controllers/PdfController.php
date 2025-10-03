<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klant;
use App\Models\Bikefit;
use Spatie\Browsershot\Browsershot;

class PdfController extends Controller
{
    // Maak een nieuwe previewmethode die de juiste HTML toont
    public function newPdfPreview(Klant $klant, Bikefit $bikefit)
    {
        return view('bikefit.generate-report', [
            'klant' => $klant,
            'bikefit' => $bikefit,
            'mobility_table' => $this->generateMobilityTable($klant, $bikefit),
            'body_measurements' => $this->generateBodyMeasurements($klant, $bikefit),
            'template_html' => $this->generateTemplateHtml($klant, $bikefit),
        ]);
    }

    // Eenvoudige print-versie van het rapport
    public function printVersion(Klant $klant, Bikefit $bikefit)
    {
        return view('bikefit.print-report', [
            'klant' => $klant,
            'bikefit' => $bikefit,
            'mobility_table' => $this->generateMobilityTable($klant, $bikefit),
            'body_measurements' => $this->generateBodyMeasurements($klant, $bikefit),
            'template_html' => $this->generateTemplateHtml($klant, $bikefit),
        ]);
    }
    
    // Fallback methode voor als Browsershot niet werkt
    private function generateHtmlFallback(Klant $klant, Bikefit $bikefit)
    {
        $htmlContent = view('bikefit.generate-report', [
            'klant' => $klant,
            'bikefit' => $bikefit,
            'mobility_table' => $this->generateMobilityTable($klant, $bikefit),
            'body_measurements' => $this->generateBodyMeasurements($klant, $bikefit),
            'template_html' => $this->generateTemplateHtml($klant, $bikefit),
            'is_pdf' => true
        ])->render();

        // Simpele HTML response voor debugging
        return response($htmlContent)
            ->header('Content-Type', 'text/html');
    }

    public function downloadPdf(Klant $klant, Bikefit $bikefit)
    {
        $pdfPath = storage_path('app/public/reports/' . $klant->id . '/bikefit_' . $bikefit->id . '_report.pdf');

        if (!file_exists($pdfPath)) {
            abort(404, 'PDF not found.');
        }

        return response()->download($pdfPath);
    }

    // Update de generateMobilityTable methode om voorbeeldinhoud te tonen
    private function generateMobilityTable(Klant $klant, Bikefit $bikefit)
    {
        return '<table><tr><th>Parameter</th><th>Waarde</th></tr><tr><td>Lengte</td><td>' . $klant->lengte_cm . ' cm</td></tr></table>';
    }

    // Update de generateBodyMeasurements methode om voorbeeldinhoud te tonen
    private function generateBodyMeasurements(Klant $klant, Bikefit $bikefit)
    {
        return '<div><p>Armlengte: ' . $klant->armlengte_cm . ' cm</p><p>Romplengte: ' . $klant->romplengte_cm . ' cm</p></div>';
    }

    // Update de generateTemplateHtml methode om voorbeeldinhoud te tonen
    private function generateTemplateHtml(Klant $klant, Bikefit $bikefit)
    {
        return '<div><h2>Bikefit Template</h2><p>Fietsmerk: ' . $bikefit->fietsmerk . '</p><p>Kadermaat: ' . $bikefit->kadermaat . '</p></div>';
    }

    public function showReportNoHeader(Klant $klant, Bikefit $bikefit)
    {
        return view('bikefit.report_no_header', [
            'klant' => $klant,
            'bikefit' => $bikefit,
            'mobility_table' => $this->generateMobilityTable($klant, $bikefit),
            'body_measurements' => $this->generateBodyMeasurements($klant, $bikefit),
            'template_html' => $this->generateTemplateHtml($klant, $bikefit),
        ]);
    }

    public function exportPdf(Klant $klant, Bikefit $bikefit)
    {
        try {
            // Genereer de HTML direct zonder URL te bezoeken
            $htmlContent = view('bikefit.generate-report', [
                'klant' => $klant,
                'bikefit' => $bikefit,
                'mobility_table' => $this->generateMobilityTable($klant, $bikefit),
                'body_measurements' => $this->generateBodyMeasurements($klant, $bikefit),
                'template_html' => $this->generateTemplateHtml($klant, $bikefit),
                'is_pdf' => true
            ])->render();

            // Voeg CSS toe voor PDF
            $css = '';
            if (file_exists(public_path('css/app.css'))) {
                $css = file_get_contents(public_path('css/app.css'));
            }

            $fullHtml = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>' . $css . '
                /* PDF-specifieke aanpassingen */
                .btn, button, .no-print, nav, .navbar { display: none !important; }
                body { font-family: Arial, sans-serif; }
                @page { margin: 15mm; }
                </style>
            </head>
            <body>' . $htmlContent . '</body>
            </html>';

            // Browsershot gebruikt de HTML direct
            $pdf = Browsershot::html($fullHtml)
                ->setOption('landscape', false)
                ->paperSize(210, 297) // A4 in mm
                ->margins(15, 15, 15, 15) // margins in mm
                ->showBackground() // Belangrijk voor achtergrondafbeeldingen
                ->waitUntilNetworkIdle()
                ->timeout(60)
                ->pdf();

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="bikefit_report_' . $klant->id . '_' . $bikefit->id . '.pdf"');

        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return response()->json(['error' => 'PDF kon niet worden geëxporteerd: ' . $e->getMessage()], 500);
        }
    }

    public function generateAndDownloadPdf(Klant $klant, Bikefit $bikefit)
    {
        $generator = new \App\Services\BikefitReportGenerator();
        $pdfPath = $generator->savePdf($bikefit);

        if (!\Storage::disk('public')->exists($pdfPath)) {
            abort(404, 'PDF not found.');
        }

        return response()->download(storage_path('app/public/' . $pdfPath));
    }

    // Print-alleen versie van het rapport (exact zoals report_preview maar zonder layout)
    public function printOnly(Klant $klant, Bikefit $bikefit)
    {
        try {
            // Probeer verschillende methodes van de BikefitReportGenerator
            $generator = new \App\Services\BikefitReportGenerator();
            
            // Probeer eerst savePdf() zoals gebruikt in BikefitController
            if (method_exists($generator, 'savePdf')) {
                $pdfPath = $generator->savePdf($bikefit);
                // Als dit werkt, redirect naar download van deze PDF
                return redirect()->route('bikefit.downloadPdf', ['klant' => $klant, 'bikefit' => $bikefit]);
            }
            
            // Probeer generate() methode
            if (method_exists($generator, 'generate')) {
                $data = $generator->generate($bikefit);
                return view('bikefit.print-only', array_merge([
                    'klant' => $klant,
                    'bikefit' => $bikefit,
                    'klantId' => $klant->id,
                ], $data));
            }
            
            // Als geen van beide werkt, gebruik fallback
            throw new \Exception('Geen bruikbare methode gevonden in BikefitReportGenerator');
            
        } catch (\Exception $e) {
            \Log::error('Print-only generation error: ' . $e->getMessage());
            
            // Fallback: eenvoudige weergave
            return view('bikefit.print-only', [
                'klant' => $klant,
                'bikefit' => $bikefit,
                'klantId' => $klant->id,
                'html' => '<div style="padding:40px; font-family: Arial, sans-serif;">
                    <h1>Bikefit Rapport</h1>
                    <p><strong>Klant:</strong> ' . $klant->naam . '</p>
                    <p><strong>Email:</strong> ' . $klant->email . '</p>
                    <p><strong>Datum:</strong> ' . $bikefit->datum->format('d-m-Y') . '</p>
                    <p><strong>Type:</strong> ' . $bikefit->testtype . '</p>
                    <p><strong>Fietsmerk:</strong> ' . ($bikefit->fietsmerk ?? 'Niet opgegeven') . '</p>
                    <p><strong>Type fitting:</strong> ' . ($bikefit->type_fitting ?? 'Niet opgegeven') . '</p>
                    ' . ($bikefit->opmerkingen ? '<p><strong>Opmerkingen:</strong> ' . nl2br(e($bikefit->opmerkingen)) . '</p>' : '') . '
                    <p><em>Volledige rapport data kon niet worden geladen.</em></p>
                </div>',
                'htmls' => null,
                'images' => [],
                'template' => null
            ]);
        }
    }
}