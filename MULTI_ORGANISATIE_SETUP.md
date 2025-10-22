# 🚀 Multi-Organisatie Setup - Stap voor Stap

## ✅ Wat is er net aangemaakt?

### 1. Database Structuur
- ✅ `organisaties` tabel - voor het beheren van alle organisaties
- ✅ `organisatie_id` kolom in `users` tabel
- ✅ `organisatie_id` kolom in `klanten` tabel
- ✅ `role` kolom in `users` tabel (superadmin, organisatie_admin, medewerker, klant)

### 2. Models & Logica
- ✅ `Organisatie` model - met relaties en helper methods
- ✅ `BelongsToOrganisatie` trait - voor automatische filtering
- ✅ User model uitgebreid met role checks
- ✅ Klant model uitgebreid met organisatie filtering

### 3. Beveiliging
- ✅ `CheckSuperAdmin` middleware
- ✅ `CheckOrganisatieAdmin` middleware
- ✅ Automatische data isolatie per organisatie

### 4. Data Seeder
- ✅ OrganisatieSeeder - koppelt je bestaande data aan Bonami organisatie

---

## 📝 Installatie Instructies

### Stap 1: Draai de Migrations

```bash
php artisan migrate
```

Dit maakt de nieuwe tabellen en kolommen aan.

### Stap 2: Draai de Seeder

```bash
php artisan db:seed --class=OrganisatieSeeder
```

Dit doet het volgende:
- Maakt "Bonami Sportcoaching" organisatie aan
- Koppelt al je bestaande users aan Bonami
- Koppelt al je bestaande klanten aan Bonami
- Maakt een superadmin account aan

**Login gegevens superadmin:**
- Email: `admin@bonamisportcoaching.nl`
- Wachtwoord: `password` ⚠️ **VERANDER DIT DIRECT!**

### Stap 3: Registreer de Middleware

Open `bootstrap/app.php` en voeg toe:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'superadmin' => \App\Http\Middleware\CheckSuperAdmin::class,
        'organisatie_admin' => \App\Http\Middleware\CheckOrganisatieAdmin::class,
    ]);
})
```

### Stap 4: Test de Setup

1. Log in met je normale account
2. Je zou alleen je eigen klanten moeten zien (automatisch gefilterd!)
3. Log in als superadmin (admin@bonamisportcoaching.nl)
4. Als superadmin zie je alles

---

## 🔧 Volgende Stappen

### Direct nodig (deze week):

1. **Voeg organisatie_id toe aan meer tabellen:**
   - `bikefits`
   - `inspanningstests`
   - `sjablonen`
   - `testzadels`
   
   Kopieer gewoon de migration `2024_01_01_000003_add_organisatie_id_to_klanten_table.php`
   en pas de tabelnaam aan!

2. **Voeg trait toe aan meer models:**
   Open elk model (Bikefit, Inspanningstest, etc.) en voeg toe:
   ```php
   use App\Models\Traits\BelongsToOrganisatie;
   
   class Bikefit extends Model
   {
       use BelongsToOrganisatie;
       // ...rest van code
   }
   ```

3. **Verander wachtwoord van superadmin:**
   ```bash
   php artisan tinker
   ```
   Vervolgens:
   ```php
   $user = User::where('email', 'admin@bonamisportcoaching.nl')->first();
   $user->password = Hash::make('jouw-veilige-wachtwoord');
   $user->save();
   ```

### Later deze maand:

4. **Maak SuperAdmin Dashboard** (zie volgende stap in roadmap)
5. **Maak organisatie aanmaak formulier**
6. **Test met demo organisatie**

---

## 🧪 Testen

### Test 1: Data Isolatie
```bash
php artisan tinker
```

```php
// Als normale user
$klanten = Klant::all(); // Ziet alleen eigen organisatie klanten

// Als superadmin
Auth::loginUsingId(1); // ID van superadmin
$klanten = Klant::alleOrganisaties()->get(); // Ziet alle klanten
```

### Test 2: Automatische Toewijzing
```php
Auth::loginUsingId(2); // Een normale user
$klant = Klant::create([
    'naam' => 'Test',
    'voornaam' => 'Klant',
    'email' => 'test@test.nl',
    // organisatie_id wordt automatisch ingevuld!
]);

dd($klant->organisatie_id); // Moet je organisatie_id zijn
```

---

## 🎯 Wat Werkt Nu Al?

✅ Klanten worden automatisch gefilterd per organisatie
✅ Nieuwe klanten krijgen automatisch de juiste organisatie_id
✅ Superadmin kan alles zien
✅ Normale users zien alleen hun eigen data
✅ Role-based toegang werkt (isSuperAdmin(), isBeheerder(), etc.)

---

## ❓ Problemen?

### "Column not found: organisatie_id"
Draai migrations opnieuw: `php artisan migrate:fresh --seed`
⚠️ Dit wist je database! Maak eerst een backup!

### "Geen klanten zichtbaar"
Check of je user een organisatie_id heeft:
```bash
php artisan tinker
User::find(auth()->id())->organisatie_id
```

### "Trait werkt niet"
Controleer of je model de trait gebruikt:
```php
use App\Models\Traits\BelongsToOrganisatie;

class JouwModel extends Model
{
    use BelongsToOrganisatie;
}
```

---

## 📞 Volgende Sessie

Laat me weten wanneer je klaar bent met deze stap, dan maken we:
1. SuperAdmin dashboard voor organisatie beheer
2. Organisatie aanmaak formulier
3. Migrations voor de overige tabellen (bikefits, etc.)

**Succes met de setup! 🎉**
