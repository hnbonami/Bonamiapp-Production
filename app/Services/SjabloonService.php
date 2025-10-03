<?php

namespace App\Services;

use App\Models\Bikefit;
use App\Models\Klant;
use App\Services\BikefitCalculator;

class SjabloonService
{
    /**
     * Vervang placeholders in HTML op basis van de meegegeven bikefit/klant.
     */
    public function vervangSleutels(string $html, ?Bikefit $bikefit = null, ?Klant $klant = null): string
    {
        // Vervang basis klant gegevens
        if ($klant) {
            $html = str_replace('$Naam$', $klant->naam ?? '', $html);
            $html = str_replace('$Voornaam$', $klant->voornaam ?? '', $html);
            $html = str_replace('$Email$', $klant->email ?? '', $html);
            $html = str_replace('$Telefoon$', $klant->telefoon ?? '', $html);
        }

        // Vervang bikefit gegevens
        if ($bikefit) {
            $datum = $bikefit->datum ? $bikefit->datum->format('d/m/Y') : date('d/m/Y');
            $html = str_replace('$Datum$', $datum, $html);
            $html = str_replace('$Testtype$', $bikefit->testtype ?? '', $html);
            $html = str_replace('$Lengte$', $bikefit->lengte_cm ?? '', $html);
            $html = str_replace('$Binnenbeenlengte$', $bikefit->binnenbeenlengte_cm ?? '', $html);
            $html = str_replace('$Armlengte$', $bikefit->armlengte_cm ?? '', $html);
            $html = str_replace('$Romplengte$', $bikefit->romplengte_cm ?? '', $html);
            $html = str_replace('$Schouderbreedte$', $bikefit->schouderbreedte_cm ?? '', $html);
            $html = str_replace('$Fietsmerk$', $bikefit->fietsmerk ?? '', $html);
            $html = str_replace('$Kadermaat$', $bikefit->kadermaat ?? '', $html);
            $html = str_replace('$TypeFitting$', $bikefit->type_fitting ?? '', $html);
            $html = str_replace('$Opmerkingen$', $bikefit->opmerkingen ?? '', $html);

            // Individuele mobiliteit sleutels
            $html = str_replace('$StraightLegRaiseLinks$', $bikefit->straight_leg_raise_links ?? '-', $html);
            $html = str_replace('$StraightLegRaiseRechts$', $bikefit->straight_leg_raise_rechts ?? '-', $html);
            $html = str_replace('$KnieflexieLinks$', $bikefit->knieflexie_links ?? '-', $html);
            $html = str_replace('$KnieflexieRechts$', $bikefit->knieflexie_rechts ?? '-', $html);
            $html = str_replace('$HeupEndorotatieLinks$', $bikefit->heup_endorotatie_links ?? '-', $html);
            $html = str_replace('$HeupEndorotatieRechts$', $bikefit->heup_endorotatie_rechts ?? '-', $html);
            $html = str_replace('$HeupExorotatieLinks$', $bikefit->heup_exorotatie_links ?? '-', $html);
            $html = str_replace('$HeupExorotatieRechts$', $bikefit->heup_exorotatie_rechts ?? '-', $html);
            $html = str_replace('$EnkeldorsiflexieLinks$', $bikefit->enkeldorsiflexie_links ?? '-', $html);
            $html = str_replace('$EnkeldorsiflexieRechts$', $bikefit->enkeldorsiflexie_rechts ?? '-', $html);
            $html = str_replace('$OneLegSquatLinks$', $bikefit->one_leg_squat_links ?? '-', $html);
            $html = str_replace('$OneLegSquatRechts$', $bikefit->one_leg_squat_rechts ?? '-', $html);

            // Mobiliteitstabel sleutel
            if (strpos($html, '$MobiliteitTabel$') !== false) {
                $mobiliteitTabelHtml = $this->generateMobiliteitTabel($bikefit);
                $html = str_replace('$MobiliteitTabel$', $mobiliteitTabelHtml, $html);
            }

            // Nieuwe compacte mobility table sleutel
            if (strpos($html, '$MobilityTable$') !== false) {
                $mobilityTableHtml = $this->generateMobilityTableLikeResults($bikefit);
                $html = str_replace('$MobilityTable$', $mobilityTableHtml, $html);
            }

            // Mobiliteit overzicht sleutel  
            if (strpos($html, '$MobiliteitOverzicht$') !== false) {
                $mobiliteitHtml = $this->generateMobiliteitOverzicht($bikefit);
                $html = str_replace('$MobiliteitOverzicht$', $mobiliteitHtml, $html);
            }
        }

        try {
            if ($bikefit) {
                $mobilityHtml = app(BikefitCalculator::class)->renderMobilityTableHtml($bikefit);
                $aliases = ['MobiliteitTabel','mobiliteitstabel','mobility_table','mobiliteitsstabel'];
                foreach ($aliases as $k) {
                    $patterns = [
                        '/\\$\\s*' . preg_quote($k, '/') . '\\s*\\$/i',
                        '/\\{\\{\\s*' . preg_quote($k, '/') . '\\s*\\}\\}/i',
                        '/\n?::\\s*' . preg_quote($k, '/') . '\\s*::\n?/i',
                        '/\\[\\[\\s*' . preg_quote($k, '/') . '\\s*\\]\\]/i',
                    ];
                    foreach ($patterns as $p) {
                        $html = preg_replace($p, $mobilityHtml, $html);
                    }
                }
            }
        } catch (\Throwable $e) {
            // laat HTML ongewijzigd bij fout
        }

        return $html;
    }

    private function generateMobiliteitTabel($bikefit)
    {
        $mobilityFields = [
            'straight_leg_raise' => 'Straight Leg Raise',
            'knieflexie' => 'Knieflexie', 
            'heup_endorotatie' => 'Heup endorotatie',
            'heup_exorotatie' => 'Heup exorotatie',
            'enkeldorsiflexie' => 'Enkeldorsiflexie',
            'one_leg_squat' => 'One leg squat',
        ];

        $getColorStyle = function($value) {
            switch($value) {
                case 'Heel laag':
                    return 'background-color: #FEE2E2; color: #DC2626;';
                case 'Laag':
                    return 'background-color: #FED7AA; color: #EA580C;';
                case 'Gemiddeld':
                    return 'background-color: #FEF3C7; color: #D97706;';
                case 'Hoog':
                    return 'background-color: #DCFCE7; color: #16A34A;';
                case 'Heel hoog':
                    return 'background-color: #DCFCE7; color: #16A34A;';
                default:
                    return 'background-color: #F3F4F6; color: #6B7280;';
            }
        };

        $html = '<div style="margin: 20px 0; font-family: Arial, sans-serif;">
            <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #1F2937;">Functionele controle / Mobiliteit</h4>
            <table style="width: 100%; max-width: 400px; border-collapse: collapse; font-size: 11px; border: 1px solid #D1D5DB;">
                <thead>
                    <tr style="background-color: #C1DFEB;">
                        <th style="border: 1px solid #D1D5DB; padding: 5px; text-align: left; font-weight: bold;">Test</th>
                        <th style="border: 1px solid #D1D5DB; padding: 5px; text-align: center; font-weight: bold;">Links</th>
                        <th style="border: 1px solid #D1D5DB; padding: 5px; text-align: center; font-weight: bold;">Rechts</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($mobilityFields as $key => $label) {
            $linksValue = $bikefit->{$key . '_links'} ?? '-';
            $rechtsValue = $bikefit->{$key . '_rechts'} ?? '-';
            
            $linksStyle = $getColorStyle($linksValue);
            $rechtsStyle = $getColorStyle($rechtsValue);
            
            $html .= '<tr>
                <td style="border: 1px solid #D1D5DB; padding: 5px; font-weight: 600; background-color: #FFFFFF;">' . htmlspecialchars($label) . '</td>
                <td style="border: 1px solid #D1D5DB; padding: 3px; text-align: center; background-color: #FFFFFF;">
                    <span style="' . $linksStyle . ' padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 500; display: inline-block; min-width: 50px;">' . htmlspecialchars($linksValue) . '</span>
                </td>
                <td style="border: 1px solid #D1D5DB; padding: 3px; text-align: center; background-color: #FFFFFF;">
                    <span style="' . $rechtsStyle . ' padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 500; display: inline-block; min-width: 50px;">' . htmlspecialchars($rechtsValue) . '</span>
                </td>
            </tr>';
        }

        $html .= '</tbody></table></div>';
        
        return $html;
    }

    private function generateMobiliteitTabelCompact($bikefit)
    {
        $mobilityFields = [
            'straight_leg_raise' => 'Straight Leg Raise (hamstrings)',
            'knieflexie' => 'Knieflexie (rectus femoris)', 
            'heup_endorotatie' => 'Heup endorotatie',
            'heup_exorotatie' => 'Heup exorotatie',
            'enkeldorsiflexie' => 'Enkeldorsiflexie',
            'one_leg_squat' => 'One leg squat',
        ];

        $getColor = function($value) {
            switch($value) {
                case 'Heel laag': return 'background:#FEE2E2;color:#DC2626;';
                case 'Laag': return 'background:#FED7AA;color:#EA580C;';
                case 'Gemiddeld': return 'background:#FEF3C7;color:#D97706;';
                case 'Hoog': return 'background:#DCFCE7;color:#16A34A;';
                case 'Heel hoog': return 'background:#DCFCE7;color:#16A34A;';
                default: return 'background:#F3F4F6;color:#6B7280;';
            }
        };

        $html = '<div style="width: 100%; max-width: 450px; margin: 10px auto; font-family: Arial, sans-serif;">
            <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #1F2937;">Functionele controle / Mobiliteit</h4>
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #D1D5DB; font-size: 11px;">
                <thead>
                    <tr style="background-color: rgba(193,223,235,0.85);">
                        <th style="border: 1px solid #D1D5DB; padding: 4px 6px; text-align: left; font-weight: bold;">Test</th>
                        <th style="border: 1px solid #D1D5DB; padding: 4px 6px; text-align: center; font-weight: bold;">Links</th>
                        <th style="border: 1px solid #D1D5DB; padding: 4px 6px; text-align: center; font-weight: bold;">Rechts</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($mobilityFields as $key => $label) {
            $linksValue = $bikefit->{$key . '_links'} ?? '-';
            $rechtsValue = $bikefit->{$key . '_rechts'} ?? '-';
            
            $linksStyle = $getColor($linksValue);
            $rechtsStyle = $getColor($rechtsValue);
            
            $html .= '<tr>
                <td style="border: 1px solid #D1D5DB; padding: 4px 6px; font-weight: 600; font-size: 10px;">' . htmlspecialchars($label) . '</td>
                <td style="border: 1px solid #D1D5DB; padding: 2px; text-align: center;">
                    <span style="' . $linksStyle . 'padding:2px 4px;border-radius:4px;font-size:9px;font-weight:500;display:inline-block;min-width:50px;">' . htmlspecialchars($linksValue) . '</span>
                </td>
                <td style="border: 1px solid #D1D5DB; padding: 2px; text-align: center;">
                    <span style="' . $rechtsStyle . 'padding:2px 4px;border-radius:4px;font-size:9px;font-weight:500;display:inline-block;min-width:50px;">' . htmlspecialchars($rechtsValue) . '</span>
                </td>
            </tr>';
        }

        $html .= '</tbody></table></div>';
        
        return $html;
    }

    private function generateMobiliteitTabelText($bikefit)
    {
        $mobilityFields = [
            'straight_leg_raise' => 'Straight Leg Raise',
            'knieflexie' => 'Knieflexie', 
            'heup_endorotatie' => 'Heup endorotatie',
            'heup_exorotatie' => 'Heup exorotatie',
            'enkeldorsiflexie' => 'Enkeldorsiflexie',
            'one_leg_squat' => 'One leg squat',
        ];

        $text = '<strong>Functionele controle / Mobiliteit</strong><br><br>';
        
        foreach ($mobilityFields as $key => $label) {
            $links = $bikefit->{$key . '_links'} ?? '-';
            $rechts = $bikefit->{$key . '_rechts'} ?? '-';
            
            $text .= '<strong>' . $label . ':</strong> Links: ' . $links . ' | Rechts: ' . $rechts . '<br>';
        }
        
        return $text;
    }

    private function generateMobiliteitTabelSafe($bikefit)
    {
        $mobilityFields = [
            'straight_leg_raise' => 'Straight Leg Raise',
            'knieflexie' => 'Knieflexie', 
            'heup_endorotatie' => 'Heup endorotatie',
            'heup_exorotatie' => 'Heup exorotatie',
            'enkeldorsiflexie' => 'Enkeldorsiflexie',
            'one_leg_squat' => 'One leg squat',
        ];

        $html = '<table border="1" cellpadding="3" cellspacing="0" style="font-size:10px;width:350px;">
<tr style="background-color:#c1dfeb;">
<td><b>Test</b></td>
<td><b>Links</b></td>
<td><b>Rechts</b></td>
</tr>';

        foreach ($mobilityFields as $key => $label) {
            $links = $bikefit->{$key . '_links'} ?? '-';
            $rechts = $bikefit->{$key . '_rechts'} ?? '-';
            
            $html .= '<tr>
<td>' . htmlspecialchars($label) . '</td>
<td>' . htmlspecialchars($links) . '</td>
<td>' . htmlspecialchars($rechts) . '</td>
</tr>';
        }

        $html .= '</table>';
        
        return $html;
    }

    private function generateMobiliteitOverzicht($bikefit)
    {
        $tests = [
            'straight_leg_raise' => 'Straight Leg Raise',
            'knieflexie' => 'Knieflexie', 
            'heup_endorotatie' => 'Heup endorotatie',
            'heup_exorotatie' => 'Heup exorotatie',
            'enkeldorsiflexie' => 'Enkeldorsiflexie',
            'one_leg_squat' => 'One leg squat',
        ];

        $output = '<p><strong>Functionele controle / Mobiliteit:</strong></p>';
        
        foreach ($tests as $key => $label) {
            $links = $bikefit->{$key . '_links'} ?? '-';
            $rechts = $bikefit->{$key . '_rechts'} ?? '-';
            $output .= '<p>' . $label . ': Links=' . $links . ', Rechts=' . $rechts . '</p>';
        }
        
        return $output;
    }

    private function generateMobiliteitOverzichtSimple($bikefit)
    {
        $html = 'Functionele controle / Mobiliteit:<br>';
        $html .= 'Straight Leg Raise: Links=' . ($bikefit->straight_leg_raise_links ?? '-') . ', Rechts=' . ($bikefit->straight_leg_raise_rechts ?? '-') . '<br>';
        $html .= 'Knieflexie: Links=' . ($bikefit->knieflexie_links ?? '-') . ', Rechts=' . ($bikefit->knieflexie_rechts ?? '-') . '<br>';
        $html .= 'Heup endorotatie: Links=' . ($bikefit->heup_endorotatie_links ?? '-') . ', Rechts=' . ($bikefit->heup_endorotatie_rechts ?? '-') . '<br>';
        $html .= 'Heup exorotatie: Links=' . ($bikefit->heup_exorotatie_links ?? '-') . ', Rechts=' . ($bikefit->heup_exorotatie_rechts ?? '-') . '<br>';
        $html .= 'Enkeldorsiflexie: Links=' . ($bikefit->enkeldorsiflexie_links ?? '-') . ', Rechts=' . ($bikefit->enkeldorsiflexie_rechts ?? '-') . '<br>';
        $html .= 'One leg squat: Links=' . ($bikefit->one_leg_squat_links ?? '-') . ', Rechts=' . ($bikefit->one_leg_squat_rechts ?? '-') . '<br>';
        
        return $html;
    }

    private function generateMobilityTableLikeResults($bikefit)
    {
        $mobilityTests = [
            [
                'name' => 'Straight Leg Raise',
                'subtitle' => '(hamstrings)',
                'links' => $bikefit->straight_leg_raise_links ?? '-',
                'rechts' => $bikefit->straight_leg_raise_rechts ?? '-',
            ],
            [
                'name' => 'Knieflexie',
                'subtitle' => '(rectus femoris)',
                'links' => $bikefit->knieflexie_links ?? '-',
                'rechts' => $bikefit->knieflexie_rechts ?? '-',
            ],
            [
                'name' => 'Heup endorotatie',
                'subtitle' => '',
                'links' => $bikefit->heup_endorotatie_links ?? '-',
                'rechts' => $bikefit->heup_endorotatie_rechts ?? '-',
            ],
            [
                'name' => 'Heup exorotatie',
                'subtitle' => '',
                'links' => $bikefit->heup_exorotatie_links ?? '-',
                'rechts' => $bikefit->heup_exorotatie_rechts ?? '-',
            ],
            [
                'name' => 'Enkeldorsiflexie',
                'subtitle' => '',
                'links' => $bikefit->enkeldorsiflexie_links ?? '-',
                'rechts' => $bikefit->enkeldorsiflexie_rechts ?? '-',
            ],
            [
                'name' => 'One leg squat',
                'subtitle' => '',
                'links' => $bikefit->one_leg_squat_links ?? '-',
                'rechts' => $bikefit->one_leg_squat_rechts ?? '-',
            ],
        ];

        $getInlineStyles = function($value) {
            switch($value) {
                case 'Heel laag': 
                    return 'background-color: #FEE2E2; color: #DC2626; padding: 4px 8px; border-radius: 9999px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center;';
                case 'Laag': 
                    return 'background-color: #FED7AA; color: #EA580C; padding: 4px 8px; border-radius: 9999px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center;';
                case 'Gemiddeld': 
                    return 'background-color: #FEF3C7; color: #D97706; padding: 4px 8px; border-radius: 9999px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center;';
                case 'Hoog': 
                    return 'background-color: #DCFCE7; color: #16A34A; padding: 4px 8px; border-radius: 9999px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center;';
                case 'Heel hoog': 
                    return 'background-color: #DCFCE7; color: #16A34A; padding: 4px 8px; border-radius: 9999px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center;';
                default: 
                    return 'background-color: #F3F4F6; color: #6B7280; padding: 4px 8px; border-radius: 9999px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center;';
            }
        };

        $html = '<div style="transform: scale(0.7); transform-origin: top left; margin-bottom: -100px;">
            <div style="background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #E5E7EB; overflow: hidden; max-width: 800px;">
                <div style="background: linear-gradient(to right, #EFF6FF, #DBEAFE); padding: 20px; border-bottom: 1px solid #E5E7EB;">
                    <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Functionele controle/ Mobiliteit</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                        <thead style="background-color: #DBEAFE;">
                            <tr>
                                <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 500; color: #374151; text-transform: uppercase; letter-spacing: 0.05em;">Test</th>
                                <th style="padding: 12px 24px; text-align: center; font-size: 12px; font-weight: 500; color: #374151; text-transform: uppercase; letter-spacing: 0.05em;">Links</th>
                                <th style="padding: 12px 24px; text-align: center; font-size: 12px; font-weight: 500; color: #374151; text-transform: uppercase; letter-spacing: 0.05em;">Rechts</th>
                            </tr>
                        </thead>
                        <tbody style="background-color: white;">';

        foreach ($mobilityTests as $index => $test) {
            $borderBottom = $index < count($mobilityTests) - 1 ? 'border-bottom: 1px solid #E5E7EB;' : '';
            $linksStyle = $getInlineStyles($test['links']);
            $rechtsStyle = $getInlineStyles($test['rechts']);
            
            $html .= '<tr style="' . $borderBottom . '">
                <td style="padding: 16px 24px; white-space: nowrap;">
                    <div style="font-size: 14px; font-weight: 500; color: #111827;">' . htmlspecialchars($test['name']) . '</div>';
            
            if (!empty($test['subtitle'])) {
                $html .= '<div style="font-size: 12px; color: #6B7280; margin-top: 2px;">' . htmlspecialchars($test['subtitle']) . '</div>';
            }
            
            $html .= '</td>
                <td style="padding: 16px 24px; white-space: nowrap; text-align: center;">
                    <span style="' . $linksStyle . '">' . htmlspecialchars($test['links']) . '</span>
                </td>
                <td style="padding: 16px 24px; white-space: nowrap; text-align: center;">
                    <span style="' . $rechtsStyle . '">' . htmlspecialchars($test['rechts']) . '</span>
                </td>
            </tr>';
        }

        $html .= '</tbody></table></div></div></div>';

        return $html;
    }

    /** Return a list of supported placeholders with their labels for UI. */
    public function getAvailablePlaceholders(): array
    {
        $placeholders = [
            // ...existing placeholders...
        ];

        // Add mobility table key (Dutch label)
        $placeholders['mobiliteitstabel'] = 'Mobiliteitstabel (HTML)';
        // And English alias for consistency
        $placeholders['mobility_table'] = 'Mobility table (HTML)';

        return $placeholders;
    }

    /** Lijst met overige sleutels voor de UI. */
    public function overigeSleutels(): array
    {
        $sleutels = [
            // ...existing keys...
        ];
        // Voeg onze sleutel toe
        $sleutels[] = '$MobiliteitTabel$';
        return $sleutels;
    }
}

