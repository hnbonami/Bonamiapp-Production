<?php

namespace App\Services;

use App\Models\Bikefit;
use App\Models\Klant;
use App\Services\BikefitCalculator;

class SjabloonService
{
    /**
     * Vervang placeholders met bestaande Blade partials
     */
    public function vervangSleutels(string $html, ?Bikefit $bikefit = null, ?Klant $klant = null, $inspanningstest = null): string
    {
        \Log::info('🚀 SjabloonService::vervangSleutels STARTED', [
            'has_bikefit' => $bikefit !== null,
            'has_klant' => $klant !== null,
            'content_length' => strlen($html),
            'contains_ResultatenVoor' => strpos($html, '$ResultatenVoor$') !== false,
            'contains_mobiliteitklant' => strpos($html, '$mobiliteitklant$') !== false
        ]);
        
        // Vervang klant gegevens
        if ($klant) {
            $html = str_replace('{{klant.naam}}', $klant->naam ?? '', $html);
            $html = str_replace('{{klant.voornaam}}', $klant->voornaam ?? '', $html);
            $html = str_replace('{{klant.email}}', $klant->email ?? '', $html);
        }
        
        // 🔥 GEBRUIK BESTAANDE BLADE PARTIALS VOOR BIKEFIT
        if ($bikefit) {
            try {
                $calculator = app(BikefitCalculator::class);
                $results = $calculator->calculate($bikefit);
                
                // 1️⃣ $ResultatenVoor$ → Simpele HTML tabel (werkt in PDF)
                if (strpos($html, '$ResultatenVoor$') !== false) {
                    $resultatenVoorHtml = $this->generateResultatenTabelHTML($bikefit, $results['voor'] ?? [], 'VOOR');
                    $html = str_replace('$ResultatenVoor$', $resultatenVoorHtml, $html);
                    \Log::info('✅ Replaced $ResultatenVoor$');
                }
                
                // 2️⃣ $ResultatenNa$ → Simpele HTML tabel (werkt in PDF)
                if (strpos($html, '$ResultatenNa$') !== false) {
                    $resultatenNaHtml = $this->generateResultatenTabelHTML($bikefit, $results['na'] ?? [], 'NA');
                    $html = str_replace('$ResultatenNa$', $resultatenNaHtml, $html);
                    \Log::info('✅ Replaced $ResultatenNa$');
                }
                
                // 3️⃣ $Bikefit.prognose_zitpositie_html$ → Simpele HTML tabel
                if (strpos($html, '$Bikefit.prognose_zitpositie_html$') !== false) {
                    $prognoseHtml = $this->generatePrognoseHTML($bikefit, $results['aanbevolen'] ?? []);
                    $html = str_replace('$Bikefit.prognose_zitpositie_html$', $prognoseHtml, $html);
                    \Log::info('✅ Replaced $Bikefit.prognose_zitpositie_html$');
                }
                
                // 4️⃣ $Bikefit.body_measurements_block_html$ → Lichaamsmaten met afbeelding
                if (strpos($html, '$Bikefit.body_measurements_block_html$') !== false) {
                    $lichaamsmatenHtml = $this->generateBodyMeasurementsHTML($bikefit);
                    $html = str_replace('$Bikefit.body_measurements_block_html$', $lichaamsmatenHtml, $html);
                    \Log::info('✅ Replaced $Bikefit.body_measurements_block_html$');
                }
                
                // 5️⃣ $mobiliteitklant$ → Via BikefitCalculator (werkt altijd!)
                if (strpos($html, '$mobiliteitklant$') !== false) {
                    $mobiliteitHtml = $calculator->renderMobilityTableHtml($bikefit);
                    $html = str_replace('$mobiliteitklant$', $mobiliteitHtml, $html);
                    \Log::info('✅ Replaced $mobiliteitklant$ with BikefitCalculator');
                }
                
                // 6️⃣ Fallback voor andere mobiliteit placeholders
                $mobilityHtml = $calculator->renderMobilityTableHtml($bikefit);
                $otherPlaceholders = ['$MobiliteitTabel$', '$mobility_table_report$'];
                foreach ($otherPlaceholders as $placeholder) {
                    if (strpos($html, $placeholder) !== false) {
                        $html = str_replace($placeholder, $mobilityHtml, $html);
                        \Log::info('✅ Replaced ' . $placeholder);
                    }
                }
                
            } catch (\Exception $e) {
                \Log::error('❌ Failed to generate HTML components: ' . $e->getMessage());
            }
        }
        
        // Inspanningstest placeholders (blijft zoals het was)
        if ($inspanningstest) {
            \Log::info('🏃 Processing inspanningstest placeholders');
            
            // Datum formatting
            $testdatum = '';
            if (isset($inspanningstest->testdatum)) {
                $testdatum = \Carbon\Carbon::parse($inspanningstest->testdatum)->format('d-m-Y');
            } elseif (isset($inspanningstest->datum)) {
                $testdatum = \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y');
            }
            
            // Basis velden
            $html = str_replace('{{inspanningstest.testdatum}}', $testdatum, $html);
            $html = str_replace('{{inspanningstest.datum}}', $testdatum, $html);
            $html = str_replace('{{inspanningstest.testtype}}', $inspanningstest->testtype ?? '', $html);
            $html = str_replace('{{inspanningstest.testlocatie}}', $inspanningstest->testlocatie ?? '', $html);
            $html = str_replace('{{inspanningstest.protocol}}', $inspanningstest->protocol ?? '', $html);
            $html = str_replace('{{inspanningstest.specifieke_doelstellingen}}', $inspanningstest->specifieke_doelstellingen ?? '', $html);
            $html = str_replace('{{inspanningstest.weersomstandigheden}}', $inspanningstest->weersomstandigheden ?? '', $html);
            $html = str_replace('{{inspanningstest.startwattage}}', $inspanningstest->startwattage ?? '', $html);
            $html = str_replace('{{inspanningstest.stappen_min}}', $inspanningstest->stappen_min ?? '', $html);
            $html = str_replace('{{inspanningstest.stappen_watt}}', $inspanningstest->stappen_watt ?? '', $html);
            $html = str_replace('{{inspanningstest.analyse_methode}}', $inspanningstest->analyse_methode ?? '', $html);
            
            // Lichaamsmetingen
            $html = str_replace('{{inspanningstest.lichaamslengte}}', $inspanningstest->lichaamslengte_cm ?? '', $html);
            $html = str_replace('{{inspanningstest.lichaamsgewicht}}', $inspanningstest->lichaamsgewicht_kg ?? '', $html);
            $html = str_replace('{{inspanningstest.bmi}}', $inspanningstest->bmi ?? '', $html);
            $html = str_replace('{{inspanningstest.hartslag_rust}}', $inspanningstest->hartslag_rust_bpm ?? '', $html);
            $html = str_replace('{{inspanningstest.hartslag_max}}', $inspanningstest->maximale_hartslag_bpm ?? '', $html);
            $html = str_replace('{{inspanningstest.vetpercentage}}', $inspanningstest->vetpercentage ?? '', $html);
            $html = str_replace('{{inspanningstest.buikomtrek}}', $inspanningstest->buikomtrek_cm ?? '', $html);
            
            // Drempelwaarden
            $html = str_replace('{{inspanningstest.aerobe_drempel_vermogen}}', $inspanningstest->aerobe_drempel_vermogen ?? '', $html);
            $html = str_replace('{{inspanningstest.aerobe_drempel_hartslag}}', $inspanningstest->aerobe_drempel_hartslag ?? '', $html);
            $html = str_replace('{{inspanningstest.anaerobe_drempel_vermogen}}', $inspanningstest->anaerobe_drempel_vermogen ?? '', $html);
            $html = str_replace('{{inspanningstest.anaerobe_drempel_hartslag}}', $inspanningstest->anaerobe_drempel_hartslag ?? '', $html);
            
            // Trainingstatus velden
            $html = str_replace('{{inspanningstest.slaapkwaliteit}}', $inspanningstest->slaapkwaliteit ?? '', $html);
            $html = str_replace('{{inspanningstest.eetlust}}', $inspanningstest->eetlust ?? '', $html);
            $html = str_replace('{{inspanningstest.gevoel_op_training}}', $inspanningstest->gevoel_op_training ?? '', $html);
            $html = str_replace('{{inspanningstest.stressniveau}}', $inspanningstest->stressniveau ?? '', $html);
            $html = str_replace('{{inspanningstest.gemiddelde_trainingstatus}}', $inspanningstest->gemiddelde_trainingstatus ?? '', $html);
            $html = str_replace('{{inspanningstest.training_dag_voor_test}}', $inspanningstest->training_dag_voor_test ?? '', $html);
            $html = str_replace('{{inspanningstest.training_2d_voor_test}}', $inspanningstest->training_2d_voor_test ?? '', $html);
            
            // Besluit/Advies velden
            $html = str_replace('{{inspanningstest.besluit_lichaamssamenstelling}}', $inspanningstest->besluit_lichaamssamenstelling ?? '', $html);
            $html = str_replace('{{inspanningstest.advies_aerobe_drempel}}', $inspanningstest->advies_aerobe_drempel ?? '', $html);
            $html = str_replace('{{inspanningstest.advies_anaerobe_drempel}}', $inspanningstest->advies_anaerobe_drempel ?? '', $html);
            
            // HTML Componenten via Blade partials
            try {
                // Decode JSON velden
                $testresultaten = is_string($inspanningstest->testresultaten) 
                    ? json_decode($inspanningstest->testresultaten, true) ?? []
                    : ($inspanningstest->testresultaten ?? []);
                
                $trainingszones = isset($inspanningstest->trainingszones_data)
                    ? (is_string($inspanningstest->trainingszones_data) ? json_decode($inspanningstest->trainingszones_data, true) : $inspanningstest->trainingszones_data)
                    : [];
                
                // Maak kopie voor partials
                $inspanningstestCopy = clone $inspanningstest;
                $inspanningstestCopy->testresultaten = $testresultaten;
                $inspanningstestCopy->trainingszones_data = $trainingszones;
                
                // Vervang ALLE HTML componenten uit commit b50924a
                
                // 1. Algemene info
                if (strpos($html, '{{INSPANNINGSTEST_ALGEMEEN}}') !== false) {
                    $partialHtml = view('inspanningstest.partials._algemene_info_report', [
                        'inspanningstest' => $inspanningstestCopy,
                        'klant' => $inspanningstest->klant
                    ])->render();
                    $html = str_replace('{{INSPANNINGSTEST_ALGEMEEN}}', $partialHtml, $html);
                }
                
                // 2. Trainingstatus
                if (strpos($html, '{{INSPANNINGSTEST_TRAININGSTATUS}}') !== false) {
                    $partialHtml = view('inspanningstest.partials._trainingstatus_report', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    $html = str_replace('{{INSPANNINGSTEST_TRAININGSTATUS}}', $partialHtml, $html);
                }
                
                // 3. Testresultaten
                if (strpos($html, '{{INSPANNINGSTEST_TESTRESULTATEN}}') !== false) {
                    $partialHtml = view('inspanningstest.partials._testresultaten', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    $html = str_replace('{{INSPANNINGSTEST_TESTRESULTATEN}}', $partialHtml, $html);
                }
                
                // 4. Grafiek analyse
                if (strpos($html, '{{INSPANNINGSTEST_GRAFIEK}}') !== false) {
                    $partialHtml = view('inspanningstest.partials._grafiek_analyse_report', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    $html = str_replace('{{INSPANNINGSTEST_GRAFIEK}}', $partialHtml, $html);
                }
                
                // 5. Trainingszones
                if (strpos($html, '{{INSPANNINGSTEST_TRAININGSZONES}}') !== false) {
                    $partialHtml = view('inspanningstest.partials._trainingszones_report', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    $html = str_replace('{{INSPANNINGSTEST_TRAININGSZONES}}', $partialHtml, $html);
                }
                
                // 5b. ZONES alias (backward compatibility)
                if (strpos($html, '{{INSPANNINGSTEST_ZONES}}') !== false) {
                    $partialHtml = view('inspanningstest.partials._trainingszones_report', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    $html = str_replace('{{INSPANNINGSTEST_ZONES}}', $partialHtml, $html);
                }
                
                // 6. Drempelwaarden overzicht
                if (strpos($html, '{{INSPANNINGSTEST_DREMPELS}}') !== false) {
                    $partialHtml = view('inspanningstest.partials._drempelwaarden_overzicht_report', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    $html = str_replace('{{INSPANNINGSTEST_DREMPELS}}', $partialHtml, $html);
                }
                
                // 7. AI Analyse - Gecombineerd (backward compatibility)
                if (strpos($html, '{{INSPANNINGSTEST_AI_ANALYSE}}') !== false) {
                    $aiDeel1Html = view('inspanningstest.partials._ai_analyse_report_deel1', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    
                    $aiDeel2Html = view('inspanningstest.partials._ai_analyse_report_deel2', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    
                    $combinedHtml = $aiDeel1Html . '<div style="page-break-before: always;"></div>' . $aiDeel2Html;
                    $html = str_replace('{{INSPANNINGSTEST_AI_ANALYSE}}', $combinedHtml, $html);
                }
                
                // 8. AI Analyse Deel 1
                if (strpos($html, '{{INSPANNINGSTEST_AI_ANALYSE_DEEL1}}') !== false) {
                    $partialHtml = view('inspanningstest.partials._ai_analyse_report_deel1', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    $html = str_replace('{{INSPANNINGSTEST_AI_ANALYSE_DEEL1}}', $partialHtml, $html);
                }
                
                // 9. AI Analyse Deel 2
                if (strpos($html, '{{INSPANNINGSTEST_AI_ANALYSE_DEEL2}}') !== false) {
                    $partialHtml = view('inspanningstest.partials._ai_analyse_report_deel2', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    $html = str_replace('{{INSPANNINGSTEST_AI_ANALYSE_DEEL2}}', $partialHtml, $html);
                }
                
                // 10. AI Analyse Deel 3
                if (strpos($html, '{{INSPANNINGSTEST_AI_ANALYSE_DEEL3}}') !== false) {
                    $partialHtml = view('inspanningstest.partials._ai_analyse_report_deel3', [
                        'inspanningstest' => $inspanningstestCopy
                    ])->render();
                    $html = str_replace('{{INSPANNINGSTEST_AI_ANALYSE_DEEL3}}', $partialHtml, $html);
                }
                
            } catch (\Exception $e) {
                \Log::error('❌ Failed to render inspanningstest partials: ' . $e->getMessage());
            }
        }

        \Log::info('✅ SjabloonService::vervangSleutels COMPLETED');
        return $html;
    }
    
    /**
     * Genereer Resultaten tabel HTML (VOOR of NA)
     */
    private function generateResultatenTabelHTML($bikefit, $results, $type = 'VOOR')
    {
        $bgColor = $type === 'VOOR' ? '#f8f9fa' : '#ecfdf5';
        $titleColor = $type === 'VOOR' ? '#2563eb' : '#10b981';
        $emoji = $type === 'VOOR' ? '📊' : '✅';
        
        $html = '<div style="padding: 20px; background: ' . $bgColor . '; border-radius: 8px; margin: 20px 0;">';
        $html .= '<h3 style="color: ' . $titleColor . '; margin-bottom: 15px;">' . $emoji . ' Zitpositie ' . $type . ' aanpassing</h3>';
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        
        $fields = [
            'zadelhoogte' => 'Zadelhoogte',
            'zadelterugstand' => 'Setback',
            'reach' => 'Reach',
            'drop' => 'Drop',
            'cranklengte' => 'Cranklengte'
        ];
        
        foreach ($fields as $key => $label) {
            if (isset($results[$key]) && !empty($results[$key])) {
                $value = $results[$key];
                $unit = in_array($key, ['zadelhoogte', 'zadelterugstand']) ? 'cm' : 'mm';
                $html .= '<tr><td style="padding: 10px; border-bottom: 1px solid #ddd; width: 50%;"><strong>' . $label . ':</strong></td>';
                $html .= '<td style="padding: 10px; border-bottom: 1px solid #ddd;">' . number_format($value, 1) . ' ' . $unit . '</td></tr>';
            }
        }
        
        $html .= '</table></div>';
        return $html;
    }
    
    /**
     * Genereer Prognose Zitpositie HTML
     */
    private function generatePrognoseHTML($bikefit, $results)
    {
        $html = '<div style="padding: 20px; background: #fef3c7; border-radius: 8px; margin: 20px 0;">';
        $html .= '<h3 style="color: #d97706; margin-bottom: 15px;">📐 Aanbevolen Zitpositie</h3>';
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        
        $fields = [
            'zadelhoogte' => 'Aanbevolen Zadelhoogte',
            'zadelterugstand' => 'Aanbevolen Setback',
            'reach' => 'Aanbevolen Reach',
            'cranklengte' => 'Aanbevolen Cranklengte'
        ];
        
        foreach ($fields as $key => $label) {
            if (isset($results[$key]) && !empty($results[$key])) {
                $value = $results[$key];
                $unit = in_array($key, ['zadelhoogte', 'zadelterugstand']) ? 'cm' : 'mm';
                $html .= '<tr><td style="padding: 10px; border-bottom: 1px solid #ddd; width: 60%;"><strong>' . $label . ':</strong></td>';
                $html .= '<td style="padding: 10px; border-bottom: 1px solid #ddd;">' . number_format($value, 1) . ' ' . $unit . '</td></tr>';
            }
        }
        
        $html .= '</table></div>';
        return $html;
    }
    
    /**
     * Genereer Lichaamsmaten HTML met afbeelding
     */
    private function generateBodyMeasurementsHTML($bikefit)
    {
        // Gebruik de afbeelding met overlay cijfers (zoals in _body_measurements.blade.php)
        $html = '<div style="max-width: 475px; margin: 20px 0; position: relative;">';
        $html .= '<img src="' . asset('images/body-blank.png') . '" alt="Lichaamsmaten" style="width: 100%; height: auto;">';
        
        // Overlay cijfers (posities uit _body_measurements.blade.php)
        $html .= '<span style="position:absolute;left:15%;top:46%;font-size:1.2em;color:#222;font-family:Tahoma,Arial,sans-serif;font-weight:600;">' . ($bikefit->lengte_cm ?? '-') . '</span>';
        $html .= '<span style="position:absolute;left:67%;top:13%;font-size:1.0em;color:#222;font-family:Tahoma,Arial,sans-serif;font-weight:600;">' . ($bikefit->schouderbreedte_cm ?? '-') . '</span>';
        $html .= '<span style="position:absolute;left:85%;top:29%;font-size:1.2em;color:#222;font-family:Tahoma,Arial,sans-serif;font-weight:600;">' . ($bikefit->romplengte_cm ?? '-') . '</span>';
        $html .= '<span style="position:absolute;left:86%;top:53%;font-size:0.9em;color:#222;font-family:Tahoma,Arial,sans-serif;font-weight:600;">' . ($bikefit->armlengte_cm ?? '-') . '</span>';
        $html .= '<span style="position:absolute;left:77%;top:65%;font-size:1.0em;color:#222;font-family:Tahoma,Arial,sans-serif;font-weight:600;">' . ($bikefit->binnenbeenlengte_cm ?? '-') . '</span>';
        $html .= '</div>';
        
        return $html;
    }
}

