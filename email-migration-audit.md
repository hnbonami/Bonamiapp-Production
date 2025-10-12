# Email Systeem Migratie Audit - Bonami Sportcoaching

## 🎯 Doel
Veilige migratie van alle oude email systemen naar het nieuwe uniforme Email Admin systeem.

## ✅ Voltooide Stappen

### Infrastructuur
- [x] `EmailMigrationService` aangemaakt voor veilige migratie
- [x] `MigrateEmailSystemCommand` Artisan command aangemaakt
- [x] Admin interface toegevoegd voor migratie management
- [x] Legacy routes omgeleid naar nieuwe systeem

### Routes Gemigreerd
- [x] `/mailtest` → `/mailtest-legacy` (redirect naar Email Admin)
- [x] `/debug/birthday-test` → legacy variant met redirect
- [x] `/debug/testzadel-reminder-test` → legacy variant met redirect
- [x] Nieuwe routes: `/admin/email/migration`

## 📋 Volgende Stappen

### Uit te Voeren
1. **Voer migratie uit**: `php artisan email:migrate --dry-run`
2. **Test nieuwe systeem**: `php artisan email:migrate --test`
3. **Volledige migratie**: `php artisan email:migrate`
4. **Update controllers handmatig**:
   - `BirthdayController` - vervang Mail:: calls
   - `TestzadelsController` - vervang reminder systeem
   - `KlantenController` - vervang invite emails
   - `MedewerkerController` - vervang invite emails

### Controllers Te Updaten
- [ ] `BirthdayController::sendManual()` - gebruik EmailAdmin service
- [ ] `TestzadelsController::sendReminder()` - gebruik EmailAdmin triggers
- [ ] `KlantenController::sendInvitation()` - gebruik EmailAdmin templates
- [ ] `MedewerkerController::sendInvitation()` - gebruik EmailAdmin templates

### Templates Te Migreren
- [ ] `emails.birthday` → Email Admin template
- [ ] `emails.testzadel-reminder` → Email Admin template
- [ ] Eventuele andere email views

## 🔧 Migratie Uitvoeren - Stap voor Stap

### Stap 1: Database Migratie
```bash
# Voer eerst de database migratie uit
php artisan migrate

# Dit voegt de benodigde kolommen toe:
# - template_key kolom aan email_templates
# - trigger_key kolom aan email_triggers  
# - content kolom aan email_templates
# - trigger_type, trigger_data kolommen aan email_triggers
```

### Stap 2: Test Migratie (Dry Run)
```bash
# Test wat er zou gebeuren zonder wijzigingen
php artisan email:migrate --dry-run
```

### Stap 3: Migreer Templates
```bash
# Migreer alleen templates
php artisan email:migrate --templates
```

### Stap 4: Migreer Triggers
```bash
# Migreer alleen triggers  
php artisan email:migrate --triggers
```

### Stap 5: Test Systeem
```bash
# Test het nieuwe systeem
php artisan email:migrate --test
```

### Stap 6: Volledige Migratie
```bash
# Voer complete migratie uit
php artisan email:migrate
```

## 🔍 Troubleshooting

### Probleem: "Geen templates gemigreerd"
**Oplossing:** 
1. Controleer of database migratie is uitgevoerd: `php artisan migrate`
2. Check logs in `storage/logs/laravel.log`
3. Probeer admin interface: `/admin/email/migration`

### Probleem: EmailTemplate/EmailTrigger model niet gevonden
**Oplossing:**
1. Zorg dat de modellen bestaan in `app/Models/`
2. Check namespace imports in EmailMigrationService
3. Voer `composer dump-autoload` uit

### Probleem: Templates kunnen niet gerenderd worden
**Oplossing:**
De migratie service maakt nu standaard templates aan als oude Blade views niet bestaan.

## 🌐 Admin Interface

Ga naar `/admin/email/migration` voor:
- Status overzicht van oude vs nieuwe systemen
- Migratie knoppen voor verschillende componenten
- Real-time feedback over migratie status

## ⚠️ Belangrijke Opmerkingen

### Veiligheid
- Altijd backup maken voor migratie
- Test in lokale omgeving eerst
- Gebruik `--dry-run` voor veilige test
- Controleer email logs na migratie

### Na Migratie
- Verwijder oude email routes
- Update documentatie
- Train team op nieuwe Email Admin systeem
- Monitor email delivery performance

## 🎯 Voordelen Nieuwe Systeem

✅ **Centrale email management**  
✅ **Email logs en tracking**  
✅ **Template versioning**  
✅ **Automatische triggers**  
✅ **A/B testing mogelijkheden**  
✅ **Unsubscribe management**  
✅ **Performance monitoring**

---
*Status: Infrastructuur klaar, klaar voor uitvoering van migratie*