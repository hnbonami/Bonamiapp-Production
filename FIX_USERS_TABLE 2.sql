# DIRECT SQL COMMANDS om de kolommen toe te voegen
# Voer deze uit in je database (via phpMyAdmin, TablePlus, of terminal)

USE bonamispportcoaching;

# Voeg alle ontbrekende kolommen toe aan users tabel
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255) NULL AFTER email_verified_at,
ADD COLUMN IF NOT EXISTS geboortedatum DATE NULL AFTER avatar_path,
ADD COLUMN IF NOT EXISTS adres VARCHAR(255) NULL AFTER geboortedatum,
ADD COLUMN IF NOT EXISTS stad VARCHAR(255) NULL AFTER adres,
ADD COLUMN IF NOT EXISTS postcode VARCHAR(10) NULL AFTER stad,
ADD COLUMN IF NOT EXISTS telefoon VARCHAR(255) NULL AFTER postcode;

# Check de tabel structuur
DESCRIBE users;