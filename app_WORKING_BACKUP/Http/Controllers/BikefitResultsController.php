<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bikefit;
use App\Services\BikefitCalculator;
use PDF; // Voeg deze regel toe voor de PDF facade

class BikefitResultsController extends Controller
{
    // Printvriendelijke rapportweergave (zonder knoppen/layout)
    public function printReport($klantId, $bikefitId)
    {
        $bikefit = \App\Models\Bikefit::findOrFail($bikefitId);
        $klant = \App\Models\Klant::findOrFail($klantId);
        $results = (new \App\Services\BikefitCalculator())->calculate($bikefit);
        $bikefitVoor = clone $bikefit;
        $bikefitVoor->context = 'voor';
        $bikefitNa = clone $bikefit;
        $bikefitNa->context = 'na';
        $resultsNa = (new \App\Services\BikefitCalculator())->calculate($bikefitNa);
        $resultsVoor = (new \App\Services\BikefitCalculator())->calculate($bikefitVoor, $resultsNa);

        // Render Blade partials naar HTML
        $resultatenVoorHtml = view('bikefit._results_section', ['results' => $resultsVoor, 'bikefit' => $bikefitVoor])->render();
        $resultatenNaHtml = view('bikefit._results_section', ['results' => $resultsNa, 'bikefit' => $bikefitNa])->render();
        $mobiliteitHtml = view('bikefit._mobility_results', ['bikefit' => $bikefitNa])->render();

        // Zoek het gekoppelde sjabloon
        $template = null;
        $usedType = null;
        $allTypes = \App\Models\Template::pluck('type')->toArray();
        if (!empty($bikefit->template_kind)) {
            $template = \App\Models\Template::where('type', $bikefit->template_kind)->first();
            $usedType = $bikefit->template_kind;
        }
        if (!$template && !empty($bikefit->testtype)) {
            $template = \App\Models\Template::where('type', $bikefit->testtype)->first();
            $usedType = $bikefit->testtype;
        }
        $htmls = [];
        $images = [];
        if ($template && $template->html_contents) {
            $contents = json_decode($template->html_contents, true);
            if ($template->background_images) {
                $images = json_decode($template->background_images, true);
            }
            foreach ($contents as $i => $html) {
                foreach ($klant->getAttributes() as $key => $value) {
                    $html = str_replace('$Klant.' . $key . '$', e($value), $html);
                }
                foreach ($bikefit->getAttributes() as $key => $value) {
                    $html = str_replace('$Bikefit.' . $key . '$', e($value), $html);
                }
                foreach ($results as $key => $value) {
                    $html = str_replace('$Bikefit.' . $key . '$', e($value), $html);
                }
                // Voeg handmatige ja/neen sleutel toe
                $html = str_replace('$Bikefit.aanpassing_stuurpen_aan_ja_nee$', $bikefit->aanpassingen_stuurpen_aan == 1 ? 'ja' : 'neen', $html);
                $html = str_replace('$ResultatenVoor$', $resultatenVoorHtml, $html);
                $html = str_replace('$ResultatenNa$', $resultatenNaHtml, $html);
                $html = str_replace('$MobiliteitTabel$', $mobiliteitHtml, $html);
                $bodyMeasurementsHtml = view('bikefit._body_measurements', ['bikefit' => $bikefit])->render();
                $html = str_replace('$Bikefit.body_measurements_block_html$', $bodyMeasurementsHtml, $html);
                $prognoseZitpositieHtml = view('bikefit._prognose_zitpositie', [
                    'bikefit' => $bikefit,
                    'results' => $results
                ])->render();
                $html = str_replace('$Bikefit.prognose_zitpositie_html$', $prognoseZitpositieHtml, $html);
                $htmls[$i] = $html;
            }
        }
        return view('bikefit.report_print_friendly', compact('bikefit', 'results', 'klantId', 'htmls', 'template', 'images', 'usedType', 'allTypes'));
    }
    // Toon het rapport via een signed route (zonder login, tijdelijk geldig)
    public function reportPreviewSigned($klantId, $bikefitId)
    {
    $klant = \App\Models\Klant::findOrFail($klantId);
    $bikefit = \App\Models\Bikefit::findOrFail($bikefitId);
    // Voeg hier alle data toe die het rapport nodig heeft
    $results = (new \App\Services\BikefitCalculator())->calculate($bikefit);
    $bikefitVoor = clone $bikefit;
    $bikefitVoor->context = 'voor';
    $bikefitNa = clone $bikefit;
    $bikefitNa->context = 'na';
    $resultsNa = (new \App\Services\BikefitCalculator())->calculate($bikefitNa);
    $resultsVoor = (new \App\Services\BikefitCalculator())->calculate($bikefitVoor, $resultsNa);

    $calculations = $results;
    return view('bikefit.report', compact('klant', 'bikefit', 'results', 'resultsVoor', 'resultsNa', 'klantId', 'bikefitVoor', 'bikefitNa', 'calculations'));
    }
    /**
     * Genereer een pixel-perfecte PDF van de preview-pagina via Browsershot/Puppeteer.
     */
    public function downloadPdfPreview($klantId, $bikefitId)
    {
    // Altijd een absolute URL genereren voor Browsershot
    // Genereer een tijdelijke signed URL voor het rapport (5 minuten geldig)
    $fullUrl = url()->signedRoute('bikefit.reportPreview.signed', ['klant' => $klantId, 'bikefit' => $bikefitId], now()->addMinutes(5));
    $filename = 'bikefit_rapport_'.$klantId.'_'.$bikefitId.'_preview.pdf';

        try {
                // Haal de naam en waarde van de sessie-cookie op
                $cookieHeader = request()->header('cookie');

                $pdf = \Spatie\Browsershot\Browsershot::url($fullUrl)
                    ->setNodeBinary('/usr/local/bin/node') // pas aan indien nodig
                    ->setNpmBinary('/usr/local/bin/npm')   // pas aan indien nodig
                    ->userAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
                    ->format('A4')
                    ->margins(10, 10, 10, 10)
                    ->waitUntilNetworkIdle()
                    ->setOption('headers', [
                        'Cookie' => $cookieHeader,
                    ])
                    ->pdf();
        } catch (\Exception $e) {
            return response('PDF genereren via Browsershot/Puppeteer is mislukt: ' . $e->getMessage(), 500);
        }

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
    public function show($klantId, $bikefitId)
    {
        $bikefit = \App\Models\Bikefit::findOrFail($bikefitId);
        $klant = \App\Models\Klant::findOrFail($klantId);
        $results = (new \App\Services\BikefitCalculator())->calculate($bikefit);
        $bikefitVoor = clone $bikefit;
        $bikefitVoor->context = 'voor';
        $bikefitNa = clone $bikefit;
        $bikefitNa->context = 'na';
        $resultsNa = (new \App\Services\BikefitCalculator())->calculate($bikefitNa);
        $resultsVoor = (new \App\Services\BikefitCalculator())->calculate($bikefitVoor, $resultsNa);

        return view('bikefit.results', compact('bikefit', 'results', 'resultsVoor', 'resultsNa', 'klantId', 'bikefitVoor', 'bikefitNa'));
    }
    public function downloadPdf($klantId, $bikefitId)
    {
        // Zelfde logica als generateReport zodat preview en PDF identiek zijn
        $bikefit = \App\Models\Bikefit::findOrFail($bikefitId);
        $klant = \App\Models\Klant::findOrFail($klantId);
        $results = (new \App\Services\BikefitCalculator())->calculate($bikefit);
        $bikefitVoor = clone $bikefit;
        $bikefitVoor->context = 'voor';
        $bikefitNa = clone $bikefit;
        $bikefitNa->context = 'na';
        $resultsNa = (new \App\Services\BikefitCalculator())->calculate($bikefitNa);
        $resultsVoor = (new \App\Services\BikefitCalculator())->calculate($bikefitVoor, $resultsNa);

        $resultatenVoorHtml = view('bikefit._results_section', ['results' => $resultsVoor, 'bikefit' => $bikefitVoor])->render();
        $resultatenNaHtml = view('bikefit._results_section', ['results' => $resultsNa, 'bikefit' => $bikefitNa])->render();
        $mobiliteitHtml = view('bikefit._mobility_results', ['bikefit' => $bikefitNa])->render();

        $template = null;
        $usedType = null;
        $allTypes = \App\Models\Template::pluck('type')->toArray();
        if (!empty($bikefit->template_kind)) {
            $template = \App\Models\Template::where('type', $bikefit->template_kind)->first();
            $usedType = $bikefit->template_kind;
        }
        if (!$template && !empty($bikefit->testtype)) {
            $template = \App\Models\Template::where('type', $bikefit->testtype)->first();
            $usedType = $bikefit->testtype;
        }
        if ($template) {
            \Log::info('Gevonden sjabloon voor type: ' . $usedType . ' (template id: ' . $template->id . ')');
        } else {
            \Log::warning('Geen sjabloon gevonden voor template_kind: ' . ($bikefit->template_kind ?? '[leeg]') . ' of testtype: ' . ($bikefit->testtype ?? '[leeg]'));
        }
        $htmls = [];
        $images = [];
        if ($template && $template->html_contents) {
            $contents = json_decode($template->html_contents, true);
            if ($template->background_images) {
                $images = json_decode($template->background_images, true);
            }
            foreach ($contents as $i => $html) {
                foreach ($klant->getAttributes() as $key => $value) {
                    $html = str_replace('$Klant.' . $key . '$', e($value), $html);
                }
                foreach ($bikefit->getAttributes() as $key => $value) {
                    $html = str_replace('$Bikefit.' . $key . '$', e($value), $html);
                }
                foreach ($results as $key => $value) {
                    $html = str_replace('$Bikefit.' . $key . '$', e($value), $html);
                }
                // Voeg handmatige ja/neen sleutel toe
                $html = str_replace('$Bikefit.aanpassing_stuurpen_aan_ja_nee$', $bikefit->aanpassingen_stuurpen_aan == 1 ? 'ja' : 'neen', $html);
                $html = str_replace('$ResultatenVoor$', $resultatenVoorHtml, $html);
                $html = str_replace('$ResultatenNa$', $resultatenNaHtml, $html);
                $html = str_replace('$MobiliteitTabel$', $mobiliteitHtml, $html);
                $bodyMeasurementsHtml = view('bikefit._body_measurements', ['bikefit' => $bikefit])->render();
                $html = str_replace('$Bikefit.body_measurements_block_html$', $bodyMeasurementsHtml, $html);
                $prognoseZitpositieHtml = view('bikefit._prognose_zitpositie', [
                    'bikefit' => $bikefit,
                    'results' => $results
                ])->render();
                $html = str_replace('$Bikefit.prognose_zitpositie_html$', $prognoseZitpositieHtml, $html);
                $htmls[$i] = $html;
            }
        }
        $calculations = (new \App\Services\BikefitReportGenerator())->generate($bikefit)['calculations'] ?? [];
        $backgroundStyle = '';
        $cover = null;
        if (!empty($images) && isset($images[0])) {
            $cover = $images[0];
        }
        // Test: overschrijf $htmls[0] met Dompdf-compatibele HTML
        $htmls[0] = '<div style="padding:20px; border:1px solid #333; margin-bottom:20px;">
            <h2 style="font-size:20pt; color:#222;">Bikefit Rapport (PDF test)</h2>
            <p><strong>Klant:</strong> '.e($klant->voornaam ?? ":").' '.e($klant->naam ?? ":").'</p>
            <p><strong>Bikefit ID:</strong> '.e($bikefit->id ?? "").'</p>
            <table style="width:100%; border-collapse:collapse;">
                <tr><th style="border:1px solid #ccc; padding:4px;">Label</th><th style="border:1px solid #ccc; padding:4px;">Waarde</th></tr>
                <tr><td style="border:1px solid #ccc; padding:4px;">Lengte</td><td style="border:1px solid #ccc; padding:4px;">'.e($bikefit->lengte_cm ?? "-").' cm</td></tr>
                <tr><td style="border:1px solid #ccc; padding:4px;">Binnenbeen</td><td style="border:1px solid #ccc; padding:4px;">'.e($bikefit->binnenbeenlengte_cm ?? "-").' cm</td></tr>
            </table>
        </div>';
        return \PDF::loadView('bikefit.report', [
            'bikefit' => $bikefit,
            'klant' => $klant,
            'results' => $results,
            'calculations' => $calculations,
            'backgroundStyle' => $backgroundStyle,
            'htmls' => $htmls,
            'images' => $images,
            'cover' => $cover,
            'pdf' => true,
        ])->setPaper('a4')->download('bikefit_rapport_'.$klant->id.'_'.$bikefit->id.'.pdf');
    }

    public function generateReport(Request $request, $klantId, $bikefitId)
    {
        $bikefit = \App\Models\Bikefit::findOrFail($bikefitId);
        $klant = \App\Models\Klant::findOrFail($klantId);
        $results = (new \App\Services\BikefitCalculator())->calculate($bikefit);
        // Voor/na berekeningen
        $bikefitVoor = clone $bikefit;
        $bikefitVoor->context = 'voor';
        $bikefitNa = clone $bikefit;
        $bikefitNa->context = 'na';
        $resultsNa = (new \App\Services\BikefitCalculator())->calculate($bikefitNa);
        $resultsVoor = (new \App\Services\BikefitCalculator())->calculate($bikefitVoor, $resultsNa);

        // Render Blade partials naar HTML
        $resultatenVoorHtml = view('bikefit._results_section', ['results' => $resultsVoor, 'bikefit' => $bikefitVoor])->render();
        $resultatenNaHtml = view('bikefit._results_section', ['results' => $resultsNa, 'bikefit' => $bikefitNa])->render();
        $mobiliteitHtml = view('bikefit._mobility_results', ['bikefit' => $bikefitNa])->render();
        // Mobiliteitklant data en HTML
        $mobiliteitklantData = [
            'slr_links' => $bikefit->straight_leg_raise_links ?? '',
            'slr_rechts' => $bikefit->straight_leg_raise_rechts ?? '',
            'knieflexie_links' => $bikefit->knieflexie_links ?? '',
            'knieflexie_rechts' => $bikefit->knieflexie_rechts ?? '',
            'heup_endorotatie_links' => $bikefit->heup_endorotatie_links ?? '',
            'heup_endorotatie_rechts' => $bikefit->heup_endorotatie_rechts ?? '',
            'heup_exorotatie_links' => $bikefit->heup_exorotatie_links ?? '',
            'heup_exorotatie_rechts' => $bikefit->heup_exorotatie_rechts ?? '',
            'enkeldorsiflexie_links' => $bikefit->enkeldorsiflexie_links ?? '',
            'enkeldorsiflexie_rechts' => $bikefit->enkeldorsiflexie_rechts ?? '',
            'one_leg_squat_links' => $bikefit->one_leg_squat_links ?? '',
            'one_leg_squat_rechts' => $bikefit->one_leg_squat_rechts ?? '',
        ];
        $mobiliteitklantHtml = view('bikefit._mobility_table_report', ['mobiliteitklant' => $mobiliteitklantData])->render();

        // Zoek het gekoppelde sjabloon
        $template = null;
        $usedType = null;
        $allTypes = \App\Models\Template::pluck('type')->toArray();
        if (!empty($bikefit->template_kind)) {
            $template = \App\Models\Template::where('type', $bikefit->template_kind)->first();
            $usedType = $bikefit->template_kind;
        }
        if (!$template && !empty($bikefit->testtype)) {
            $template = \App\Models\Template::where('type', $bikefit->testtype)->first();
            $usedType = $bikefit->testtype;
        }
        if ($template) {
            \Log::info('Gevonden sjabloon voor type: ' . $usedType . ' (template id: ' . $template->id . ')');
        } else {
            \Log::warning('Geen sjabloon gevonden voor template_kind: ' . ($bikefit->template_kind ?? '[leeg]') . ' of testtype: ' . ($bikefit->testtype ?? '[leeg]'));
        }
        // Toon alle sjabloonpagina's en achtergronden
        $htmls = [];
        $images = [];
        if ($template && $template->html_contents) {
            $contents = json_decode($template->html_contents, true);
            if ($template->background_images) {
                $images = json_decode($template->background_images, true);
            } elseif (!empty($template->background_url)) {
                $images = [ [ 'path' => $template->background_url ] ];
            } elseif (!empty($template->background)) {
                $images = [ [ 'path' => $template->background ] ];
            } else {
                $images = [];
            }
            // Fallback: als images leeg is, vul met lege placeholders zodat er geen undefined key ontstaat
            $pageCount = is_array($contents) ? count($contents) : 1;
            if (count($images) === 0) {
                for ($i = 0; $i < $pageCount; $i++) {
                    $images[$i] = [ 'path' => null ];
                }
            } elseif (count($images) < $pageCount) {
                for ($i = count($images); $i < $pageCount; $i++) {
                    $images[$i] = $images[$i-1];
                }
            }
            foreach ($contents as $i => $html) {
                foreach ($klant->getAttributes() as $key => $value) {
                    $html = str_replace('$Klant.' . $key . '$', e($value), $html);
                }
                foreach ($bikefit->getAttributes() as $key => $value) {
                    $html = str_replace('$Bikefit.' . $key . '$', e($value), $html);
                }
                foreach ($results as $key => $value) {
                    $html = str_replace('$Bikefit.' . $key . '$', e($value), $html);
                }
                // Voeg handmatige ja/neen sleutel toe
                $html = str_replace('$Bikefit.aanpassing_stuurpen_aan_ja_nee$', $bikefit->aanpassingen_stuurpen_aan == 1 ? 'ja' : 'neen', $html);
                $html = str_replace('$ResultatenVoor$', $resultatenVoorHtml, $html);
                $html = str_replace('$ResultatenNa$', $resultatenNaHtml, $html);
                $html = str_replace('$MobiliteitTabel$', $mobiliteitHtml, $html);
                $html = str_replace('$mobiliteitklant$', $mobiliteitklantHtml, $html);
                $bodyMeasurementsHtml = view('bikefit._body_measurements', ['bikefit' => $bikefit])->render();
                $html = str_replace('$Bikefit.body_measurements_block_html$', $bodyMeasurementsHtml, $html);
                $prognoseZitpositieHtml = view('bikefit._prognose_zitpositie', [
                    'bikefit' => $bikefit,
                    'results' => $results
                ])->render();
                $html = str_replace('$Bikefit.prognose_zitpositie_html$', $prognoseZitpositieHtml, $html);
                $htmls[$i] = $html;
            }
        }
        return view('bikefit.report_preview', compact('bikefit', 'results', 'klantId', 'htmls', 'template', 'images', 'usedType', 'allTypes'));
    }

    // Perfect print methode - toont rapport zonder Laravel layout
    public function printPerfect(\App\Models\Klant $klant, \App\Models\Bikefit $bikefit)
    {
        // Verificeer dat de bikefit bij de klant hoort
        if ($bikefit->klant_id !== $klant->id) {
            abort(404);
        }
        
        // Gebruik dezelfde logica als generateReport
        $results = (new \App\Services\BikefitCalculator())->calculate($bikefit);
        
        // Voor/na berekeningen
        $bikefitVoor = clone $bikefit;
        $bikefitVoor->context = 'voor';
        $bikefitNa = clone $bikefit;
        $bikefitNa->context = 'na';
        $resultsNa = (new \App\Services\BikefitCalculator())->calculate($bikefitNa);
        $resultsVoor = (new \App\Services\BikefitCalculator())->calculate($bikefitVoor, $resultsNa);

        // Render Blade partials naar HTML
        $resultatenVoorHtml = view('bikefit._results_section', ['results' => $resultsVoor, 'bikefit' => $bikefitVoor])->render();
        $resultatenNaHtml = view('bikefit._results_section', ['results' => $resultsNa, 'bikefit' => $bikefitNa])->render();
        $mobiliteitHtml = view('bikefit._mobility_results', ['bikefit' => $bikefitNa])->render();
        
        // Mobiliteitklant data en HTML
        $mobiliteitklantData = [
            'slr_links' => $bikefit->straight_leg_raise_links ?? '',
            'slr_rechts' => $bikefit->straight_leg_raise_rechts ?? '',
            'knieflexie_links' => $bikefit->knieflexie_links ?? '',
            'knieflexie_rechts' => $bikefit->knieflexie_rechts ?? '',
            'heup_endorotatie_links' => $bikefit->heup_endorotatie_links ?? '',
            'heup_endorotatie_rechts' => $bikefit->heup_endorotatie_rechts ?? '',
            'heup_exorotatie_links' => $bikefit->heup_exorotatie_links ?? '',
            'heup_exorotatie_rechts' => $bikefit->heup_exorotatie_rechts ?? '',
            'enkeldorsiflexie_links' => $bikefit->enkeldorsiflexie_links ?? '',
            'enkeldorsiflexie_rechts' => $bikefit->enkeldorsiflexie_rechts ?? '',
            'one_leg_squat_links' => $bikefit->one_leg_squat_links ?? '',
            'one_leg_squat_rechts' => $bikefit->one_leg_squat_rechts ?? '',
        ];
        $mobiliteitklantHtml = view('bikefit._mobility_table_report', ['mobiliteitklant' => $mobiliteitklantData])->render();

        // Zoek het gekoppelde sjabloon
        $template = null;
        $usedType = null;
        $allTypes = \App\Models\Template::pluck('type')->toArray();
        if (!empty($bikefit->template_kind)) {
            $template = \App\Models\Template::where('type', $bikefit->template_kind)->first();
            $usedType = $bikefit->template_kind;
        }
        if (!$template && !empty($bikefit->testtype)) {
            $template = \App\Models\Template::where('type', $bikefit->testtype)->first();
            $usedType = $bikefit->testtype;
        }
        
        // Toon alle sjabloonpagina's en achtergronden
        $htmls = [];
        $images = [];
        if ($template && $template->html_contents) {
            $contents = json_decode($template->html_contents, true);
            if ($template->background_images) {
                $images = json_decode($template->background_images, true);
            } elseif (!empty($template->background_url)) {
                $images = [ [ 'path' => $template->background_url ] ];
            } elseif (!empty($template->background)) {
                $images = [ [ 'path' => $template->background ] ];
            } else {
                $images = [];
            }
            
            // Fallback: als images leeg is, vul met lege placeholders
            $pageCount = is_array($contents) ? count($contents) : 1;
            if (count($images) === 0) {
                for ($i = 0; $i < $pageCount; $i++) {
                    $images[$i] = [ 'path' => null ];
                }
            } elseif (count($images) < $pageCount) {
                for ($i = count($images); $i < $pageCount; $i++) {
                    $images[$i] = $images[$i-1];
                }
            }
            
            foreach ($contents as $i => $html) {
                foreach ($klant->getAttributes() as $key => $value) {
                    $html = str_replace('$Klant.' . $key . '$', e($value), $html);
                }
                foreach ($bikefit->getAttributes() as $key => $value) {
                    $html = str_replace('$Bikefit.' . $key . '$', e($value), $html);
                }
                foreach ($results as $key => $value) {
                    $html = str_replace('$Bikefit.' . $key . '$', e($value), $html);
                }
                // Voeg handmatige ja/neen sleutel toe
                $html = str_replace('$Bikefit.aanpassing_stuurpen_aan_ja_nee$', $bikefit->aanpassingen_stuurpen_aan == 1 ? 'ja' : 'neen', $html);
                $html = str_replace('$ResultatenVoor$', $resultatenVoorHtml, $html);
                $html = str_replace('$ResultatenNa$', $resultatenNaHtml, $html);
                $html = str_replace('$MobiliteitTabel$', $mobiliteitHtml, $html);
                $html = str_replace('$mobiliteitklant$', $mobiliteitklantHtml, $html);
                $bodyMeasurementsHtml = view('bikefit._body_measurements', ['bikefit' => $bikefit])->render();
                $html = str_replace('$Bikefit.body_measurements_block_html$', $bodyMeasurementsHtml, $html);
                $prognoseZitpositieHtml = view('bikefit._prognose_zitpositie', [
                    'bikefit' => $bikefit,
                    'results' => $results
                ])->render();
                $html = str_replace('$Bikefit.prognose_zitpositie_html$', $prognoseZitpositieHtml, $html);
                $htmls[$i] = $html;
            }
        }
        
        return view('bikefit.print-perfect', compact('klant', 'bikefit', 'results', 'htmls', 'template', 'images', 'usedType', 'allTypes'));
    }

    
}
