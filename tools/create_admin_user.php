<?php
require __DIR__ . '/../vendor/autoload.php';
use Illuminate\Support\Facades\Hash;
use App\Models\User;

$email = 'info@bonami-sportcoaching.be';
$password = 'passord';
$name = 'Admin';
$role = 'admin';
$user = User::where('email', $email)->first();
if ($user) {
    echo json_encode(['status' => 'exists', 'id' => $user->id, 'email' => $user->email]) . "\n";
    exit(0);
}
$user = new User();
$user->name = $name;
$user->email = $email;
$user->password = Hash::make($password);
$user->role = $role;
$user->save();
echo json_encode(['status' => 'created', 'id' => $user->id, 'email' => $user->email]) . "\n";


$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'info@bonami-sportcoaching.be';
$password = 'passord';
$name = 'Admin';
$role = 'admin';

$user = User::where('email', $email)->first();
if ($user) {
    echo json_encode(['status' => 'exists', 'id' => $user->id, 'email' => $user->email]) . "\n";
    exit(0);
}

$user = new User();
$user->name = $name;
$user->email = $email;
$user->password = Hash::make($password);
$user->role = $role;
$user->save();

echo json_encode(['status' => 'created', 'id' => $user->id, 'email' => $user->email]) . "\n";
