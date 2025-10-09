#!/bin/bash

echo "=== DIRECTE SQL UITVOERING ==="

# Voer onze tabellen direct uit via mysql
mysql -u root -p bonamisportcoaching << 'EOF'

-- Create role_permissions table
CREATE TABLE IF NOT EXISTS role_permissions (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    role_name varchar(255) NOT NULL,
    tab_name varchar(255) NOT NULL,
    can_access tinyint(1) NOT NULL DEFAULT 0,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY role_permissions_role_name_tab_name_unique (role_name, tab_name),
    KEY role_permissions_role_name_can_access_index (role_name, can_access)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create role_test_permissions table  
CREATE TABLE IF NOT EXISTS role_test_permissions (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    role_name varchar(255) NOT NULL,
    test_type varchar(255) NOT NULL,
    can_access tinyint(1) NOT NULL DEFAULT 0,
    can_create tinyint(1) NOT NULL DEFAULT 0,
    can_edit tinyint(1) NOT NULL DEFAULT 0,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY role_test_permissions_role_name_test_type_unique (role_name, test_type),
    KEY role_test_permissions_role_name_can_access_index (role_name, can_access)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_login_logs table
CREATE TABLE IF NOT EXISTS user_login_logs (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint unsigned NOT NULL,
    login_at timestamp NOT NULL,
    logout_at timestamp NULL DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    session_duration int DEFAULT NULL,
    user_agent varchar(255) DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY user_login_logs_user_id_login_at_index (user_id, login_at),
    KEY user_login_logs_login_at_index (login_at),
    CONSTRAINT user_login_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add tracking fields to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS last_login_at timestamp NULL AFTER email_verified_at,
ADD COLUMN IF NOT EXISTS login_count int NOT NULL DEFAULT 0 AFTER last_login_at,
ADD COLUMN IF NOT EXISTS status enum('active','inactive','suspended') NOT NULL DEFAULT 'active' AFTER login_count,
ADD COLUMN IF NOT EXISTS admin_notes text NULL AFTER status;

EOF

echo "=== TABELLEN AANGEMAAKT ==="
echo "Nu seeder uitvoeren..."

php artisan db:seed --class=RolePermissionsSeeder

echo "=== KLAAR! ==="