# AVATAR DEBUG SCRIPT - Lokaal vs Productie

## Run deze commando's in Tinker om het probleem te vinden:

```php
// 1. Check de huidige user
$user = Auth::user();
dd([
    'user_id' => $user->id,
    'user_role' => $user->role,
    'klant_id' => $user->klant_id,
    'user_avatar' => $user->avatar,
    'user_avatar_path' => $user->avatar_path,
]);

// 2. Als user een klant_id heeft, check de klant record
if ($user->klant_id) {
    $klant = \App\Models\Klant::find($user->klant_id);
    dd([
        'klant_id' => $klant->id,
        'klant_avatar' => $klant->avatar,
        'klant_avatar_path' => $klant->avatar_path,
        'updated_at' => $klant->updated_at,
    ]);
}

// 3. Check of het bestand bestaat op verschillende locaties
$avatarPath = 'avatars/klanten/FILENAME.jpg'; // Vervang FILENAME

// Lokaal checken:
dd([
    'storage_public_exists' => \Storage::disk('public')->exists($avatarPath),
    'storage_public_path' => storage_path('app/public/' . $avatarPath),
    'file_exists_storage' => file_exists(storage_path('app/public/' . $avatarPath)),
]);

// Productie checken (alleen op server runnen):
dd([
    'httpd_www_path' => base_path('../httpd.www/uploads/' . $avatarPath),
    'file_exists_httpd' => file_exists(base_path('../httpd.www/uploads/' . $avatarPath)),
    'avatars_disk_exists' => \Storage::disk('avatars')->exists(str_replace('avatars/', '', $avatarPath)),
]);

// 4. Check alle klanten met avatars
$klantenMetAvatars = \App\Models\Klant::whereNotNull('avatar_path')->get(['id', 'voornaam', 'naam', 'avatar_path', 'updated_at']);
dd($klantenMetAvatars->toArray());
```

## Het probleem

Ik zie het probleem! In **app.blade.php topbar** wordt ALTIJD `asset('storage/' . $avatar)` gebruikt:

```php
// TOPBAR (app.blade.php) - FOUT in productie
@if($avatarUrl)
    <img id="topbar-avatar" src="{{ $avatarUrl }}" ...>
@endif

// Maar $avatarUrl wordt zo berekend:
if ($avatar) {
    $avatarUrl = app()->environment('production') 
        ? asset('uploads/' . $avatar)  // ✅ CORRECT voor productie
        : asset('storage/' . $avatar);  // ✅ CORRECT voor lokaal
}
```

Maar dit gebruikt de **oude logica** waarbij `$avatar` de waarde van `avatar` kolom is, NIET `avatar_path`!

## DE OPLOSSING

In **KlantController** en **ProfileController** wordt de avatar opgeslagen in de kolom `avatar_path`, maar in **app.blade.php** wordt nog steeds de oude `avatar` kolom gebruikt!

We moeten app.blade.php aanpassen om `avatar_path` te gebruiken in plaats van `avatar`.
