<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTablesSeeder extends Seeder
{
    public function run()
    {
        // Create tables via Laravel Schema instead of raw SQL
        
        // 1. Create role_permissions table
        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function ($table) {
                $table->id();
                $table->string('role_name');
                $table->string('tab_name');
                $table->boolean('can_access')->default(false);
                $table->timestamps();
                $table->unique(['role_name', 'tab_name']);
                $table->index(['role_name', 'can_access']);
            });
            echo "✅ role_permissions table created\n";
        }

        // 2. Create role_test_permissions table
        if (!Schema::hasTable('role_test_permissions')) {
            Schema::create('role_test_permissions', function ($table) {
                $table->id();
                $table->string('role_name');
                $table->string('test_type');
                $table->boolean('can_access')->default(false);
                $table->boolean('can_create')->default(false);
                $table->boolean('can_edit')->default(false);
                $table->timestamps();
                $table->unique(['role_name', 'test_type']);
                $table->index(['role_name', 'can_access']);
            });
            echo "✅ role_test_permissions table created\n";
        }

        // 3. Create user_login_logs table
        if (!Schema::hasTable('user_login_logs')) {
            Schema::create('user_login_logs', function ($table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamp('login_at');
                $table->timestamp('logout_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->integer('session_duration')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'login_at']);
                $table->index('login_at');
            });
            echo "✅ user_login_logs table created\n";
        }

        // 4. Add columns to users table
        Schema::table('users', function ($table) {
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
                echo "✅ last_login_at column added\n";
            }
            if (!Schema::hasColumn('users', 'login_count')) {
                $table->integer('login_count')->default(0)->after('last_login_at');
                echo "✅ login_count column added\n";
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('login_count');
                echo "✅ status column added\n";
            }
            if (!Schema::hasColumn('users', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('status');
                echo "✅ admin_notes column added\n";
            }
        });

        echo "\n🎯 Now running permissions seeder...\n\n";

        // 5. Run the permissions seeder
        $this->call(RolePermissionsSeeder::class);

        echo "\n✅ ALL DONE! Permission system ready.\n";
    }
}