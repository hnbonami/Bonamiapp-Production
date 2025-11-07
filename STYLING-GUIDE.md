# 🎨 BONAMI UNIFORM STYLING SYSTEM

## Overzicht

Dit document beschrijft het uniforme styling systeem van de Bonami Sportcoaching applicatie. Alle styling is gecentraliseerd en consistent toegepast over de hele applicatie.

---

## 📁 Bestandsstructuur

```
public/
├── css/
│   ├── variables.css      # Alle CSS variabelen (kleuren, spacing, etc.)
│   ├── buttons.css        # Uniform button systeem
│   ├── components.css     # Cards, forms, tables, badges, alerts
│   ├── darkmode.css       # Dark mode styling en transitions
│   └── app.css           # App-specifieke styling
├── js/
│   └── darkmode.js       # Dark mode manager JavaScript
```

---

## 🎨 CSS Variabelen

Alle kleuren, spacing en andere design tokens zijn gedefinieerd in `variables.css`:

### Kleuren

```css
--bonami-blue: #00D4FF;        /* Primary brand color */
--bonami-blue-hover: #00BFEA;  /* Hover state */
--bonami-blue-active: #00AAD5; /* Active/pressed state */
--bonami-dark: #111111;        /* Dark text/backgrounds */
```

### Spacing

```css
--spacing-xs: 0.25rem;   /* 4px */
--spacing-sm: 0.5rem;    /* 8px */
--spacing-md: 1rem;      /* 16px */
--spacing-lg: 1.5rem;    /* 24px */
--spacing-xl: 2rem;      /* 32px */
--spacing-2xl: 3rem;     /* 48px */
```

### Border Radius

```css
--border-radius-sm: 4px;
--border-radius-md: 8px;
--border-radius-lg: 12px;
--border-radius-xl: 16px;
```

---

## 🔘 Button System

Gebruik altijd deze button classes voor consistente styling:

### Primary Button (Bonami Blue)
```html
<button class="btn btn-primary">Opslaan</button>
```

### Secondary Button
```html
<button class="btn btn-secondary">Annuleren</button>
```

### Danger Button
```html
<button class="btn btn-danger">Verwijderen</button>
```

### Success Button
```html
<button class="btn btn-success">Bevestigen</button>
```

### Outline Button
```html
<button class="btn btn-outline">Bekijken</button>
<button class="btn btn-outline-primary">Details</button>
```

### Button Sizes
```html
<button class="btn btn-primary btn-sm">Klein</button>
<button class="btn btn-primary">Normaal</button>
<button class="btn btn-primary btn-lg">Groot</button>
<button class="btn btn-primary btn-xl">Extra Groot</button>
```

### Icon Button
```html
<button class="btn btn-primary btn-icon">
    <i class="fas fa-plus"></i>
</button>
```

### Full Width Button
```html
<button class="btn btn-primary btn-block">Full Width</button>
```

---

## 🃏 Card System

### Basic Card
```html
<div class="card">
    <div class="card-header">
        Titel
    </div>
    <div class="card-body">
        Content hier
    </div>
    <div class="card-footer">
        Footer content
    </div>
</div>
```

### Card Variants
```html
<div class="card card-primary">...</div>
<div class="card card-success">...</div>
<div class="card card-danger">...</div>
<div class="card card-warning">...</div>
```

---

## 📝 Form Elements

Alle form elementen krijgen automatisch uniforme styling:

```html
<div class="form-group">
    <label class="form-label">Naam</label>
    <input type="text" class="form-control" placeholder="Voer naam in">
</div>

<div class="form-group">
    <label class="form-label">Beschrijving</label>
    <textarea class="form-control" rows="3"></textarea>
</div>

<div class="form-group">
    <label class="form-label">Categorie</label>
    <select class="form-control">
        <option>Optie 1</option>
        <option>Optie 2</option>
    </select>
</div>
```

---

## 📊 Tables

```html
<table class="table">
    <thead>
        <tr>
            <th>Naam</th>
            <th>Status</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Jan Janssen</td>
            <td><span class="badge badge-success">Actief</span></td>
            <td>
                <button class="btn btn-sm btn-primary">Bewerken</button>
            </td>
        </tr>
    </tbody>
</table>
```

### Striped Table
```html
<table class="table table-striped">...</table>
```

---

## 🏷️ Badges

```html
<span class="badge badge-primary">Nieuw</span>
<span class="badge badge-success">✅ Actief</span>
<span class="badge badge-danger">❌ Inactief</span>
<span class="badge badge-warning">⚠️ Pending</span>
<span class="badge badge-info">ℹ️ Info</span>
```

---

## 🌙 Dark Mode

### Automatische Dark Mode

Dark mode wordt automatisch toegepast op ALLE elementen die de CSS variabelen gebruiken. Geen extra classes nodig!

### Dark Mode Toggle

Een dark mode toggle button is automatisch beschikbaar op elke pagina (rechtsonder).

Keyboard shortcut: **Ctrl + Shift + D** (of Cmd + Shift + D op Mac)

### Dark Mode in Code

```javascript
// Check of dark mode actief is
if (window.darkModeManager && window.darkModeManager.isDarkMode()) {
    console.log('Dark mode is aan');
}

// Toggle dark mode programmatisch
window.darkModeManager.toggle();

// Enable dark mode
window.darkModeManager.enableDarkMode();

// Disable dark mode
window.darkModeManager.disableDarkMode();

// Luister naar dark mode events
document.addEventListener('darkmode-enabled', () => {
    console.log('Dark mode aangezet!');
});

document.addEventListener('darkmode-disabled', () => {
    console.log('Dark mode uitgezet!');
});
```

### Dark Mode Toggle Button Toevoegen

```html
<button data-dark-mode-toggle>
    <i class="fas fa-moon"></i>
    <span data-dark-mode-text>Dark Mode</span>
</button>
```

---

## 🚀 Implementatie in Bestaande Views

### Stap 1: Gebruik de Uniform Layout

```blade
@extends('layouts.uniform')

@section('title', 'Pagina Titel')

@section('content')
    <!-- Your content hier -->
@endsection
```

### Stap 2: Vervang Oude Button Classes

**Oud:**
```html
<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
    Opslaan
</button>
```

**Nieuw:**
```html
<button class="btn btn-primary">
    Opslaan
</button>
```

### Stap 3: Vervang Oude Card Styling

**Oud:**
```html
<div style="background: white; padding: 20px; border-radius: 8px;">
    Content
</div>
```

**Nieuw:**
```html
<div class="card">
    <div class="card-body">
        Content
    </div>
</div>
```

---

## ✅ Checklist voor Nieuwe Views

- [ ] Extend `layouts.uniform` layout
- [ ] Gebruik `btn btn-*` classes voor alle buttons
- [ ] Gebruik `card` classes voor containers
- [ ] Gebruik `form-control` voor inputs
- [ ] Gebruik `table` class voor tabellen
- [ ] Gebruik `badge badge-*` voor status indicators
- [ ] Test in zowel light als dark mode
- [ ] Check responsiveness op mobile

---

## 🎯 Best Practices

1. **Gebruik ALTIJD CSS variabelen** in plaats van hardcoded kleuren
2. **Gebruik de button classes** - maak geen custom button styling
3. **Test in beide modes** - check altijd light EN dark mode
4. **Consistent spacing** - gebruik de spacing variabelen
5. **Toegankelijkheid** - zorg dat buttons altijd labels hebben
6. **Mobile first** - test altijd op mobile formaat

---

## 🐛 Troubleshooting

### Dark mode werkt niet?

1. Check of `darkmode.js` geladen is
2. Check of de CSS variabelen geladen zijn
3. Kijk in de browser console voor errors
4. Check of `<html>` element de `.dark` class krijgt

### Buttons hebben geen styling?

1. Check of `buttons.css` geladen is
2. Gebruik je wel de juiste classes? (bijv. `btn btn-primary`)
3. Is er conflicterende CSS?

### Kleuren kloppen niet?

1. Check of `variables.css` als eerste geladen wordt
2. Gebruik je CSS variabelen (bijv. `var(--bonami-blue)`)?
3. Is er oude inline styling die overschrijft?

---

## 📞 Support

Bij vragen of problemen: check dit document eerst, of raadpleeg de dark-mode-test.html voor voorbeelden.

Laatste update: {{ now()->format('d-m-Y') }}
