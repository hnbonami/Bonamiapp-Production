#!/bin/bash

echo "🔧 DIRECT DATABASE FIX"
echo "====================="

# Get database credentials from .env
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
DB_PORT=$(grep DB_PORT .env | cut -d '=' -f2)
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)

echo "Connecting to database: $DB_DATABASE"

# Execute SQL directly
mysql -h${DB_HOST} -P${DB_PORT} -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} << 'EOF'

-- Step 1: Add user_id columns if they don't exist
SET @sql1 = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'klanten' AND column_name = 'user_id' AND table_schema = DATABASE()) > 0, 'SELECT "user_id column already exists in klanten"', 'ALTER TABLE klanten ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER id');
PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @sql2 = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'medewerkers' AND column_name = 'user_id' AND table_schema = DATABASE()) > 0, 'SELECT "user_id column already exists in medewerkers"', 'ALTER TABLE medewerkers ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER id');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Step 2: Show current counts
SELECT 'BEFORE SYNC:' as status;
SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM klanten) as total_klanten,
    (SELECT COUNT(*) FROM medewerkers) as total_medewerkers,
    (SELECT COUNT(*) FROM klanten WHERE user_id IS NOT NULL) as klanten_linked,
    (SELECT COUNT(*) FROM medewerkers WHERE user_id IS NOT NULL) as medewerkers_linked;

-- Step 3: Create users for all klanten who don't have one
INSERT IGNORE INTO users (name, email, password, role, status, email_verified_at, created_at, updated_at)
SELECT 
    CONCAT(COALESCE(voornaam, ''), ' ', COALESCE(naam, '')) as name,
    COALESCE(email, CONCAT('klant', id, '@temp.local')) as email,
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' as password,
    'klant' as role,
    'active' as status,
    NOW() as email_verified_at,
    NOW() as created_at,
    NOW() as updated_at
FROM klanten k
WHERE NOT EXISTS (
    SELECT 1 FROM users u WHERE u.email = k.email AND k.email IS NOT NULL AND k.email != ''
)
OR k.email IS NULL 
OR k.email = '';

-- Step 4: Create users for all medewerkers who don't have one
INSERT IGNORE INTO users (name, email, password, role, status, email_verified_at, created_at, updated_at)
SELECT 
    CONCAT(COALESCE(voornaam, ''), ' ', COALESCE(naam, '')) as name,
    COALESCE(email, CONCAT('medewerker', id, '@temp.local')) as email,
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' as password,
    'medewerker' as role,
    'active' as status,
    NOW() as email_verified_at,
    NOW() as created_at,
    NOW() as updated_at
FROM medewerkers m
WHERE NOT EXISTS (
    SELECT 1 FROM users u WHERE u.email = m.email AND m.email IS NOT NULL AND m.email != ''
)
OR m.email IS NULL 
OR m.email = '';

-- Step 5: Link klanten to users by email
UPDATE klanten k 
INNER JOIN users u ON (k.email = u.email AND k.email IS NOT NULL AND k.email != '')
SET k.user_id = u.id 
WHERE k.user_id IS NULL;

-- Step 6: Link klanten to users by generated email for those without email
UPDATE klanten k 
INNER JOIN users u ON u.email = CONCAT('klant', k.id, '@temp.local')
SET k.user_id = u.id 
WHERE k.user_id IS NULL AND (k.email IS NULL OR k.email = '');

-- Step 7: Link medewerkers to users by email
UPDATE medewerkers m 
INNER JOIN users u ON (m.email = u.email AND m.email IS NOT NULL AND m.email != '')
SET m.user_id = u.id 
WHERE m.user_id IS NULL;

-- Step 8: Link medewerkers to users by generated email for those without email
UPDATE medewerkers m 
INNER JOIN users u ON u.email = CONCAT('medewerker', m.id, '@temp.local')
SET m.user_id = u.id 
WHERE m.user_id IS NULL AND (m.email IS NULL OR m.email = '');

-- Step 9: Show final results
SELECT 'AFTER SYNC:' as status;
SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') as admin_count,
    (SELECT COUNT(*) FROM users WHERE role = 'medewerker') as medewerker_count,
    (SELECT COUNT(*) FROM users WHERE role = 'klant') as klant_count,
    (SELECT COUNT(*) FROM klanten WHERE user_id IS NOT NULL) as klanten_linked,
    (SELECT COUNT(*) FROM medewerkers WHERE user_id IS NOT NULL) as medewerkers_linked;

EOF

echo ""
echo "✅ DATABASE SYNC COMPLETE!"
echo "Now check /admin/users - it should show all users correctly."