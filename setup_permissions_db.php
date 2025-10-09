<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Direct database setup script - omzeilt migratie problemen
 */

// Controleer of tabellen bestaan, zo niet maak ze aan
function createTablesDirectly() {
    try {
        // 1. Role Permissions Table
        if (!Schema::hasTable('role_permissions')) {
            DB::statement("
                CREATE TABLE role_permissions (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    role_name VARCHAR(255) NOT NULL,
                    tab_name VARCHAR(255) NOT NULL,
                    can_access BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    UNIQUE KEY unique_role_tab (role_name, tab_name),
                    INDEX idx_role_access (role_name, can_access)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            echo "✅ role_permissions tabel aangemaakt\n";
        } else {
            echo "ℹ️ role_permissions tabel bestaat al\n";
        }

        // 2. Role Test Permissions Table  
        if (!Schema::hasTable('role_test_permissions')) {
            DB::statement("
                CREATE TABLE role_test_permissions (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    role_name VARCHAR(255) NOT NULL,
                    test_type VARCHAR(255) NOT NULL,
                    can_access BOOLEAN DEFAULT FALSE,
                    can_create BOOLEAN DEFAULT FALSE,
                    can_edit BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    UNIQUE KEY unique_role_test (role_name, test_type),
                    INDEX idx_role_test_access (role_name, can_access)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            echo "✅ role_test_permissions tabel aangemaakt\n";
        } else {
            echo "ℹ️ role_test_permissions tabel bestaat al\n";
        }

        // 3. User Login Logs Table
        if (!Schema::hasTable('user_login_logs')) {
            DB::statement("
                CREATE TABLE user_login_logs (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    login_at TIMESTAMP NOT NULL,
                    logout_at TIMESTAMP NULL,
                    ip_address VARCHAR(45) NULL,
                    session_duration INT NULL,
                    user_agent TEXT NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_user_login (user_id, login_at),
                    INDEX idx_login_at (login_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            echo "✅ user_login_logs tabel aangemaakt\n";
        } else {
            echo "ℹ️ user_login_logs tabel bestaat al\n";
        }

        // 4. Add columns to users table if they don't exist
        if (!Schema::hasColumn('users', 'last_login_at')) {
            DB::statement("ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP NULL AFTER email_verified_at");
            echo "✅ last_login_at kolom toegevoegd aan users\n";
        }

        if (!Schema::hasColumn('users', 'login_count')) {
            DB::statement("ALTER TABLE users ADD COLUMN login_count INT DEFAULT 0 AFTER last_login_at");
            echo "✅ login_count kolom toegevoegd aan users\n";
        }

        if (!Schema::hasColumn('users', 'status')) {
            DB::statement("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' AFTER login_count");
            echo "✅ status kolom toegevoegd aan users\n";
        }

        if (!Schema::hasColumn('users', 'admin_notes')) {
            DB::statement("ALTER TABLE users ADD COLUMN admin_notes TEXT NULL AFTER status");
            echo "✅ admin_notes kolom toegevoegd aan users\n";
        }

        return true;

    } catch (Exception $e) {
        echo "❌ Fout bij aanmaken tabellen: " . $e->getMessage() . "\n";
        return false;
    }
}

// Voeg default permissions toe
function seedDefaultPermissions() {
    try {
        // Check if data already exists
        if (DB::table('role_permissions')->count() > 0) {
            echo "ℹ️ Permissions data bestaat al\n";
            return true;
        }

        $tabs = ['dashboard', 'klanten', 'medewerkers', 'instagram', 'nieuwsbrief', 'sjablonen', 'testzadels', 'admin'];
        $testTypes = ['bikefit', 'inspanningstest_fietsen', 'inspanningstest_lopen', 'voedingsadvies', 'zadeldrukmeting', 'maatbepaling'];

        // Admin - alles toegestaan
        foreach ($tabs as $tab) {
            DB::table('role_permissions')->insert([
                'role_name' => 'admin',
                'tab_name' => $tab,
                'can_access' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        foreach ($testTypes as $test) {
            DB::table('role_test_permissions')->insert([
                'role_name' => 'admin',
                'test_type' => $test,
                'can_access' => true,
                'can_create' => true,
                'can_edit' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Medewerker - beperkte toegang
        $medewerkerTabs = ['dashboard', 'klanten', 'instagram', 'nieuwsbrief', 'sjablonen', 'testzadels'];
        foreach ($medewerkerTabs as $tab) {
            DB::table('role_permissions')->insert([
                'role_name' => 'medewerker',
                'tab_name' => $tab,
                'can_access' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        foreach ($testTypes as $test) {
            DB::table('role_test_permissions')->insert([
                'role_name' => 'medewerker',
                'test_type' => $test,
                'can_access' => true,
                'can_create' => true,
                'can_edit' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Klant - alleen dashboard
        DB::table('role_permissions')->insert([
            'role_name' => 'klant',
            'tab_name' => 'dashboard',
            'can_access' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        foreach ($testTypes as $test) {
            DB::table('role_test_permissions')->insert([
                'role_name' => 'klant',
                'test_type' => $test,
                'can_access' => true,
                'can_create' => false,
                'can_edit' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        echo "✅ Default permissions toegevoegd\n";
        return true;

    } catch (Exception $e) {
        echo "❌ Fout bij seeden: " . $e->getMessage() . "\n";
        return false;
    }
}

// Main execution
echo "🚀 Starting direct database setup...\n\n";

if (createTablesDirectly()) {
    echo "\n";
    if (seedDefaultPermissions()) {
        echo "\n🎉 Database setup succesvol voltooid!\n";
        echo "\nJe kunt nu doorgaan met:\n";
        echo "- Routes toevoegen\n";
        echo "- Views maken\n";
        echo "- Admin interface gebruiken\n";
    }
} else {
    echo "\n❌ Setup gefaald. Check de foutmeldingen hierboven.\n";
}