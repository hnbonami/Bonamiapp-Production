<?php

namespace App\Services;

use App\Models\Klant;
use App\Models\Bikefit;
use App\Models\Inspanningstest;
use App\Models\Sjabloon;

class RapportGeneratieService
{
    public function generateBikefitRapport(Bikefit $bikefit, Sjabloon $sjabloon = null)
    {
        $klant = $bikefit->klant;
        
        // Als geen sjabloon meegegeven, zoek het juiste sjabloon op basis van testtype
        if (!$sjabloon) {
            $sjabloon = $this->findMatchingTemplate('bikefit', $bikefit->testtype);
            if (!$sjabloon) {
                throw new \Exception('Geen geschikt sjabloon gevonden voor dit bikefit type.');
            }
        }

        $data = $this->prepareBikefitData($klant, $bikefit);
        
        return $this->processTemplate($sjabloon, $data);
    }

    public function generateInspanningstestRapport(Inspanningstest $test, Sjabloon $sjabloon = null)
    {
        $klant = $test->klant;
        
        if (!$sjabloon) {
            $sjabloon = $this->findMatchingTemplate('inspanningstest', $test->testtype);
            if (!$sjabloon) {
                throw new \Exception('Geen geschikt sjabloon gevonden voor dit test type.');
            }
        }

        $data = $this->prepareInspanningstestData($klant, $test);
        
        return $this->processTemplate($sjabloon, $data);
    }

    private function findMatchingTemplate(string $categorie, ?string $testtype = null)
    {
        $query = Sjabloon::where('categorie', $categorie)
                         ->where('is_actief', true);

        if ($testtype) {
            // Probeer eerst exact match op testtype
            $exact = (clone $query)->where('testtype', $testtype)->first();
            if ($exact) return $exact;
        }

        // Als geen exact match, neem de eerste algemene van die categorie
        return $query->whereNull('testtype')->first() 
               ?? $query->first();
    }

    private function prepareBikefitData(Klant $klant, Bikefit $bikefit)
    {
        $data = [
            'klant' => [
                'naam' => $klant->naam,
                'voornaam' => $klant->voornaam,
                'email' => $klant->email,
                'telefoon' => $klant->telefoon,
                'geboortedatum' => $klant->geboortedatum ? $klant->geboortedatum->format('d-m-Y') : '',
                'leeftijd' => $klant->geboortedatum ? $klant->geboortedatum->age : '',
                'sport' => $klant->sport,
                'niveau' => $klant->niveau,
            ],
            'bikefit' => [
                'datum' => $bikefit->created_at->format('d-m-Y'),
                'testtype' => $bikefit->testtype,
                'lengte_cm' => $bikefit->lengte_cm,
                'binnenbeenlengte_cm' => $bikefit->binnenbeenlengte_cm,
                'armlengte_cm' => $bikefit->armlengte_cm,
                'romplengte_cm' => $bikefit->romplengte_cm,
                'schouderbreedte_cm' => $bikefit->schouderbreedte_cm,
                'zadel_trapas_hoek' => $bikefit->zadel_trapas_hoek,
                'zadel_trapas_afstand' => $bikefit->zadel_trapas_afstand,
                'stuur_trapas_hoek' => $bikefit->stuur_trapas_hoek,
                'stuur_trapas_afstand' => $bikefit->stuur_trapas_afstand,
                'fietsmerk' => $bikefit->fietsmerk,
                'kadermaat' => $bikefit->kadermaat,
                'frametype' => $bikefit->frametype,
                'type_zadel' => $bikefit->type_zadel,
                'zadelbreedte' => $bikefit->zadelbreedte,
                'algemene_klachten' => $bikefit->algemene_klachten,
                'opmerkingen' => $bikefit->opmerkingen,
            ]
        ];

        // Voeg mobility table HTML toe
        $data['mobility_table_html'] = $this->generateMobilityTableHtml($bikefit);

        return $data;
    }

    private function prepareInspanningstestData(Klant $klant, Inspanningstest $test)
    {
        return [
            'klant' => [
                'naam' => $klant->naam,
                'voornaam' => $klant->voornaam,
                'email' => $klant->email,
                'telefoon' => $klant->telefoon,
                'geboortedatum' => $klant->geboortedatum ? $klant->geboortedatum->format('d-m-Y') : '',
                'leeftijd' => $klant->geboortedatum ? $klant->geboortedatum->age : '',
                'sport' => $klant->sport,
                'niveau' => $klant->niveau,
            ],
            'test' => [
                'datum' => $test->created_at->format('d-m-Y'),
                'testtype' => $test->testtype,
                'duur_minuten' => $test->duur_minuten,
                'max_hartslag' => $test->max_hartslag,
                'rust_hartslag' => $test->rust_hartslag,
                'max_vermogen' => $test->max_vermogen,
                'vo2_max' => $test->vo2_max,
                'lactaat_drempel' => $test->lactaat_drempel,
                'opmerkingen' => $test->opmerkingen,
            ]
        ];
    }

    private function processTemplate(Sjabloon $sjabloon, array $data)
    {
        $processedPages = [];

        foreach ($sjabloon->paginas as $pagina) {
            if ($pagina->is_url_pagina) {
                $processedPages[] = [
                    'type' => 'url',
                    'url' => $pagina->externe_url,
                    'pagina_nummer' => $pagina->pagina_nummer
                ];
            } else {
                $processedContent = $this->replacePlaceholders($pagina->inhoud, $data);
                $processedPages[] = [
                    'type' => 'content',
                    'content' => $processedContent,
                    'background' => $pagina->achtergrond_url,
                    'pagina_nummer' => $pagina->pagina_nummer
                ];
            }
        }

        return [
            'sjabloon' => $sjabloon,
            'paginas' => $processedPages,
            'data' => $data
        ];
    }

    private function replacePlaceholders(string $content, array $data)
    {
        // Vervang alle placeholders met echte data
        $placeholders = $this->extractPlaceholders($content);
        
        foreach ($placeholders as $placeholder) {
            $value = $this->getValueFromData($placeholder, $data);
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    private function extractPlaceholders(string $content)
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        return $matches[0];
    }

    private function getValueFromData(string $placeholder, array $data)
    {
        // Verwijder {{ en }}
        $key = trim($placeholder, '{}');
        
        // Special case voor mobility table
        if ($key === 'mobility_table_html') {
            return $data['mobility_table_html'] ?? '';
        }

        // Split op punt voor nested keys (bijv. klant.naam)
        $parts = explode('.', $key);
        $value = $data;

        foreach ($parts as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return ''; // Placeholder niet gevonden
            }
        }

        return $value ?? '';
    }

    private function generateMobilityTableHtml(Bikefit $bikefit)
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

        $html = '<table class="mobility-table" style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #f5f5f5;">';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Test</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Resultaat</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Status</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($mobilityFields as $field => $label) {
            $value = $bikefit->$field;
            $status = $this->getMobilityStatus($value);
            $statusColor = $this->getMobilityStatusColor($status);
            
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $label . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . ($value ?? '-') . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; color: ' . $statusColor . '; font-weight: bold;">' . $status . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    private function getMobilityStatus($value)
    {
        if (is_null($value) || $value === '') {
            return 'Niet getest';
        }

        $numValue = (float) $value;
        
        if ($numValue >= 90) return 'Uitstekend';
        if ($numValue >= 75) return 'Goed';
        if ($numValue >= 60) return 'Gemiddeld';
        if ($numValue >= 45) return 'Matig';
        
        return 'Slecht';
    }

    private function getMobilityStatusColor($status)
    {
        return match($status) {
            'Uitstekend' => '#28a745',
            'Goed' => '#6c757d',
            'Gemiddeld' => '#ffc107',
            'Matig' => '#fd7e14',
            'Slecht' => '#dc3545',
            default => '#6c757d'
        };
    }
}