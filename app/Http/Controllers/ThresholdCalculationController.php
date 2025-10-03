<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ThresholdCalculationController extends Controller
{
    /**
     * Calculate D-max and D-max Modified thresholds using mathematical methods
     */
    public function calculateThresholds(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.x' => 'required|numeric',
            'data.*.y' => 'required|numeric',
            'method' => 'required|string|in:dmax,dmax_modified'
        ]);

        $data = $request->input('data');
        $method = $request->input('method');

        // Sort data by X values
        usort($data, function($a, $b) {
            return $a['x'] <=> $b['x'];
        });

        try {
            if ($method === 'dmax') {
                $result = $this->calculateDmax($data);
            } else {
                $result = $this->calculateDmaxModified($data);
            }

            return response()->json([
                'success' => true,
                'method' => $method,
                'aerobe' => $result['aerobe'],
                'anaerobe' => $result['anaerobe'],
                'curve_coefficients' => $result['curve_coefficients'],
                'helper_line' => $result['helper_line']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Calculate D-max using mathematical approach
     */
    private function calculateDmax(array $data): array
    {
        // Step 1: Fit parabola to lactate data
        $coefficients = $this->fitParabola($data);
        
        // Step 2: Define helper line (first to last point)
        $firstPoint = $data[0];
        $lastPoint = end($data);
        $helperLine = $this->lineBetweenPoints(
            $firstPoint['x'], $firstPoint['y'],
            $lastPoint['x'], $lastPoint['y']
        );

        // Step 3: Find maximum distance point
        $dmaxPoint = $this->computeDmax($data, $coefficients, $helperLine);

        // Step 4: Calculate aerobe threshold (baseline + 0.4)
        $minLactate = min(array_column($data, 'y'));
        $aerobeThreshold = $minLactate + 0.4;
        $aerobeX = $this->findXForY($data, $aerobeThreshold);

        return [
            'aerobe' => [
                'x' => $aerobeX,
                'y' => $aerobeThreshold,
                'method' => 'baseline + 0.4 mmol/L'
            ],
            'anaerobe' => [
                'x' => $dmaxPoint['x'],
                'y' => $dmaxPoint['y'],
                'distance' => $dmaxPoint['distance'],
                'method' => 'Maximum distance to helper line'
            ],
            'curve_coefficients' => $coefficients,
            'helper_line' => $helperLine
        ];
    }

    /**
     * Calculate D-max Modified using mathematical approach
     */
    private function calculateDmaxModified(array $data): array
    {
        // Step 1: Fit parabola to lactate data
        $coefficients = $this->fitParabola($data);
        
        // Step 2: Calculate aerobe threshold (baseline + 0.4)
        $minLactate = min(array_column($data, 'y'));
        $aerobeThreshold = $minLactate + 0.4;
        $aerobeX = $this->findXForY($data, $aerobeThreshold);

        // Step 3: Define helper line (aerobe point to last point)
        $lastPoint = end($data);
        $helperLine = $this->lineBetweenPoints(
            $aerobeX, $aerobeThreshold,
            $lastPoint['x'], $lastPoint['y']
        );

        // Step 4: Find maximum distance point from aerobe point onwards
        $dmaxPoint = $this->computeDmaxModified($data, $coefficients, $helperLine, $aerobeX);

        return [
            'aerobe' => [
                'x' => $aerobeX,
                'y' => $aerobeThreshold,
                'method' => 'baseline + 0.4 mmol/L'
            ],
            'anaerobe' => [
                'x' => $dmaxPoint['x'],
                'y' => $dmaxPoint['y'],
                'distance' => $dmaxPoint['distance'],
                'method' => 'Maximum distance from aerobe to last point'
            ],
            'curve_coefficients' => $coefficients,
            'helper_line' => $helperLine
        ];
    }

    /**
     * Linear function between two points: y = mx + b
     */
    private function lineBetweenPoints(float $x1, float $y1, float $x2, float $y2): array
    {
        $m = ($y2 - $y1) / ($x2 - $x1);
        $b = $y1 - $m * $x1;
        
        return ['slope' => $m, 'intercept' => $b];
    }

    /**
     * Fit parabola to data points: y = ax² + bx + c
     */
    private function fitParabola(array $data): array
    {
        $n = count($data);
        
        // Build matrices for least squares fitting
        $sumX = array_sum(array_column($data, 'x'));
        $sumX2 = array_sum(array_map(fn($p) => $p['x'] ** 2, $data));
        $sumX3 = array_sum(array_map(fn($p) => $p['x'] ** 3, $data));
        $sumX4 = array_sum(array_map(fn($p) => $p['x'] ** 4, $data));
        
        $sumY = array_sum(array_column($data, 'y'));
        $sumXY = array_sum(array_map(fn($p) => $p['x'] * $p['y'], $data));
        $sumX2Y = array_sum(array_map(fn($p) => ($p['x'] ** 2) * $p['y'], $data));

        // Solve system of equations using Cramer's rule
        $det = $n * ($sumX2 * $sumX4 - $sumX3 ** 2) - 
               $sumX * ($sumX * $sumX4 - $sumX2 * $sumX3) + 
               $sumX2 * ($sumX * $sumX3 - $sumX2 ** 2);

        if (abs($det) < 1e-10) {
            // Fallback to quadratic approximation
            return $this->simpleParabolaFit($data);
        }

        $detA = $sumY * ($sumX2 * $sumX4 - $sumX3 ** 2) - 
                $sumXY * ($sumX * $sumX4 - $sumX2 * $sumX3) + 
                $sumX2Y * ($sumX * $sumX3 - $sumX2 ** 2);

        $detB = $n * ($sumXY * $sumX4 - $sumX2Y * $sumX3) - 
                $sumY * ($sumX * $sumX4 - $sumX2 * $sumX3) + 
                $sumX2 * ($sumX * $sumX2Y - $sumXY * $sumX2);

        $detC = $n * ($sumX2 * $sumX2Y - $sumX3 * $sumXY) - 
                $sumX * ($sumX * $sumX2Y - $sumX2 * $sumXY) + 
                $sumY * ($sumX * $sumX3 - $sumX2 ** 2);

        return [
            'a' => $detA / $det,
            'b' => $detB / $det,
            'c' => $detC / $det
        ];
    }

    /**
     * Simple parabola fit fallback
     */
    private function simpleParabolaFit(array $data): array
    {
        // Use exponential approximation for lactate curve
        $firstY = $data[0]['y'];
        $lastY = end($data)['y'];
        $ratio = $lastY / $firstY;
        
        return [
            'a' => 0.001 * $ratio,
            'b' => 0.1,
            'c' => $firstY
        ];
    }

    /**
     * Calculate parabola value at x: y = ax² + bx + c
     */
    private function parabola(array $coeffs, float $x): float
    {
        return $coeffs['a'] * ($x ** 2) + $coeffs['b'] * $x + $coeffs['c'];
    }

    /**
     * Calculate line value at x: y = mx + b
     */
    private function line(array $lineParams, float $x): float
    {
        return $lineParams['slope'] * $x + $lineParams['intercept'];
    }

    /**
     * Find D-max point (maximum distance from curve to helper line)
     */
    private function computeDmax(array $data, array $coeffs, array $helperLine): array
    {
        $maxDistance = 0;
        $maxPoint = null;

        $minX = $data[0]['x'];
        $maxX = end($data)['x'];

        // Test 100 points between min and max
        for ($i = 0; $i <= 100; $i++) {
            $x = $minX + ($maxX - $minX) * ($i / 100);
            
            $curveY = $this->parabola($coeffs, $x);
            $lineY = $this->line($helperLine, $x);
            $distance = abs($curveY - $lineY);

            // Only consider points where curve is above line
            if ($curveY > $lineY && $distance > $maxDistance) {
                $maxDistance = $distance;
                $maxPoint = [
                    'x' => $x,
                    'y' => $curveY,
                    'distance' => $distance
                ];
            }
        }

        return $maxPoint ?: [
            'x' => $minX + ($maxX - $minX) * 0.6,
            'y' => $this->parabola($coeffs, $minX + ($maxX - $minX) * 0.6),
            'distance' => 0
        ];
    }

    /**
     * Find D-max Modified point (from aerobe threshold onwards)
     */
    private function computeDmaxModified(array $data, array $coeffs, array $helperLine, float $startX): array
    {
        $maxDistance = 0;
        $maxPoint = null;

        $maxX = end($data)['x'];

        // Test from aerobe point to end
        for ($i = 0; $i <= 100; $i++) {
            $x = $startX + ($maxX - $startX) * ($i / 100);
            
            $curveY = $this->parabola($coeffs, $x);
            $lineY = $this->line($helperLine, $x);
            $distance = abs($curveY - $lineY);

            // Only consider points where curve is above line
            if ($curveY > $lineY && $distance > $maxDistance) {
                $maxDistance = $distance;
                $maxPoint = [
                    'x' => $x,
                    'y' => $curveY,
                    'distance' => $distance
                ];
            }
        }

        return $maxPoint ?: [
            'x' => $startX + ($maxX - $startX) * 0.7,
            'y' => $this->parabola($coeffs, $startX + ($maxX - $startX) * 0.7),
            'distance' => 0
        ];
    }

    /**
     * Find X value for given Y (linear interpolation)
     */
    private function findXForY(array $data, float $targetY): float
    {
        for ($i = 0; $i < count($data) - 1; $i++) {
            $p1 = $data[$i];
            $p2 = $data[$i + 1];

            if ($p1['y'] <= $targetY && $p2['y'] >= $targetY) {
                $ratio = ($targetY - $p1['y']) / ($p2['y'] - $p1['y']);
                return $p1['x'] + $ratio * ($p2['x'] - $p1['x']);
            }
        }

        // Fallback to first point
        return $data[0]['x'];
    }
}