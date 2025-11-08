# 📧 Multi-tenant Email Template Systeem - Implementatie Instructies

## 🎯 Overzicht

Dit systeem maakt het mogelijk om:
- **Standaard Performance Pulse templates** te gebruiken voor alle organisaties
- **Custom templates** toe te staan voor organisaties met 'custom_emails' feature
- **Automatische fallback** van custom naar standaard templates

---

## 🚀 Stap 1: Database Migratie Uitvoeren

```bash
php artisan migrate
```

Dit voegt de volgende kolommen toe aan `email_templates`:
- `organisatie_id` - Koppeling met organisatie (NULL = standaard template)
- `is_default` - Markeer als Performance Pulse standaard template
- `parent_template_id` - Optionele parent template relatie

**✅ Veilig:** Deze migratie voegt alleen kolommen toe, verwijdert geen data.

---

## 🌱 Stap 2: Standaard Templates Seeden

```bash
php artisan db:seed --class=DefaultEmailTemplatesSeeder
```

Dit maakt 6 standaard Performance Pulse templates aan:
1. ✉️ **Welcome Customer** - Klant welkom email
2. 👋 **Welcome Employee** - Medewerker welkom email
3. 🚴 **Testzadel Reminder** - Herinnering testzadel
4. 🎂 **Birthday** - Verjaardagswensen
5. 🙏 **Referral Thank You** - Bedankt voor doorverwijzing
6. 📢 **General Notification** - Algemene notificatie

**Features:**
- 📱 Responsive design (mobile-friendly)
- 🎨 Performance Pulse branding (#c8e1eb kleuren)
- 🔧 Placeholder support (@{{voornaam}}, @{{bedrijf_naam}}, etc.)
- ✅ Modern, clean HTML

---

## 📊 Hoe het Systeem Werkt

### Template Hierarchie

```
1. Check: Heeft organisatie custom template?
   └─ JA → Gebruik custom template ✨
   └─ NEE → Fallback naar Performance Pulse standaard 📧

2. Check: Is Performance Pulse standaard beschikbaar?
   └─ JA → Gebruik standaard template
   └─ NEE → Error (geen template gevonden)
```

### Code Voorbeeld

```php
// Automatische template selectie met fallback
$template = EmailTemplate::findTemplateWithFallback('welcome_customer', $organisatieId);

// Dit zoekt eerst custom template, dan fallback naar standaard
```

---

## 🎨 Standaard Template Design

**Performance Pulse Stijl:**
- **Header:** Gradient blauw (#c8e1eb → #a8d5e2)
- **Content:** Clean, leesbaar, moderne fonts
- **Buttons:** Performance Pulse blauw (#c8e1eb)
- **Footer:** Subtiele grijze achtergrond
- **Responsive:** Werkt perfect op mobile

**Voorbeeld:**
```html
<!DOCTYPE html>
<html>
<head>
    <style>
        .header { background: linear-gradient(135deg, #c8e1eb 0%, #a8d5e2 100%); }
        .button { background-color: #c8e1eb; color: #1a1a1a; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>⚡ Performance Pulse</h1>
        </div>
        <div class="content">
            <h2>Welkom, @{{voornaam}}!</h2>
            <p>...</p>
        </div>
    </div>
</body>
</html>
```

---

## 🔧 Bestaande Templates Migreren (Optioneel)

Als je bestaande templates hebt die je wilt behouden als custom templates:

```php
// In tinker of een seeder:
php artisan tinker

// Voorbeeld: Maak bestaande template organisatie-specifiek
$template = EmailTemplate::find(1);
$template->organisatie_id = 1; // Jouw organisatie ID
$template->is_default = false;
$template->save();
```

---

## 📝 Template Badges in UI

De UI toont nu badges voor elk template type:

- **🟢 Actief / 🔴 Inactief** - Template status
- **📧 Performance Pulse Standaard** - Standaard template voor iedereen
- **✨ Custom Template** - Organisatie-specifieke template

---

## 🛠️ Voor Ontwikkelaars

### Model Methods

```php
// Check of template standaard is
$template->isDefaultTemplate(); // true/false

// Check of template custom is
$template->isCustomTemplate(); // true/false

// Scopes
EmailTemplate::default()->get(); // Alleen standaard templates
EmailTemplate::forOrganisatie($orgId)->get(); // Organisatie templates
EmailTemplate::activeForType('welcome_customer')->get(); // Actieve templates voor type
```

### Service Methods Zijn Automatisch Geüpdatet

```php
// Alle send methods gebruiken nu automatisch fallback systeem
$emailService->sendWelcomeEmail($customer); // ✅ Auto fallback
$emailService->sendTestzadelReminderEmail($klant, $vars); // ✅ Auto fallback
$emailService->sendBirthdayEmail($klant, $vars); // ✅ Auto fallback
```

---

## ⚙️ Feature Toggle Integratie

### Hoe Custom Emails Toestaan

```php
// In je feature toggle systeem:
FeatureToggle::enable('custom_emails', $organisatieId);

// Nu kan deze organisatie:
// - Custom templates maken
// - Standaard templates overerven en aanpassen
// - Eigen branding toevoegen
```

### UI Aanpassingen (Toekomstig)

Je kunt in de UI checken:

```blade
@if(auth()->user()->hasFeature('custom_emails'))
    <!-- Toon "Template Bewerken" knop -->
    <a href="{{ route('admin.email.templates.edit', $template->id) }}">
        Bewerken
    </a>
@else
    <!-- Toon upgrade badge -->
    <span class="badge">🔒 Upgrade voor aanpassingen</span>
@endif
```

---

## 🧪 Testen

### Test Standaard Template

```bash
php artisan tinker

# Test welcome customer email
$klant = App\Models\Klant::first();
$emailService = app(App\Services\EmailIntegrationService::class);
$emailService->sendWelcomeEmail($klant);

# Check logs voor template selectie
tail -f storage/logs/laravel.log
```

### Expected Log Output

```
✅ Standaard Performance Pulse template gebruikt
   type: welcome_customer
   template_id: 123
```

---

## 📊 Database Schema Wijzigingen

### Voor Migratie

```
email_templates
├── id
├── name
├── type
├── subject
├── body_html
├── description
├── is_active
├── created_at
└── updated_at
```

### Na Migratie

```
email_templates
├── id
├── organisatie_id          ← NIEUW (nullable, indexed)
├── name
├── type
├── subject
├── body_html
├── description
├── is_active
├── is_default              ← NIEUW (boolean, default false)
├── parent_template_id      ← NIEUW (nullable)
├── created_at
└── updated_at
```

---

## 🔄 Rollback Instructies

Mocht je terug willen naar de oude situatie:

```bash
php artisan migrate:rollback --step=1
```

**⚠️ Waarschuwing:** Dit verwijdert de nieuwe kolommen, maar bestaande templates blijven behouden.

---

## ✅ Checklist

- [ ] `php artisan migrate` uitgevoerd
- [ ] `php artisan db:seed --class=DefaultEmailTemplatesSeeder` uitgevoerd
- [ ] Standaard templates zichtbaar in admin panel
- [ ] Test email verzonden met standaard template
- [ ] Logs controleren voor correcte template selectie
- [ ] Feature toggle 'custom_emails' geconfigureerd (optioneel)

---

## 🎉 Voltooid!

Je multi-tenant email template systeem is nu klaar voor gebruik!

**Voordelen:**
- ✅ Professionele Performance Pulse templates voor iedereen
- ✅ Organisaties kunnen custom templates maken (met feature toggle)
- ✅ Automatische fallback = geen broken emails
- ✅ Backwards compatible met bestaande code
- ✅ Schaalbaar en onderhoudbaar

**Vragen?** Check de logs of neem contact op met het dev team! 🚀
