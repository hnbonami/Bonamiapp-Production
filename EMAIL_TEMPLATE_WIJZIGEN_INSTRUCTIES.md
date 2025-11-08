# 🎨 Email Templates Wijzigen - Instructies

## 🎯 Wanneer gebruik je welke methode?

### ✅ Optie 1: Via Admin Panel (AANBEVOLEN)

**Wanneer gebruiken:**
- Je wilt snel een wijziging testen
- Je wilt een template aanpassen voor jouw organisatie
- Je wilt een custom template maken

**Hoe:**
1. Ga naar `/admin/email/templates`
2. Klik op **"Bewerken"** bij de template die je wilt wijzigen
3. Pas de HTML/CSS aan
4. Sla op

**Let op:** Dit wijzigt alleen JOUW organisatie template (custom template)

---

### 🔄 Optie 2: Reset Standaard Templates (SUPERADMIN)

**Wanneer gebruiken:**
- Je hebt de seeder aangepast met nieuwe wijzigingen
- Je wilt alle standaard Performance Pulse templates updaten
- Je wilt de laatste versie van de templates

**Hoe:**
1. Pas de seeder aan: `database/seeders/DefaultEmailTemplatesSeeder.php`
2. Ga naar `/admin/email/templates`
3. Klik op **"Reset Standaard Templates"** (oranje knop, alleen zichtbaar voor superadmin)
4. Bevestig de actie

**Resultaat:**
- ✅ Alle standaard templates (is_default = 1) worden bijgewerkt
- ✅ Custom organisatie templates blijven behouden
- ✅ Nieuwe wijzigingen uit seeder worden toegepast

---

### 👁️ Optie 3: Preview Functie

**Gebruik:**
1. Klik op **"Preview"** bij een template
2. Opent in nieuw tabblad
3. Toont template met demo data

**Demo placeholders:**
- @{{voornaam}} → Jan
- @{{naam}} → Janssen
- @{{email}} → jan@voorbeeld.nl
- @{{bedrijf_naam}} → Bonami Sportcoaching
- etc.

---

## 🛠️ Workflow voor Wijzigingen

### Scenario 1: Kleine Tekstwijziging

```
1. Admin Panel → Templates
2. Klik "Bewerken"
3. Wijzig tekst in editor
4. Klik "Preview" om te checken
5. Sla op
```

**Tijd:** ~2 minuten ✅

---

### Scenario 2: Nieuwe Features/Styling in Seeder

```
1. Edit: database/seeders/DefaultEmailTemplatesSeeder.php
   
   private function getWelcomeCustomerTemplate(): string
   {
       $logoUrl = asset('images/performance-pulse-logo.png');
       return '<!DOCTYPE html>
       ...
       [JE WIJZIGINGEN HIER]
       ...
       
2. Ga naar Admin Panel
3. Klik "Reset Standaard Templates" (oranje knop)
4. Check preview van templates
```

**Tijd:** ~5 minuten ✅

---

### Scenario 3: Logo Wijzigen

**In de seeder:**
```php
$logoUrl = asset('images/performance-pulse-logo.png');
```

**Vervang met nieuw logo:**
```php
$logoUrl = asset('images/new-logo.png');
```

**Dan:**
1. Upload nieuw logo naar `public/images/new-logo.png`
2. Klik "Reset Standaard Templates"
3. Klaar! ✅

---

## 📊 Template Types Overzicht

| Type | Gebruik | Wanneer verzonden |
|------|---------|-------------------|
| `welcome_customer` | Nieuwe klant aangemaakt | Bij klant registratie |
| `welcome_employee` | Nieuwe medewerker | Bij medewerker uitnodiging |
| `testzadel_reminder` | Testzadel herinnering | Automatisch/handmatig |
| `birthday` | Verjaardag | Automatisch op verjaardag |
| `referral_thank_you` | Bedankt doorverwijzing | Bij doorverwijzing |
| `general_notification` | Algemene update | Handmatig |

---

## 🎨 HTML/CSS Tips

### Logo aanpassen:
```html
<img src="' . $logoUrl . '" 
     alt="Performance Pulse Logo" 
     style="max-width: 70px; height: auto; margin-bottom: 15px;">
```

**Grootte wijzigen:**
- `max-width: 70px` → `max-width: 100px` (groter)
- `max-width: 70px` → `max-width: 50px` (kleiner)

### Kleuren wijzigen:
```css
background: linear-gradient(135deg, #c8e1eb 0%, #a8d5e2 100%);
```

**Andere kleuren:**
- Performance Pulse blauw: `#c8e1eb`
- Donkerder blauw: `#a8d5e2`
- Tekst: `#1a1a1a`

### Button styling:
```css
.button { 
    background: #c8e1eb; 
    color: #1a1a1a; 
    padding: 14px 28px; 
}
```

---

## ⚠️ Belangrijke Placeholders

**Altijd beschikbaar:**
```
@{{voornaam}}           - Voornaam klant
@{{naam}}               - Achternaam klant
@{{email}}              - Email adres
@{{bedrijf_naam}}       - Naam organisatie
@{{jaar}}               - Huidig jaar
@{{website_url}}        - Website URL
@{{unsubscribe_url}}    - Uitschrijf link
```

**Template specifiek:**
```
Welcome:
@{{temporary_password}} - Tijdelijk wachtwoord

Testzadel:
@{{merk}}              - Zadel merk
@{{model}}             - Zadel model
@{{uitgeleend_op}}     - Datum uitgeleend

Birthday:
@{{leeftijd}}          - Leeftijd
```

---

## 🚀 Quick Commands

### Via Terminal (als je dat liever hebt):
```bash
# Reset templates via command line
php artisan db:seed --class=DefaultEmailTemplatesSeeder

# Check hoeveel standaard templates er zijn
php artisan tinker
>>> EmailTemplate::where('is_default', 1)->count();

# Bekijk alle template types
>>> EmailTemplate::where('is_default', 1)->pluck('name', 'type');
```

---

## ✅ Checklist na Wijzigingen

- [ ] Preview gecontroleerd
- [ ] Test email verstuurd
- [ ] Logo zichtbaar
- [ ] Placeholders werken
- [ ] Responsive op mobile
- [ ] Kleuren kloppen
- [ ] Links werken
- [ ] Geen spelfouten

---

## 🆘 Troubleshooting

**Logo niet zichtbaar?**
```bash
# Check of logo bestaat:
ls -la public/images/performance-pulse-logo.png

# Als niet: upload logo naar public/images/
```

**Wijzigingen niet zichtbaar?**
```
1. Check of je de juiste template bewerkt (standaard vs custom)
2. Klik "Reset Standaard Templates" voor seeder wijzigingen
3. Clear browser cache (Cmd+Shift+R)
```

**Placeholders niet vervangen?**
```
Gebruik altijd: @{{voornaam}} (met @{{}})
Niet: {{voornaam}} of @{voornaam}
```

---

## 🎉 Klaar!

Nu kun je gemakkelijk email templates wijzigen via het admin panel! 🚀

**Snelste workflow:**
1. Bewerk template in admin panel
2. Preview checken
3. Test email sturen
4. Klaar! ✅
