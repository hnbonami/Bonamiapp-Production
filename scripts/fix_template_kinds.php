<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$map = [
    'inspanning-fietsen' => 'inspanningstest_fietsen',
    'inspanning-lopen' => 'inspanningstest_lopen',
    'bikefit-standard' => 'standaard_bikefit',
    'bikefit-pro' => 'professionele_bikefit',
    'zadeldruk' => 'zadeldrukmeting',
    'maatbepaling' => 'maatbepaling',
];

foreach($map as $slug => $kind) {
    $t = \App\Models\ReportTemplate::where('slug', $slug)->first();
    if ($t && ($t->kind ?? '') !== $kind) {
        $t->kind = $kind;
        $t->save();
        echo "Updated {$slug} -> {$kind}\n";
    }
}

echo "Done\n";
