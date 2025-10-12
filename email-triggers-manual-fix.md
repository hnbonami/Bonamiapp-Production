# Handmatige Database Fix voor Email Triggers

## SQL Commando's om direct in database uit te voeren:

```sql
-- Voeg ontbrekende kolommen toe aan email_triggers
ALTER TABLE email_triggers 
ADD COLUMN trigger_key VARCHAR(255) NULL AFTER id,
ADD COLUMN trigger_type VARCHAR(255) DEFAULT 'scheduled' AFTER description,
ADD COLUMN trigger_data JSON NULL AFTER trigger_type,
ADD COLUMN conditions JSON NULL AFTER is_active,
ADD COLUMN settings JSON NULL AFTER conditions,
ADD COLUMN emails_sent INT DEFAULT 0 AFTER settings,
ADD COLUMN last_run_at TIMESTAMP NULL AFTER emails_sent,
ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER last_run_at;

-- Update bestaande triggers met basis types
UPDATE email_triggers 
SET trigger_type = 'welcome_customer',
    trigger_key = 'welcome_customer_trigger',
    trigger_data = '{"schedule": "daily", "time": "09:00"}'
WHERE name LIKE '%Welkom%Klanten%';

UPDATE email_triggers 
SET trigger_type = 'welcome_employee',
    trigger_key = 'welcome_employee_trigger', 
    trigger_data = '{"schedule": "daily", "time": "09:00"}'
WHERE name LIKE '%Welkom%Medewerkers%';

UPDATE email_triggers 
SET trigger_type = 'testzadel_reminder',
    trigger_key = 'testzadel_reminder_trigger',
    trigger_data = '{"schedule": "daily", "time": "10:00", "days_before_due": 7}'
WHERE name LIKE '%Testzadel%';

UPDATE email_triggers 
SET trigger_type = 'birthday',
    trigger_key = 'birthday_trigger',
    trigger_data = '{"schedule": "daily", "time": "09:00"}'
WHERE name LIKE '%Verjaardag%';

-- Update overige triggers
UPDATE email_triggers 
SET trigger_type = 'unknown',
    trigger_data = '{}'
WHERE trigger_type IS NULL;

-- Voeg unique constraint toe
ALTER TABLE email_triggers ADD UNIQUE KEY unique_trigger_key (trigger_key);
```

## Laravel Artisan Commando's:

```bash
# Voer migratie uit
php artisan migrate --path=database/migrations/2024_10_12_230000_repair_email_triggers_structure.php

# Test cleanup
php artisan email:cleanup-triggers --dry-run

# Fix triggers
php artisan email:cleanup-triggers --fix

# Seed nieuwe triggers (optioneel)
php artisan db:seed --class=EmailTriggerSeeder
```