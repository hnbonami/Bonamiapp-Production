<?php

namespace App\Services;

class BikefitCalculatorWrapper {
    
    public static function renderScaledMobilityTable($bikefit) {
        $calculator = app(\App\Services\BikefitCalculator::class);
        $originalHtml = $calculator->renderMobilityTableHtml($bikefit);
        
        // Wrap in scaling div
        return '<div style="transform: scale(0.7); transform-origin: top left; font-size: 12px; margin-bottom: -30%; width: 142.86%; overflow: hidden;">' 
               . $originalHtml . 
               '</div>';
    }
}