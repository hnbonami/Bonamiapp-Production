<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class MakeAdminUser extends Command
{
    protected $signature = 'make:admin-user';
    protected $description = 'Maak een admin gebruiker aan met standaard e-mail en wachtwoord';

    public function handle()
    {
        $email = 'info@bonami-sportcoaching.be';
        $password = 'passord';
        $name = 'Admin';
        $role = 'admin';

        $user = User::where('email', $email)->first();
        if ($user) {
            $this->info('Gebruiker bestaat al: ' . $user->email);
            return 0;
        }

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->role = $role;
        $user->save();

        $this->info('Admin gebruiker aangemaakt: ' . $user->email);
        return 0;
    }
}
