<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\ReportTemplate::all() as $t) {
    echo $t->id . ' | ' . ($t->name ?? '(no name)') . ' | ' . ($t->slug ?? '(no slug)') . ' | ' . ($t->kind ?? '(no kind)') . ' | active:' . ($t->is_active ? '1' : '0') . "\n";
}
