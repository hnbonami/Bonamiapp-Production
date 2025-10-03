<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;

$email = $argv[1] ?? 'info@bonami-sportcoaching.be';
$plain = $argv[2] ?? 'password';

$user = App\Models\User::where('email', $email)->first();
if (! $user) {
    echo "NOT_FOUND\n";
    exit(2);
}

$user->password = Hash::make($plain);
$user->save();

echo json_encode(['id' => $user->id, 'email' => $user->email, 'password_set' => true]) . "\n";
