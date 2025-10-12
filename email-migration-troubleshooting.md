# Email Migratie Fix Script - Bonami Sportcoaching

## 🚨 Migratie Conflict Oplossen

### Probleem
De email_templates tabel bestaat al, maar de migratie probeert deze opnieuw aan te maken.

### Oplossing 1: Veilige Migratie (AANBEVOLEN)

```bash
# Ga naar project directory
cd /Users/hannesbonami/Desktop/Bonamiapp

# Check welke migraties zijn uitgevoerd
php artisan migrate:status

# Voer alleen de nieuwe, veilige migraties uit
php artisan migrate --path=database/migrations/2024_10_12_220000_fix_email_templates_table.php
php artisan migrate --path=database/migrations/2024_10_12_210000_create_email_triggers_table.php
php artisan migrate --path=database/migrations/2024_10_12_220001_fix_email_logs_table.php
php artisan migrate --path=database/migrations/2024_10_12_220002_create_email_subscriptions_table.php

# Seed de email data
php artisan db:seed --class=EmailTriggerSeeder

# Test het systeem
php artisan email:migrate --test
```

### Oplossing 2: Reset Migraties (ALLEEN ALS NODIG)

⚠️ **WAARSCHUWING**: Dit verwijdert ALLE data uit email tabellen!

```bash
# Backup maken
mysqldump -u [username] -p [database] > backup_$(date +%Y%m%d_%H%M%S).sql

# Drop email tabellen handmatig
mysql -u [username] -p [database] -e "DROP TABLE IF EXISTS email_templates, email_triggers, email_logs, email_subscriptions;"

# Verwijder migratie entries
php artisan migrate:rollback --step=5

# Voer migraties opnieuw uit
php artisan migrate

# Seed data
php artisan db:seed --class=EmailTriggerSeeder
```

### Oplossing 3: Handmatige Database Fix

```sql
-- Voeg ontbrekende kolommen toe aan email_templates
ALTER TABLE email_templates 
ADD COLUMN template_key VARCHAR(255) NULL UNIQUE AFTER id,
ADD COLUMN content LONGTEXT NULL AFTER body_html,
ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER is_active;

-- Maak email_triggers tabel
CREATE TABLE IF NOT EXISTS email_triggers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trigger_key VARCHAR(255) NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    trigger_type VARCHAR(255) DEFAULT 'scheduled',
    trigger_data JSON NULL,
    email_template_id BIGINT UNSIGNED NULL,
    is_active BOOLEAN DEFAULT TRUE,
    conditions JSON NULL,
    settings JSON NULL,
    emails_sent INT DEFAULT 0,
    last_run_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (email_template_id) REFERENCES email_templates(id) ON DELETE SET NULL
);

-- Maak email_subscriptions tabel
CREATE TABLE IF NOT EXISTS email_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    subscriber_type VARCHAR(255) NOT NULL,
    subscriber_id BIGINT UNSIGNED NULL,
    status VARCHAR(255) DEFAULT 'subscribed',
    unsubscribe_token VARCHAR(255) UNIQUE NULL,
    unsubscribe_reason TEXT NULL,
    subscribed_at TIMESTAMP NULL,
    unsubscribed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## ✅ Verificatie

Na het uitvoeren van een van de oplossingen:

```bash
# Test de email triggers
php artisan email:migrate --triggers

# Test de admin interface
# Ga naar: http://127.0.0.1:8000/admin/email/triggers

# Test trigger bewerking
# Probeer een testzadel trigger aan te passen
```

## 🎯 Verwacht Resultaat

- ✅ Email templates tabel heeft alle benodigde kolommen
- ✅ Email triggers tabel bestaat en is gevuld
- ✅ Email logs werken correct
- ✅ Trigger bewerking werkt (testzadel dagen aanpassen)
- ✅ Admin interface toont alle triggers correct

---

**Aanbeveling**: Start met Oplossing 1 (veilige migratie) omdat deze geen data verliest.