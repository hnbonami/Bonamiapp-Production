<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bikefit;
use App\Services\BikefitReportGenerator;

$id = $argv[1] ?? null;
if (!$id) {
    echo "Usage: php generate_bikefit_pdf.php {bikefit_id}\n";
    exit(1);
}

$bikefit = Bikefit::find($id);
if (!$bikefit) {
    echo "Bikefit $id not found\n";
    exit(1);
}

$gen = new BikefitReportGenerator();
try {
    $path = $gen->savePdf($bikefit);
    echo "Saved PDF to: $path\n";
} catch (Throwable $e) {
    echo "PDF generation failed: " . $e->getMessage() . "\n";
}
