<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/app.php';
$kernel = app()->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;

// Ensure there is a user and klant and bikefit
$user = \App\Models\User::first();
if (!$user) {
    $user = \App\Models\User::factory()->create(['email' => 'u'.time().'@example.com']);
}
$k = \App\Models\Klant::first() ?: \App\Models\Klant::factory()->create(['voornaam'=>'T','naam'=>'U','email'=>'t'.time().'@example.com']);
$bf = \App\Models\Bikefit::create(['klant_id'=>$k->id,'testtype'=>'test']);

// Create a small tmp file to simulate uploaded file
$tmp = sys_get_temp_dir() . '/test_upload_'.time().'.tmp';
file_put_contents($tmp, 'dummy');
$uploaded = new UploadedFile($tmp, 'test.jpg', null, null, true);

$req = Request::create('/uploads', 'POST', [
    'bikefit_id' => $bf->id,
    'caption' => 'Attached via test',
], [], [], []);

// Make the request return our test user when ->user() is called
$req->setUserResolver(function () use ($user) {
    return $user;
});

$req->files->set('file', $uploaded);

$controller = new \App\Http\Controllers\UserUploadController();
$res = $controller->store($req);
echo "Response: ";
print_r($res->getContent());
