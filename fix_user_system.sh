#!/bin/bash

echo "🔧 FIXING USER SYSTEM - DIRECT SQL APPROACH"
echo "============================================"

# Use MySQL directly with Laravel database config
mysql -h 127.0.0.1 -u root -p bonamisportcoaching << 'EOF'

-- 1. Add user_id columns if they don't exist
ALTER TABLE klanten ADD COLUMN IF NOT EXISTS user_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE klanten ADD INDEX IF NOT EXISTS klanten_user_id_index (user_id);

ALTER TABLE medewerkers ADD COLUMN IF NOT EXISTS user_id BIGINT UNSIGNED NULL AFTER id;  
ALTER TABLE medewerkers ADD INDEX IF NOT EXISTS medewerkers_user_id_index (user_id);

-- 2. Create Users for existing Klanten
INSERT IGNORE INTO users (name, email, password, role, status, email_verified_at, created_at, updated_at)
SELECT 
    CONCAT(IFNULL(voornaam, ''), ' ', IFNULL(naam, '')) as name,
    email,
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' as password, -- 'password' hashed
    'klant' as role,
    'active' as status,
    NOW() as email_verified_at,
    NOW() as created_at,
    NOW() as updated_at
FROM klanten 
WHERE email IS NOT NULL 
AND email != ''
AND email NOT IN (SELECT email FROM users WHERE email IS NOT NULL);

-- 3. Create Users for existing Medewerkers  
INSERT IGNORE INTO users (name, email, password, role, status, email_verified_at, created_at, updated_at)
SELECT 
    CONCAT(IFNULL(voornaam, ''), ' ', IFNULL(naam, '')) as name,
    email,
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' as password, -- 'password' hashed
    'medewerker' as role,
    'active' as status,
    NOW() as email_verified_at,
    NOW() as created_at,
    NOW() as updated_at
FROM medewerkers 
WHERE email IS NOT NULL 
AND email != ''
AND email NOT IN (SELECT email FROM users WHERE email IS NOT NULL);

-- 4. Link Klanten to Users
UPDATE klanten k 
INNER JOIN users u ON k.email = u.email 
SET k.user_id = u.id 
WHERE k.email IS NOT NULL AND k.email != '' AND k.user_id IS NULL;

-- 5. Link Medewerkers to Users
UPDATE medewerkers m 
INNER JOIN users u ON m.email = u.email 
SET m.user_id = u.id 
WHERE m.email IS NOT NULL AND m.email != '' AND m.user_id IS NULL;

-- 6. Update User roles based on linked data
UPDATE users u 
INNER JOIN klanten k ON u.id = k.user_id 
SET u.role = 'klant' 
WHERE u.role != 'admin';

UPDATE users u 
INNER JOIN medewerkers m ON u.id = m.user_id 
SET u.role = 'medewerker' 
WHERE u.role != 'admin';

-- 7. Show final stats
SELECT 
    'FINAL STATS' as info,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') as admin_count,
    (SELECT COUNT(*) FROM users WHERE role = 'medewerker') as medewerker_count,
    (SELECT COUNT(*) FROM users WHERE role = 'klant') as klant_count,
    (SELECT COUNT(*) FROM klanten WHERE user_id IS NOT NULL) as klanten_linked,
    (SELECT COUNT(*) FROM medewerkers WHERE user_id IS NOT NULL) as medewerkers_linked;

EOF

echo ""
echo "✅ DONE! Check the stats above."
echo "Default password for all users: password"
echo ""
echo "Now refresh /admin/users to see the results!"