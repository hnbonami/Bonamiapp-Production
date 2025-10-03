<?php


// Helper functions for the application

if (!function_exists('format_date')) {
    /**
     * Format a date for display
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    function format_date($date, $format = 'd-m-Y')
    {
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('calculate_bmi')) {
    /**
     * Calculate BMI from height and weight
     *
     * @param float $weight_kg
     * @param int $height_cm
     * @return float|null
     */
    function calculate_bmi($weight_kg, $height_cm)
    {
        if (!$weight_kg || !$height_cm || $height_cm <= 0) {
            return null;
        }
        
        $height_m = $height_cm / 100;
        return round($weight_kg / ($height_m * $height_m), 1);
    }
}

// Global helpers for the application.

if (!function_exists('report_template_kinds')) {
    function report_template_kinds(): array
    {
        return [
            'inspanningstest_fietsen' => 'Inspanningstest - Fietsen',
            'inspanningstest_lopen' => 'Inspanningstest - Lopen',
            'standaard_bikefit' => 'Standaard Bikefit',
            'professionele_bikefit' => 'Professionele Bikefit',
            'zadeldrukmeting' => 'Zadeldrukmeting',
            'maatbepaling' => 'Maatbepaling',
        ];
    }
}

