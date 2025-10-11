## 🔍 **EMAIL TRIGGER DIAGNOSE**

**Voer deze commando's uit om te checken:**

```bash
cd /Users/hannesbonami/Desktop/Bonamiapp
php artisan tinker
```

**Check 1: Welke email triggers bestaan er?**
```php
$triggers = \App\Models\EmailTrigger::all();
echo "=== EMAIL TRIGGERS ===\n";
foreach($triggers as $trigger) {
    echo "ID: {$trigger->id} | Name: {$trigger->name} | Type: {$trigger->type} | Active: " . ($trigger->is_active ? 'YES' : 'NO') . "\n";
}
echo "\n";
```

**Check 2: Welke email templates bestaan er?**
```php
$templates = \App\Models\EmailTemplate::all();
echo "=== EMAIL TEMPLATES ===\n";
foreach($templates as $template) {
    echo "ID: {$template->id} | Name: {$template->name} | Type: {$template->type} | Active: " . ($template->is_active ? 'YES' : 'NO') . "\n";
}
echo "\n";
```

**Check 3: Zijn er welcome templates gekoppeld aan triggers?**
```php
$welcomeTemplates = \App\Models\EmailTemplate::whereIn('type', ['welcome_customer', 'welcome_employee'])->get();
echo "=== WELCOME TEMPLATES ===\n";
foreach($welcomeTemplates as $template) {
    $trigger = \App\Models\EmailTrigger::where('email_template_id', $template->id)->first();
    echo "Template: {$template->name} | Has Trigger: " . ($trigger ? 'YES' : 'NO') . "\n";
}
exit
```

## 🎯 **DIAGNOSE RESULTATEN:**

✅ **Email triggers bestaan:** JA - alle 4 triggers zijn aanwezig
✅ **Welcome triggers actief:** JA - welcome_customer & welcome_employee bestaan  
❌ **HOOFDPROBLEEM GEVONDEN:** Mail driver = `null` ❌

## 🚨 **KRITIEK PROBLEEM: MAIL CONFIGURATIE**

**Mail driver is `null` - daarom werken emails niet!**

**Check ook deze commando's:**
```bash
php artisan tinker
```

```php
// Check volledige mail config
config('mail.default')
config('mail.mailers.smtp')
config('mail.from')
```

## **MOGELIJKE OORZAKEN:**
1. ❌ **MAIL_DRIVER** niet ingesteld in `.env` file
2. ❌ **MAIL_MAILER** niet ingesteld in `.env` file  
3. ❌ Mail configuratie incomplete
4. ❌ .env file niet correct geladen

## **OPLOSSING:**
Check je `.env` file voor mail instellingen zoals:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=jouw@email.com
MAIL_PASSWORD=jouw_wachtwoord
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=jouw@email.com
MAIL_FROM_NAME="Bonami Sportcoaching"
```