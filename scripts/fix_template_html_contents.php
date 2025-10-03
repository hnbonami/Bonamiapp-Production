<?php
// scripts/fix_template_html_contents.php

use Illuminate\Database\Capsule\Manager as DB;

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel DB connection (for standalone script)
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class);

// Fix all templates where html_contents is a string (not a JSON array)
$templates = DB::table('templates')->get();

foreach ($templates as $template) {
    $decoded = json_decode($template->html_contents, true);
    if (is_string($decoded)) {
        // Convert to array
        $fixed = json_encode([$decoded]);
        DB::table('templates')->where('id', $template->id)->update([
            'html_contents' => $fixed
        ]);
        echo "Template #{$template->id} fixed (string -> array)\n";
    } elseif (!is_array($decoded)) {
        // If not array or string, skip
        continue;
    }
}
echo "Klaar. Alle templates met string html_contents zijn nu arrays.\n";
