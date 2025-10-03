<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Klant;
use App\Models\Bikefit;

// Create or find a test klant by email to avoid duplicate entries
$k = Klant::firstOrCreate(['email' => 'test+template@example.com'], [
    'voornaam' => 'T',
    'naam' => 'TemplateTester',
]);

$b = Bikefit::create([
    'klant_id' => $k->id,
    'datum' => now(),
    'testtype' => 'standard',
    'template_kind' => 'standaard_bikefit',
]);

echo "K:{$k->id} B:{$b->id} template:{$b->template_kind}\n";
