# Upload Toegangsrechten Implementatie

## ✅ Wat is toegevoegd:

### 1. Database Migration
- **Bestand**: `database/migrations/add_access_rights_to_uploads_table.php`
- **Kolom**: `toegang` (varchar 50) met default waarde `alle_medewerkers`

### 2. Upload Model (`app/Models/Upload.php`)
- **Constanten** voor toegangsrechten:
  - `TOEGANG_ALLEEN_MEZELF` - alleen de uploader
  - `TOEGANG_KLANT` - klant + uploader
  - `TOEGANG_ALLE_MEDEWERKERS` - alle medewerkers/admins
  - `TOEGANG_IEDEREEN` - publiek toegankelijk
  
- **Methodes**:
  - `getToegangsOpties()` - array met alle opties voor dropdowns
  - `heeftToegang($user)` - check of gebruiker toegang heeft

### 3. UploadController (`app/Http/Controllers/UploadController.php`)
- **Validatie** toegevoegd voor `toegang`, `naam`, `beschrijving`
- **show()** - controleert toegang voordat document wordt getoond
- **upload()** - slaat toegangsrechten op bij nieuwe uploads
- **destroy()** - controleert of gebruiker mag verwijderen (uploader of admin)
- **Logging** voor security audit trail

### 4. Upload Form (`resources/views/uploads/form.blade.php`)
- **Dropdown** met toegangsrechten + uitleg
- **Verbeterde UI** met betere styling en error handling
- **Extra velden**: naam en beschrijving

## 🚀 Installatie instructies:

### Stap 1: Run de migration
```bash
php artisan migrate
```

### Stap 2: Test het systeem
1. Upload een document via de form
2. Selecteer verschillende toegangsrechten
3. Test of documenten alleen zichtbaar zijn voor de juiste gebruikers

## 🔒 Hoe werken de toegangsrechten?

### Alleen mezelf
- Alleen de uploader kan het document zien/openen
- Admin heeft altijd toegang

### Klant + mezelf
- De uploader kan het document zien
- De gekoppelde klant kan het document zien (via klant_id)
- Admin heeft altijd toegang

### Alle medewerkers
- Alle gebruikers met role `medewerker` of `admin` kunnen het document zien
- De uploader kan het document zien
- **Standaard waarde** voor nieuwe uploads

### Iedereen
- Alle ingelogde gebruikers kunnen het document zien
- Gebruik voor algemene documenten/templates

## 📊 Beveiliging features:

1. **Toegangscontrole** - `heeftToegang()` check in Upload model
2. **Logging** - alle toegangspogingen worden gelogd
3. **403 errors** - bij ongeautoriseerde toegang
4. **Verwijder rechten** - alleen uploader of admin kan verwijderen
5. **Validatie** - input wordt gevalideerd voor security

## 🎨 UI Verbeteringen:

- Duidelijke labels en uitleg bij elke optie
- Error messages onder velden
- Annuleren knop
- Betere file input styling
- Responsive design

## 📝 Voorbeeld gebruik in Blade views:

```php
@foreach($uploads as $upload)
    @if($upload->heeftToegang(auth()->user()))
        <a href="{{ route('uploads.show', $upload) }}">
            {{ $upload->naam ?? 'Document' }}
        </a>
        <span class="text-xs text-gray-500">
            ({{ \App\Models\Upload::getToegangsOpties()[$upload->toegang] }})
        </span>
    @endif
@endforeach
```

## 🔧 Volgende stappen (optioneel):

1. **Email notificaties** - waarschuw klanten als er een document voor hen is
2. **Download tracking** - log wie wanneer documenten download
3. **Expiratie datums** - automatisch verwijderen na X maanden
4. **Bulk upload** - meerdere bestanden tegelijk uploaden met dezelfde rechten
5. **Gedeelde links** - tijdelijke toegangslinks genereren

## ⚠️ Let op:

- Bestaande uploads krijgen automatisch `alle_medewerkers` als toegangsrecht
- Test grondig met verschillende user roles
- Check of klanten daadwerkelijk een `klant_id` hebben in de users tabel voor "Klant + mezelf" optie
