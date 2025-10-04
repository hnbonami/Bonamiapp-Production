<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sjabloon;
use App\Models\Klant;
use App\Models\Bikefit;
use App\Models\Inspanningstest;
use Barryvdh\DomPDF\Facade\Pdf;

class RapportController extends Controller
{
    public function selectTemplate(Request $request)
    {
        $type = $request->get('type'); // bikefit or inspanningstest
        $testtype = $request->get('testtype');
        
        $sjablonen = Sjabloon::where('categorie', $type)
            ->where('testtype', $testtype)
            ->get();
            
        return view('rapporten.select-template', compact('sjablonen', 'type', 'testtype'));
    }
    
    public function bikefitRapport(Bikefit $bikefit, Request $request)
    {
        $sjabloonId = $request->get('sjabloon_id');
        $sjabloon = Sjabloon::with('paginas')->findOrFail($sjabloonId);
        $klant = $bikefit->klant;
        
        // Generate rapport data
        $rapportData = $this->generateBikefitRapportData($klant, $bikefit, $sjabloon);
        
        return view('sjablonen.generated-report', compact('sjabloon', 'klant', 'bikefit', 'rapportData'));
    }
    
    public function bikefitRapportPdf(Bikefit $bikefit, Request $request)
    {
        $sjabloonId = $request->get('sjabloon_id');
        $sjabloon = Sjabloon::with('paginas')->findOrFail($sjabloonId);
        $klant = $bikefit->klant;
        
        // Generate rapport data
        $rapportData = $this->generateBikefitRapportData($klant, $bikefit, $sjabloon);
        
        $html = view('sjablonen.pdf-template', compact('sjabloon', 'klant', 'bikefit', 'rapportData'))->render();
        
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
        $filename = 'Rapport_' . $klant->naam . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    public function inspanningstestRapport(Inspanningstest $inspanningstest, Request $request)
    {
        $sjabloonId = $request->get('sjabloon_id');
        $sjabloon = Sjabloon::with('paginas')->findOrFail($sjabloonId);
        $klant = $inspanningstest->klant;
        
        // Generate rapport data
        $rapportData = $this->generateInspanningstestRapportData($klant, $inspanningstest, $sjabloon);
        
        return view('sjablonen.generated-report', compact('sjabloon', 'klant', 'inspanningstest', 'rapportData'));
    }
    
    public function inspanningstestRapportPdf(Inspanningstest $inspanningstest, Request $request)
    {
        $sjabloonId = $request->get('sjabloon_id');
        $sjabloon = Sjabloon::with('paginas')->findOrFail($sjabloonId);
        $klant = $inspanningstest->klant;
        
        // Generate rapport data
        $rapportData = $this->generateInspanningstestRapportData($klant, $inspanningstest, $sjabloon);
        
        $html = view('sjablonen.pdf-template', compact('sjabloon', 'klant', 'inspanningstest', 'rapportData'))->render();
        
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
        $filename = 'Rapport_' . $klant->naam . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    private function generateBikefitRapportData($klant, $bikefit, $sjabloon)
    {
        $replacements = [
            // Klant gegevens
            '{{klant.naam}}' => $klant->naam ?? '',
            '{{klant.voornaam}}' => $klant->voornaam ?? '',
            '{{klant.email}}' => $klant->email ?? '',
            '{{klant.telefoon}}' => $klant->telefoon ?? '',
            '{{klant.geboortedatum}}' => $klant->geboortedatum ? $klant->geboortedatum->format('d-m-Y') : '',
            '{{klant.sport}}' => $klant->sport ?? '',
            '{{klant.niveau}}' => $klant->niveau ?? '',
            '{{klant.lengte}}' => $klant->lengte ?? '',
            '{{klant.gewicht}}' => $klant->gewicht ?? '',
            
            // Bikefit gegevens
            '{{bikefit.datum}}' => $bikefit->datum ? $bikefit->datum->format('d-m-Y') : '',
            '{{bikefit.testtype}}' => $bikefit->testtype ?? '',
            '{{bikefit.lengte_cm}}' => $bikefit->lengte_cm ?? '',
            '{{bikefit.binnenbeenlengte_cm}}' => $bikefit->binnenbeenlengte_cm ?? '',
            '{{bikefit.armlengte_cm}}' => $bikefit->armlengte_cm ?? '',
            '{{bikefit.romplengte_cm}}' => $bikefit->romplengte_cm ?? '',
            '{{bikefit.schouderbreedte_cm}}' => $bikefit->schouderbreedte_cm ?? '',
            '{{bikefit.zadel_trapas_hoek}}' => $bikefit->zadel_trapas_hoek ?? '',
            '{{bikefit.zadel_trapas_afstand}}' => $bikefit->zadel_trapas_afstand ?? '',
            '{{bikefit.stuur_trapas_hoek}}' => $bikefit->stuur_trapas_hoek ?? '',
            '{{bikefit.stuur_trapas_afstand}}' => $bikefit->stuur_trapas_afstand ?? '',
            '{{bikefit.aanpassingen_zadel}}' => $bikefit->aanpassingen_zadel ?? '',
            '{{bikefit.aanpassingen_setback}}' => $bikefit->aanpassingen_setback ?? '',
            '{{bikefit.aanpassingen_reach}}' => $bikefit->aanpassingen_reach ?? '',
            '{{bikefit.aanpassingen_drop}}' => $bikefit->aanpassingen_drop ?? '',
            '{{bikefit.type_zadel}}' => $bikefit->type_zadel ?? '',
            '{{bikefit.zadeltil}}' => $bikefit->zadeltil ?? '',
            '{{bikefit.zadelbreedte}}' => $bikefit->zadelbreedte ?? '',
            '{{bikefit.fietsmerk}}' => $bikefit->fietsmerk ?? '',
            '{{bikefit.kadermaat}}' => $bikefit->kadermaat ?? '',
            '{{bikefit.frametype}}' => $bikefit->frametype ?? '',
            '{{bikefit.algemene_klachten}}' => $bikefit->algemene_klachten ?? '',
            '{{bikefit.opmerkingen}}' => $bikefit->opmerkingen ?? '',
        ];
        
        // Mobiliteit tabel genereren
        $mobilityHtml = $this->generateMobilityTable($bikefit);
        $replacements['$mobiliteit_tabel_html$'] = $mobilityHtml;
        
        return $replacements;
    }
    
    private function generateInspanningstestRapportData($klant, $inspanningstest, $sjabloon)
    {
        $replacements = [
            // Klant gegevens
            '{{klant.naam}}' => $klant->naam ?? '',
            '{{klant.voornaam}}' => $klant->voornaam ?? '',
            '{{klant.email}}' => $klant->email ?? '',
            '{{klant.telefoon}}' => $klant->telefoon ?? '',
            '{{klant.geboortedatum}}' => $klant->geboortedatum ? $klant->geboortedatum->format('d-m-Y') : '',
            '{{klant.sport}}' => $klant->sport ?? '',
            '{{klant.niveau}}' => $klant->niveau ?? '',
            '{{klant.lengte}}' => $klant->lengte ?? '',
            '{{klant.gewicht}}' => $klant->gewicht ?? '',
            
            // Inspanningstest gegevens
            '{{inspanningstest.datum}}' => $inspanningstest->datum ? $inspanningstest->datum->format('d-m-Y') : '',
            '{{inspanningstest.testtype}}' => $inspanningstest->testtype ?? '',
            '{{inspanningstest.sport}}' => $inspanningstest->sport ?? '',
            '{{inspanningstest.niveau}}' => $inspanningstest->niveau ?? '',
            '{{inspanningstest.leeftijd}}' => $inspanningstest->leeftijd ?? '',
            '{{inspanningstest.gewicht}}' => $inspanningstest->gewicht ?? '',
            '{{inspanningstest.lengte}}' => $inspanningstest->lengte ?? '',
            '{{inspanningstest.rustpols}}' => $inspanningstest->rustpols ?? '',
            '{{inspanningstest.maximale_pols}}' => $inspanningstest->maximale_pols ?? '',
            '{{inspanningstest.vo2_max}}' => $inspanningstest->vo2_max ?? '',
            '{{inspanningstest.opmerkingen}}' => $inspanningstest->opmerkingen ?? '',
        ];
        
        return $replacements;
    }
    
    private function generateMobilityTable($bikefit)
    {
        $mobilityFields = [
            'straight_leg_raise_links' => 'Straight Leg Raise Links',
            'straight_leg_raise_rechts' => 'Straight Leg Raise Rechts',
            'knieflexie_links' => 'Knieflexie Links',
            'knieflexie_rechts' => 'Knieflexie Rechts',
            'heup_endorotatie_links' => 'Heup Endorotatie Links',
            'heup_endorotatie_rechts' => 'Heup Endorotatie Rechts',
            'heup_exorotatie_links' => 'Heup Exorotatie Links',
            'heup_exorotatie_rechts' => 'Heup Exorotatie Rechts',
            'enkeldorsiflexie_links' => 'Enkeldorsiflexie Links',
            'enkeldorsiflexie_rechts' => 'Enkeldorsiflexie Rechts',
            'one_leg_squat_links' => 'One Leg Squat Links',
            'one_leg_squat_rechts' => 'One Leg Squat Rechts',
        ];
        
        $html = '<table class="mobility-table" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr><th style="border: 1px solid #ddd; padding: 8px;">Test</th><th style="border: 1px solid #ddd; padding: 8px;">Resultaat</th></tr></thead>';
        $html .= '<tbody>';
        
        foreach ($mobilityFields as $field => $label) {
            $value = $bikefit->$field ?? '_';
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $label . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $value . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        
        return $html;
    }
}