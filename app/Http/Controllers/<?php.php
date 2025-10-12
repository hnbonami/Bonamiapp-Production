<?php
use App\Models\User;
$user = User::where('email', 'emergency@bonami.be')->first();
echo "Name: " . $user->name;
echo "Email: " . $user->email;
echo "Role: " . $user->role;
echo "Is Admin: " . $user->is_admin;
exit