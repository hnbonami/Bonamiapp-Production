<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(1);
if (! $user) { echo "NO_USER\n"; exit(2); }

try {
    $html = view('profile.edit', ['user' => $user])->render();
    file_put_contents(__DIR__ . '/profile_render.html', $html);
    echo "RENDER_OK\n";
} catch (Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
