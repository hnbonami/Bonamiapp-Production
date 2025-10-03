<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? 'info@bonami-sportcoaching.be';
$plain = $argv[2] ?? 'password';

$user = App\Models\User::where('email', $email)->first();
if (! $user) {
    echo "NOT_FOUND\n";
    exit(0);
}

$data = [
    'id' => $user->id,
    'email' => $user->email,
    'role' => $user->role,
    'name' => $user->name,
    'avatar' => $user->avatar_path,
    'password_matches' => password_verify($plain, $user->password) ? true : false,
];

echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
