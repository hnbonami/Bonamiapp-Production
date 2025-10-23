<?php
namespace App\Services;

use App\Models\Bikefit;
use App\Models\Klant;
use Barryvdh\DomPDF\Facade\Pdf;

class BikefitReportGenerator
{
    /**
     * Build a data array used by the report view (and PDF generator).
     * Keep calculations encapsulated here so we can unit test them later.
     *
     * @param Bikefit $bikefit
     * @return array
     */
    public function generate(Bikefit $bikefit): array
    {
        // ensure images are loaded
        $bikefit->loadMissing('images');

        $images = $bikefit->images->map(function ($img) {
            return [
                'path' => $img->path,
                'caption' => $img->caption,
                'is_cover' => $img->is_cover,
            ];
        })->toArray();

        // if none explicitly marked as cover, take first image as cover
        $cover = null;
        foreach ($images as $i) {
            if (!empty($i['is_cover'])) { $cover = $i; break; }
        }
        if (is_null($cover) && count($images) > 0) {
            $cover = $images[0];
        }

        return [
            'bikefit' => $bikefit,
            'klant' => $bikefit->klant ?? null,
            'images' => $images,
            'cover' => $cover,
            'calculations' => [
                'estimated_saddle_height_cm' => $this->estimateSaddleHeight($bikefit),
                'recommended_reach_mm' => $this->estimateReach($bikefit),
            ],
        ];
    }

    protected function estimateSaddleHeight(Bikefit $b): ?float
    {
        // Simple placeholder formula (to be tuned with your domain logic):
        // saddle height ~= binnenbeenlengte * 0.883 - 0.6
        if (empty($b->binnenbeenlengte_cm)) return null;
        return round($b->binnenbeenlengte_cm * 0.883 - 0.6, 1);
    }

    protected function estimateReach(Bikefit $b): ?int
    {
        // Placeholder: use body length to estimate reach in mm.
        if (empty($b->lengte_cm)) return null;
        return (int) round(($b->lengte_cm / 100) * 370); // arbitrary factor
    }

    /**
     * Render report HTML and save a PDF to storage, returning the relative path.
     *
     * @param Bikefit $bikefit
     * @return string Relative storage path (storage/app/...)
     */
    public function savePdf(Bikefit $bikefit, $filename = null)
    {
        \Log::info('BikefitReportGenerator::savePdf aangeroepen voor bikefit ID: ' . $bikefit->id);
        
        try {
            // Gebruik de nieuwe sjabloon-gebaseerde report generatie
            $sjablonenController = new \App\Http\Controllers\SjablonenController();
            
            // Zoek matching sjabloon
            $sjabloon = $sjablonenController->findMatchingTemplate($bikefit->testtype, 'bikefit');
            
            if (!$sjabloon) {
                \Log::warning('Geen sjabloon gevonden, gebruik fallback HTML');
                $html = '<html><body><h1>Bikefit Rapport</h1><p>Geen sjabloon beschikbaar voor: ' . $bikefit->testtype . '</p></body></html>';
            } else {
                // Genereer HTML via sjabloon systeem
                $sjabloon->load(['pages' => function($query) {
                    $query->orderBy('page_number', 'asc');
                }]);
                
                // Gebruik reflection om private method aan te roepen
                $reflection = new \ReflectionClass($sjablonenController);
                $method = $reflection->getMethod('generatePagesForBikefit');
                $method->setAccessible(true);
                $generatedPages = $method->invoke($sjablonenController, $sjabloon, $bikefit);
                
                // Render de view met generated pages
                $html = view('sjablonen.pdf-template', [
                    'template' => $sjabloon,
                    'klantModel' => $bikefit->klant,
                    'generatedPages' => $generatedPages
                ])->render();
            }
            
            \Log::info('[PDF DEBUG] HTML naar dompdf:', ['html' => $html]);
            if (empty(trim(strip_tags($html)))) {
                $html = '<html><body><div style="color:orange;font-size:24px;">[DEBUG: HTML was leeg!]</div></body></html>';
            }
            $pdf = \PDF::loadHTML($html)->setPaper('a4', 'portrait');
            $dir = 'reports/' . ($bikefit->klant_id ?? 'unknown');
            $filename = 'bikefit_' . $bikefit->id . '_report.pdf';
            $fullPath = $dir . '/' . $filename;

            // Tijdelijk PDF-bestand genereren
            $tmpPdfPath = sys_get_temp_dir() . '/bikefit_tmp_' . uniqid() . '.pdf';
            file_put_contents($tmpPdfPath, $pdf->output());

        // Debug: bereik FPDI-check
        \Log::info('FPDI-check bereikt, ga achtergrondbestand controleren.');
        $backgroundPath = public_path('backgrounds/background.pdf');
        if (file_exists($backgroundPath)) {
                \Log::info('Achtergrond PDF gevonden en wordt toegepast: ' . $backgroundPath);
                try {
                    require_once(base_path('vendor/setasign/fpdi-fpdf/src/autoload.php'));
                    $fpdi = new \setasign\Fpdi\Fpdi();
                    $pageCount = $fpdi->setSourceFile($backgroundPath);
                    $srcPdf = new \setasign\Fpdi\Fpdi();
                    $srcPageCount = $srcPdf->setSourceFile($tmpPdfPath);
                    for ($i = 1; $i <= $srcPageCount; $i++) {
                        $bgPage = $i <= $pageCount ? $i : $pageCount;
                        $templateId = $fpdi->importPage($bgPage);
                        $size = $fpdi->getTemplateSize($templateId);
                        $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $fpdi->useTemplate($templateId);
                        $srcTemplateId = $srcPdf->importPage($i);
                        $fpdi->useTemplate($srcTemplateId, 0, 0);
                    }
                    $finalPdf = $fpdi->Output('S');
                    \Storage::disk('public')->put($fullPath, $finalPdf);
                    @unlink($tmpPdfPath);
                    \Log::info('PDF met achtergrond succesvol opgeslagen: ' . $fullPath);
                } catch (\Throwable $e) {
                    \Log::error('Fout bij toepassen achtergrond PDF: ' . $e->getMessage());
                    // fallback: sla standaard PDF op
                    \Storage::disk('public')->put($fullPath, $pdf->output());
                    @unlink($tmpPdfPath);
                }
            } else {
                \Log::info('Geen achtergrond PDF gevonden, standaard PDF opgeslagen.');
                \Storage::disk('public')->put($fullPath, $pdf->output());
                @unlink($tmpPdfPath);
            }
            return $fullPath;
        } catch (\Exception $e) {
            \Log::error('Fout in savePdf: ' . $e->getMessage());
            throw $e;
        }
    }
}
