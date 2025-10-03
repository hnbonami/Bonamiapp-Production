<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'info@bonamisportcoaching.be',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        // Check if admin user already exists
        $existingAdmin = User::where('email', 'info@bonami-sportcoaching.be')->first();
        
        if ($existingAdmin) {
            // Update existing user to ensure admin privileges
            $existingAdmin->update([
                'name' => 'Bonami Admin',
                'email' => 'info@bonami-sportcoaching.be',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin', // Ensure admin role
                'is_admin' => true, // Set admin flag if it exists
            ]);
            
            $this->command->info('✅ Admin user updated: info@bonami-sportcoaching.be');
        } else {
            // Create new admin user
            User::create([
                'name' => 'Bonami Admin',
                'email' => 'info@bonami-sportcoaching.be',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin', // Set admin role
                'is_admin' => true, // Set admin flag if it exists
            ]);
            
            $this->command->info('✅ Admin user created: info@bonami-sportcoaching.be');
        }
        
        $this->command->info('📧 Email: info@bonami-sportcoaching.be');
        $this->command->info('🔐 Password: password');
        $this->command->info('🔑 Role: admin (full access)');
    }
}