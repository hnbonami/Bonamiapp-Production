<?php
namespace App\Services;

use App\Models\Bikefit;

class BikefitCalculator
{
    public function calculate(Bikefit $bikefit, $resultsNa = null): array
    {
        $results = [];

        // Zadelhoogte na bikefit
        if (isset($bikefit->context) && $bikefit->context === 'na') {
            // Nieuw veld: tussenberekening_drop = aanpassingen_zadel + aanpassingen_drop
            $tussenberekening_drop = 0;
            if (isset($bikefit->aanpassingen_zadel) && is_numeric($bikefit->aanpassingen_zadel)) {
                $tussenberekening_drop += floatval($bikefit->aanpassingen_zadel);
            }
            if (isset($bikefit->aanpassingen_drop) && is_numeric($bikefit->aanpassingen_drop)) {
                $tussenberekening_drop += floatval($bikefit->aanpassingen_drop);
            }
            $results['tussenberekening_drop'] = $tussenberekening_drop;
            // Nieuw veld: tussenberekening_reach = aanpassingen_setback + aanpassingen_reach
            $tussenberekening_reach = 0;
            if (isset($bikefit->aanpassingen_setback) && is_numeric($bikefit->aanpassingen_setback)) {
                $tussenberekening_reach += floatval($bikefit->aanpassingen_setback);
            }
            if (isset($bikefit->aanpassingen_reach) && is_numeric($bikefit->aanpassingen_reach)) {
                $tussenberekening_reach += floatval($bikefit->aanpassingen_reach);
            }
            $results['tussenberekening_reach'] = $tussenberekening_reach;
            if (isset($bikefit->zadel_trapas_afstand) && is_numeric($bikefit->zadel_trapas_afstand)) {
                $results['zadelhoogte'] = round(floatval($bikefit->zadel_trapas_afstand), 1);
            } else {
                $results['zadelhoogte'] = '';
            }
            $lengte = floatval($bikefit->lengte_cm ?? 0);
            if ($lengte > 0) {
                if ($lengte < 166) {
                    $results['cranklengte'] = 165;
                } elseif ($lengte < 183) {
                    $results['cranklengte'] = 170;
                } elseif ($lengte < 191) {
                    $results['cranklengte'] = 172.5;
                } else {
                    $results['cranklengte'] = 175;
                }
            } else {
                $results['cranklengte'] = null;
            }
            $binnenbeenlengte = floatval($bikefit->binnenbeenlengte_cm ?? 0);
            $schouderbreedte = floatval($bikefit->schouderbreedte_cm ?? 0);
            if ($schouderbreedte > 0) {
                if ($schouderbreedte < 36) {
                    $results['stuurbreedte'] = 38;
                } elseif ($schouderbreedte < 40) {
                    $results['stuurbreedte'] = 40;
                } elseif ($schouderbreedte < 44) {
                    $results['stuurbreedte'] = 42;
                } else {
                    $results['stuurbreedte'] = 44;
                }
            } else {
                $results['stuurbreedte'] = null;
            }
            $stuur_trapas_afstand = isset($bikefit->stuur_trapas_afstand) && is_numeric($bikefit->stuur_trapas_afstand) ? floatval($bikefit->stuur_trapas_afstand) : null;
            $stuur_trapas_hoek = isset($bikefit->stuur_trapas_hoek) && is_numeric($bikefit->stuur_trapas_hoek) ? floatval($bikefit->stuur_trapas_hoek) : null;
            $zadel_trapas_afstand = isset($bikefit->zadel_trapas_afstand) && is_numeric($bikefit->zadel_trapas_afstand) ? floatval($bikefit->zadel_trapas_afstand) : null;
            $zadel_trapas_hoek = isset($bikefit->zadel_trapas_hoek) && is_numeric($bikefit->zadel_trapas_hoek) ? floatval($bikefit->zadel_trapas_hoek) : null;
            if ($zadel_trapas_afstand !== null && $stuur_trapas_afstand !== null && $zadel_trapas_hoek !== null && $stuur_trapas_hoek !== null) {
                // Bereken D als projectie van zijde I op de horizontale as
                $gamma = 180 - $zadel_trapas_hoek - $stuur_trapas_hoek;
                $afstand_center_zadel_stuur = sqrt(pow($zadel_trapas_afstand, 2) + pow($stuur_trapas_afstand, 2) - 2 * $zadel_trapas_afstand * $stuur_trapas_afstand * cos($gamma * pi() / 180));
                // Horizontale projectie: D = I * cos(hoek met horizontaal)
                // Hoek met horizontaal = stuur_trapas_hoek
                $results['reach'] = round($afstand_center_zadel_stuur * cos($stuur_trapas_hoek * pi() / 180), 1);
            } else if ($stuur_trapas_afstand !== null && $stuur_trapas_hoek !== null) {
                $results['reach'] = round($stuur_trapas_afstand * cos($stuur_trapas_hoek * pi() / 180), 1);
            } else {
                $results['reach'] = null;
            }
            $zadelhoogte = isset($results['zadelhoogte']) && is_numeric($results['zadelhoogte']) ? floatval($results['zadelhoogte']) : null;
            $hoek = isset($bikefit->zadel_trapas_hoek) && is_numeric($bikefit->zadel_trapas_hoek) ? floatval($bikefit->zadel_trapas_hoek) : null;
            if ($zadelhoogte !== null && $hoek !== null) {
                $berekend = $zadelhoogte * cos($hoek * pi() / 180);
                $results['zadelterugstand'] = round($berekend, 1);
            } else {
                $results['zadelterugstand'] = null;
            }
            // Direct na berekening van B, bereken C
            $zadellengte = (isset($bikefit->zadel_lengte_center_top) && is_numeric($bikefit->zadel_lengte_center_top)) ? floatval($bikefit->zadel_lengte_center_top) : 0;
            $B = isset($results['zadelterugstand']) && is_numeric($results['zadelterugstand']) ? floatval($results['zadelterugstand']) : null;
            if ($B !== null) {
                $berekendeC = round($B - $zadellengte, 1);
                \Log::info('Berekening C (zadelterugstand_top):', [
                    'B' => $B,
                    'zadellengte' => $zadellengte,
                    'C' => $berekendeC
                ]);
                $results['zadelterugstand_top'] = $berekendeC;
            } else {
                $results['zadelterugstand_top'] = null;
            }
            if ($binnenbeenlengte > 0) {
                $results['drop'] = round($binnenbeenlengte * 0.08, 1);
            } else {
                $results['drop'] = null;
            }
               // Berekening afstand center zadel-stuur (veld I)
               $zadel_trapas_afstand = isset($bikefit->zadel_trapas_afstand) && is_numeric($bikefit->zadel_trapas_afstand) ? floatval($bikefit->zadel_trapas_afstand) : null;
               $stuur_trapas_afstand = isset($bikefit->stuur_trapas_afstand) && is_numeric($bikefit->stuur_trapas_afstand) ? floatval($bikefit->stuur_trapas_afstand) : null;
               $zadel_trapas_hoek = isset($bikefit->zadel_trapas_hoek) && is_numeric($bikefit->zadel_trapas_hoek) ? floatval($bikefit->zadel_trapas_hoek) : null;
               $stuur_trapas_hoek = isset($bikefit->stuur_trapas_hoek) && is_numeric($bikefit->stuur_trapas_hoek) ? floatval($bikefit->stuur_trapas_hoek) : null;
            if ($zadel_trapas_afstand !== null && $stuur_trapas_afstand !== null && $zadel_trapas_hoek !== null && $stuur_trapas_hoek !== null) {
                $gamma = 180 - $zadel_trapas_hoek - $stuur_trapas_hoek;
                $afstand_center_zadel_stuur = sqrt(pow($zadel_trapas_afstand, 2) + pow($stuur_trapas_afstand, 2) - 2 * $zadel_trapas_afstand * $stuur_trapas_afstand * cos($gamma * pi() / 180));
                $results['afstand_center_zadel_stuur'] = round($afstand_center_zadel_stuur, 1);
                // Nieuwe berekening D
                $B_raw = isset($bikefit->zadel_trapas_afstand) && is_numeric($bikefit->zadel_trapas_afstand) && isset($bikefit->zadel_trapas_hoek) && is_numeric($bikefit->zadel_trapas_hoek)
                    ? floatval($bikefit->zadel_trapas_afstand) * cos(floatval($bikefit->zadel_trapas_hoek) * pi() / 180)
                    : null;
                $zadellengte_raw = (isset($bikefit->zadel_lengte_center_top) && is_numeric($bikefit->zadel_lengte_center_top)) ? floatval($bikefit->zadel_lengte_center_top) : 0;
                $C_raw = $B_raw !== null ? $B_raw - $zadellengte_raw : null;
                $I_raw = $afstand_center_zadel_stuur;
                if ($B_raw !== null && $C_raw !== null && $I_raw !== null) {
                    $results['reach'] = round($I_raw - $C_raw - 0.5 * ($B_raw - $C_raw), 1);
                    // Bereken F (drop zadel-stuur) met Pythagoras, correcte projectie
                    // Bereken F (drop zadel-stuur) als verticale projectie van I
                    // Bereken F als verschil in y-coördinaat tussen zadel en stuur
                    if ($zadel_trapas_afstand !== null && $zadel_trapas_hoek !== null && $stuur_trapas_afstand !== null && $stuur_trapas_hoek !== null) {
                        $zadel_y = $zadel_trapas_afstand * sin($zadel_trapas_hoek * pi() / 180);
                        $stuur_y = $stuur_trapas_afstand * sin($stuur_trapas_hoek * pi() / 180);
                        $F = abs($zadel_y - $stuur_y);
                        $results['drop_zadel_stuur'] = round($F, 1);
                        \Log::info('Berekening F (drop_zadel_stuur, y-coordinaat):', [
                            'zadel_trapas_afstand' => $zadel_trapas_afstand,
                            'zadel_trapas_hoek' => $zadel_trapas_hoek,
                            'zadel_y' => $zadel_y,
                            'stuur_trapas_afstand' => $stuur_trapas_afstand,
                            'stuur_trapas_hoek' => $stuur_trapas_hoek,
                            'stuur_y' => $stuur_y,
                            'F' => $F,
                            'F_rounded' => $results['drop_zadel_stuur']
                        ]);
                        // Bereken E (reach) na bikefit met Pythagoras: E = sqrt(C^2 + D^2)
                        if (isset($results['reach']) && isset($results['zadelterugstand_top']) && is_numeric($results['reach']) && is_numeric($results['zadelterugstand_top'])) {
                            $E = sqrt(pow($results['reach'], 2) + pow($results['zadelterugstand_top'], 2));
                            $results['reach_e'] = round($E, 1);
                            \Log::info('Berekening E (reach na bikefit, Pythagoras C en D):', [
                                'reach (D)' => $results['reach'],
                                'zadelterugstand_top (C)' => $results['zadelterugstand_top'],
                                'E' => $E,
                                'E_rounded' => $results['reach_e']
                            ]);
                        } else {
                            $results['reach_e'] = null;
                        }
                    } else {
                        $results['drop_zadel_stuur'] = null;
                    }
                }
            } else {
                $results['afstand_center_zadel_stuur'] = null;
            }
            return $results;
        }

        // Zadelhoogte en zadelterugstand voor bikefit
        if (isset($bikefit->context) && $bikefit->context === 'voor') {
            // Berekening D voor bikefit: D = D na bikefit - tussenberekening_reach
            if ($resultsNa && isset($resultsNa['reach']) && is_numeric($resultsNa['reach'])) {
                $results['reach'] = round($resultsNa['reach'] - ($resultsNa['tussenberekening_reach'] ?? 0), 1);
            } else {
                $results['reach'] = null;
            }
            // Berekening F voor bikefit: F = drop na bikefit - tussenberekening_drop
            if ($resultsNa && isset($resultsNa['drop_zadel_stuur']) && is_numeric($resultsNa['drop_zadel_stuur'])) {
                $results['drop_zadel_stuur'] = round($resultsNa['drop_zadel_stuur'] - ($resultsNa['tussenberekening_drop'] ?? 0), 1);
            } else {
                $results['drop_zadel_stuur'] = null;
            }
            // Berekening E voor bikefit: E = sqrt(D^2 + F^2)
            if (isset($results['reach']) && is_numeric($results['reach']) && isset($results['drop_zadel_stuur']) && is_numeric($results['drop_zadel_stuur'])) {
                $E_voor = sqrt(pow($results['reach'], 2) + pow($results['drop_zadel_stuur'], 2));
                $results['reach_e'] = round($E_voor, 1);
            } else {
                $results['reach_e'] = null;
            }
            $zadelhoogteNa = isset($bikefit->zadel_trapas_afstand) && is_numeric($bikefit->zadel_trapas_afstand) ? floatval($bikefit->zadel_trapas_afstand) : null;
            $hoekNa = isset($bikefit->zadel_trapas_hoek) && is_numeric($bikefit->zadel_trapas_hoek) ? floatval($bikefit->zadel_trapas_hoek) : null;
            $aanpassingSetback = isset($bikefit->aanpassingen_setback) && is_numeric($bikefit->aanpassingen_setback) ? floatval($bikefit->aanpassingen_setback) : 0;
            $zadelterugstandNa = null;
            if ($zadelhoogteNa !== null && $hoekNa !== null) {
                $zadelterugstandNa = $zadelhoogteNa * cos($hoekNa * pi() / 180);
            }
            $results['zadelterugstand'] = $zadelterugstandNa !== null ? round($zadelterugstandNa - $aanpassingSetback, 1) : null;
            $results['zadelhoogte'] = round($zadelhoogteNa - ($bikefit->aanpassingen_zadel ?? 0), 1);
            $lengte = floatval($bikefit->lengte_cm ?? 0);
            if ($lengte > 0) {
                if ($lengte < 166) {
                    $results['cranklengte'] = 165;
                } elseif ($lengte < 183) {
                    $results['cranklengte'] = 170;
                } elseif ($lengte < 191) {
                    $results['cranklengte'] = 172.5;
                } else {
                    $results['cranklengte'] = 175;
                }
            } else {
                $results['cranklengte'] = null;
            }
            $schouderbreedte = floatval($bikefit->schouderbreedte_cm ?? 0);
            if ($schouderbreedte > 0) {
                if ($schouderbreedte < 36) {
                    $results['stuurbreedte'] = 38;
                } elseif ($schouderbreedte < 40) {
                    $results['stuurbreedte'] = 40;
                } elseif ($schouderbreedte < 44) {
                    $results['stuurbreedte'] = 42;
                } else {
                    $results['stuurbreedte'] = 44;
                }
            } else {
                $results['stuurbreedte'] = null;
            }
            // Zadelterugstand top zadel = zadelterugstand center zadel - zadellengte center-top
            $zadellengte = isset($bikefit->zadel_lengte_center_top) && is_numeric($bikefit->zadel_lengte_center_top) ? floatval($bikefit->zadel_lengte_center_top) : 0;
            $results['zadelterugstand_top'] = isset($results['zadelterugstand']) && is_numeric($results['zadelterugstand']) ? round($results['zadelterugstand'] - $zadellengte, 1) : null;
            return $results;
        }

        // Prognose: alle berekeningen
        $binnenbeenlengte = floatval($bikefit->binnenbeenlengte_cm);
        $armlengte = floatval($bikefit->armlengte_cm);
        $romplengte = floatval($bikefit->romplengte_cm);
        $type = strtolower($bikefit->type_fitting ?? '');
        if ($binnenbeenlengte > 0) {
            $results['zadelhoogte'] = round($binnenbeenlengte * 0.883, 1);
        } else {
            $results['zadelhoogte'] = null;
        }
        $zadelhoogte = $results['zadelhoogte'] ?? null;
        if ($zadelhoogte > 0) {
            switch ($type) {
                case 'comfort':
                    $zadelterugstand_top = round($zadelhoogte * 0.1 - 1.2, 1);
                    break;
                case 'sportief':
                    $zadelterugstand_top = round($zadelhoogte * 0.1 - 0.8, 1);
                    break;
                case 'race':
                    $zadelterugstand_top = round($zadelhoogte * 0.1, 1);
                    break;
                case 'mountainbike':
                case 'mtb':
                    $zadelterugstand_top = round($zadelhoogte * 0.1 - 1, 1);
                    break;
                case 'tijdritfiets':
                case 'tt':
                    $zadelterugstand_top = round($zadelhoogte * 0.03, 1);
                    break;
                default:
                    $zadelterugstand_top = null;
            }
            $results['zadelterugstand_top'] = $zadelterugstand_top;
            $zadellengte = isset($bikefit->zadel_lengte_center_top) && is_numeric($bikefit->zadel_lengte_center_top) ? floatval($bikefit->zadel_lengte_center_top) : 0;
            $results['zadelterugstand'] = $zadelterugstand_top !== null ? round($zadelterugstand_top + $zadellengte, 1) : null;
        } else {
            $results['zadelterugstand_top'] = null;
            $results['zadelterugstand'] = null;
        }
        $reach_basis = $armlengte + ($romplengte - $binnenbeenlengte);
        if ($armlengte > 0 && $romplengte > 0 && $binnenbeenlengte > 0) {
            switch ($type) {
                case 'comfort':
                    $results['reach'] = round($reach_basis * 0.38, 1);
                    break;
                case 'sportief':
                    $results['reach'] = round($reach_basis * 0.42, 1);
                    break;
                case 'race':
                    $results['reach'] = round($reach_basis * 0.44, 1);
                    break;
                case 'mountainbike':
                case 'mtb':
                    $results['reach'] = round($reach_basis * 0.44, 1);
                    break;
                case 'tijdritfiets':
                case 'tt':
                    $results['reach'] = round($reach_basis * 0.37, 1);
                    break;
                default:
                    $results['reach'] = null;
            }
        } else {
            $results['reach'] = null;
        }
        if ($binnenbeenlengte > 0) {
            switch ($type) {
                case 'comfort':
                    $results['drop'] = round($binnenbeenlengte * 0.045, 1);
                    break;
                case 'sportief':
                    $results['drop'] = round($binnenbeenlengte * 0.08, 1);
                    break;
                case 'race':
                    $results['drop'] = round($binnenbeenlengte * 0.1, 1);
                    break;
                case 'mountainbike':
                case 'mtb':
                    $results['drop'] = round($binnenbeenlengte * 0.06, 1);
                    break;
                case 'tijdritfiets':
                case 'tt':
                    $results['drop'] = round($binnenbeenlengte * 0.1, 1);
                    break;
                default:
                    $results['drop'] = null;
            }
        } else {
            $results['drop'] = null;
        }
        // Directe reach berekenen met Pythagoras: sqrt(drop^2 + reach^2)
        $drop = $results['drop'] ?? null;
        $reach = $results['reach'] ?? null;
        // Debug: log values
        \Log::info('Directe reach inputwaarden:', ['drop' => $drop, 'reach' => $reach]);
        if (is_numeric($drop) && is_numeric($reach)) {
            $directe_reach = sqrt(pow($drop, 2) + pow($reach, 2));
            $results['directe_reach'] = round($directe_reach, 1);
        } else {
            $results['directe_reach'] = null;
        }
        if ($binnenbeenlengte > 0) {
            $lengte = floatval($bikefit->lengte_cm ?? 0);
            if ($lengte > 0) {
                if ($lengte < 166) {
                    $results['cranklengte'] = 165;
                } elseif ($lengte < 183) {
                    $results['cranklengte'] = 170;
                } elseif ($lengte < 191) {
                    $results['cranklengte'] = 172.5;
                } else {
                    $results['cranklengte'] = 175;
                }
            } else {
                $results['cranklengte'] = null;
            }
        }
        $schouderbreedte = floatval($bikefit->schouderbreedte_cm ?? 0);
        if ($schouderbreedte > 0) {
            if ($schouderbreedte < 36) {
                $results['stuurbreedte'] = 38;
            } elseif ($schouderbreedte < 40) {
                $results['stuurbreedte'] = 40;
            } elseif ($schouderbreedte < 44) {
                $results['stuurbreedte'] = 42;
            } else {
                $results['stuurbreedte'] = 44;
            }
        } else {
            $results['stuurbreedte'] = null;
        }
        return $results;
    }
}


