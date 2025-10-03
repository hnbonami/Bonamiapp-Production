<?php
// Simple bootstrap script to run a smoke test for report generation
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/app.php';

$kernel = app()->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $k = \App\Models\Klant::first();
    if (!$k) {
        $k = \App\Models\Klant::factory()->create(['voornaam'=>'T','naam'=>'U','email'=>'t'.time().'@example.com']);
        echo "Created klant {$k->id}\n";
    }

    $bf = \App\Models\Bikefit::create(['klant_id' => $k->id, 'testtype' => 'test']);
    echo "Created bikefit {$bf->id}\n";

    // create an image record pointing to a public asset if present
    $samplePath = 'public/test-asset.jpg';
    $bf->images()->create(['path' => $samplePath, 'caption' => 'Test foto', 'position' => 0, 'is_cover' => 1]);

    $path = app(\App\Services\BikefitReportGenerator::class)->savePdf($bf);
    echo "PDF saved to: {$path}\n";
} catch (\Throwable $e) {
    echo "Error: " . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
