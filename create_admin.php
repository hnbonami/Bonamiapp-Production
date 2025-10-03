<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Admin',
    'email' => 'info@bonamisportcoaching.be',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
]);

echo "Admin user created successfully!\n";