# 🔒 Security & Authorization Implementation Plan
# Bonami Sportcoaching App - Productie Beveiliging

## 📋 Inhoudsopgave
1. [Huidige Situatie](#huidige-situatie)
2. [Rollen & Permissies Matrix](#rollen--permissies-matrix)
3. [Beveiligings Checklist](#beveiligings-checklist)
4. [Implementatie Plan](#implementatie-plan)
5. [Testing Strategy](#testing-strategy)

---

## 🚨 Huidige Situatie - Security Risks

### **KRITIEKE PROBLEMEN:**

#### 1. **Geen Route Protection**
- ❌ Klanten kunnen `/admin` routes bezoeken
- ❌ Klanten kunnen `/klanten` zien (alle klanten inzien)
- ❌ Klanten kunnen `/users` beheren
- ❌ Klanten kunnen `/testzadels` beheren
- ❌ Geen controle op organisatie_id (cross-organisatie data toegang mogelijk!)

#### 2. **Geen Controller Authorization**
- ❌ Controllers checken niet expliciet op gebruikersrol
- ❌ Geen `authorize()` checks in controllers
- ❌ Geen Policy classes voor models

#### 3. **Database Query Vulnerabilities**
- ❌ Queries filteren niet altijd op `organisatie_id`
- ❌ Direct model access zonder scope checks
- ❌ Mass assignment vulnerabilities mogelijk

---

## 👥 Rollen & Permissies Matrix

### **Rolhiërarchie:**
```
SuperAdmin (role: 'superadmin')
    └── Admin (role: 'admin') 
        └── Medewerker (role: 'medewerker')
            └── Klant (role: 'klant')
```

### **Permissie Matrix:**

| Feature | SuperAdmin | Admin | Medewerker | Klant |
|---------|-----------|-------|------------|-------|
| **Organisaties** |
| Alle organisaties bekijken | ✅ | ❌ | ❌ | ❌ |
| Organisatie aanmaken | ✅ | ❌ | ❌ | ❌ |
| Organisatie bewerken | ✅ | ❌ | ❌ | ❌ |
| **Gebruikers** |
| Alle users in organisatie | ✅ | ✅ | ❌ | ❌ |
| Users aanmaken | ✅ | ✅ | ❌ | ❌ |
| Users bewerken | ✅ | ✅ | ❌ | ❌ |
| Users verwijderen | ✅ | ✅ | ❌ | ❌ |
| **Klanten** |
| Alle klanten bekijken | ✅ | ✅ | ✅ | ❌ |
| Eigen profiel bekijken | ✅ | ✅ | ✅ | ✅ |
| Klant aanmaken | ✅ | ✅ | ✅ | ❌ |
| Klant bewerken | ✅ | ✅ | ✅ | ❌ |
| Klant verwijderen | ✅ | ✅ | ❌ | ❌ |
| **Bikefits** |
| Bikefits bekijken | ✅ | ✅ | ✅ | Alleen eigen |
| Bikefit aanmaken | ✅ | ✅ | ✅ | ❌ |
| Bikefit bewerken | ✅ | ✅ | ✅ | ❌ |
| Bikefit verwijderen | ✅ | ✅ | ❌ | ❌ |
| **Inspanningstesten** |
| Inspanningstesten bekijken | ✅ | ✅ | ✅ | Alleen eigen |
| Inspanningstest aanmaken | ✅ | ✅ | ✅ | ❌ |
| Inspanningstest bewerken | ✅ | ✅ | ✅ | ❌ |
| Inspanningstest verwijderen | ✅ | ✅ | ❌ | ❌ |
| **Medewerkers** |
| Medewerkers bekijken | ✅ | ✅ | ❌ | ❌ |
| Medewerker aanmaken | ✅ | ✅ | ❌ | ❌ |
| Medewerker bewerken | ✅ | ✅ | ❌ | ❌ |
| **Testzadels** |
| Testzadels beheren | ✅ | ✅ | ❌ | ❌ |
| **Sjablonen** |
| Sjablonen beheren | ✅ | ✅ | ❌ | ❌ |
| **Prestaties** |
| Eigen prestaties bekijken | ✅ | ✅ | ✅ | ❌ |
| Alle prestaties bekijken | ✅ | ✅ | ❌ | ❌ |
| Prestaties aanmaken | ✅ | ✅ | ✅ | ❌ |
| Prestaties bewerken | ✅ | ✅ | ✅ | ❌ |
| Prestaties goedkeuren | ✅ | ✅ | ❌ | ❌ |
| **Commissies (Prestaties)** |
| Commissies bekijken | ✅ | ✅ | ❌ | ❌ |
| Commissies aanmaken | ✅ | ✅ | ❌ | ❌ |
| Commissies bewerken | ✅ | ✅ | ❌ | ❌ |
| Commissie uitbetalingen | ✅ | ✅ | ❌ | ❌ |
| **Branding** |
| Branding bekijken | ✅ | ✅ | ❌ | ❌ |
| Branding bewerken | ✅ | ✅ | ❌ | ❌ |
| Custom CSS/Logo's uploaden | ✅ | ✅ | ❌ | ❌ |
| **Rechten & Rollen Beheer** |
| Gebruikersrollen bekijken | ✅ | ✅ | ✅ | ❌ |
| Gebruikersrollen wijzigen | ✅ | ✅ | ✅ | ❌ |
| Permissies beheren | ✅ | ✅ | ✅ | ❌ |
| Nieuwe rollen aanmaken | ✅ | ✅ | ❌ | ❌ |
| **Email Beheer** |
| Email templates bekijken | ✅ | ✅ | ✅ | ❌ |
| Email templates bewerken | ✅ | ✅ | ✅ | ❌ |
| Email settings configureren | ✅ | ✅ | ✅ | ❌ |
| Bulk emails versturen | ✅ | ✅ | ✅ | ❌ |
| **Analytics** |
| Analytics bekijken | ✅ | ✅ | ✅ | ❌ |
| **Staff Notes** |
| Notities bekijken | ✅ | ✅ | ✅ | ❌ |
| Notities aanmaken | ✅ | ✅ | ✅ | ❌ |
| **Email Integratie** |
| Email settings | ✅ | ✅ | ❌ | ❌ |
| **Database Backup** |
| Backup maken | ✅ | ✅ | ❌ | ❌ |

---

## 🛡️ Beveiligings Checklist

### **A. Middleware Protection** ✅ PRIORITEIT 1
- [ ] Middleware voor role checking maken
- [ ] Middleware voor organisatie scope checking
- [ ] Routes beschermen met middleware
- [ ] API routes beschermen

### **B. Policy Classes** ✅ PRIORITEIT 1
- [ ] KlantPolicy
- [ ] BikefitPolicy
- [ ] UserPolicy
- [ ] MedewerkerPolicy
- [ ] TestzadelPolicy
- [ ] SjabloonPolicy
- [ ] OrganisatiePolicy

### **C. Controller Authorization** ✅ PRIORITEIT 1
- [ ] Authorize checks in alle controllers
- [ ] OrganisatieId validation
- [ ] Role checking in elke method

### **D. Model Scopes** ✅ PRIORITEIT 2
- [ ] Global scope voor organisatie_id
- [ ] Scopes voor role-based queries
- [ ] Soft delete scopes

### **E. Database Security** ✅ PRIORITEIT 2
- [ ] Mass assignment protection ($fillable/$guarded)
- [ ] Query scope checks
- [ ] Cross-organisatie data leaks voorkomen

### **F. View Security** ✅ PRIORITEIT 3
- [ ] Blade directives voor role checks
- [ ] Hide UI elements voor unauthorized users
- [ ] CSRF protection verificatie

### **G. API Security** ✅ PRIORITEIT 3
- [ ] API token authentication
- [ ] Rate limiting
- [ ] API versioning

---

## 🚀 Implementatie Plan

### **FASE 1: Critical Security (Week 1)** 🔥

#### **Stap 1: Middleware Aanmaken**

**Bestanden aan te maken:**
- `app/Http/Middleware/CheckRole.php`
- `app/Http/Middleware/CheckOrganisatie.php`
- `app/Http/Middleware/CheckSuperAdmin.php`

**Functionaliteit:**
```php
// CheckRole.php - Check of user specifieke rol heeft
public function handle($request, Closure $next, ...$roles)
{
    if (!auth()->check()) {
        return redirect('/login');
    }
    
    if (!in_array(auth()->user()->role, $roles)) {
        abort(403, 'Unauthorized access');
    }
    
    return $next($request);
}
```

#### **Stap 2: Routes Beschermen**

**Bestanden te wijzigen:**
- `routes/web.php`

**Route Groups maken:**
```php
// SuperAdmin routes
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::resource('organisaties', OrganisatieController::class);
});

// Admin + SuperAdmin routes
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('testzadels', TestzadelController::class);
    Route::resource('medewerkers', MedewerkerController::class);
    // etc...
});

// Medewerker + Admin routes
Route::middleware(['auth', 'role:medewerker,admin,superadmin'])->group(function () {
    Route::resource('klanten', KlantController::class);
    Route::get('/staff-notes', [StaffNoteController::class, 'index']);
});

// Klant routes (eigen data)
Route::middleware(['auth'])->group(function () {
    Route::get('/klanten/{klant}', [KlantController::class, 'show'])
        ->middleware('can:view,klant'); // Policy check
});
```

#### **Stap 3: Policy Classes Aanmaken**

**Bestanden aan te maken:**
- `app/Policies/KlantPolicy.php`
- `app/Policies/BikefitPolicy.php`
- `app/Policies/UserPolicy.php`
- etc...

**Voorbeeld KlantPolicy:**
```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Klant;

class KlantPolicy
{
    // Klant mag alleen eigen profiel bekijken
    public function view(User $user, Klant $klant)
    {
        // SuperAdmin kan alles
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Admin/Medewerker kan klanten in eigen organisatie zien
        if ($user->isBeheerder() || $user->isMedewerker()) {
            return $user->organisatie_id === $klant->organisatie_id;
        }
        
        // Klant kan alleen eigen profiel zien
        if ($user->isKlant()) {
            return $user->email === $klant->email;
        }
        
        return false;
    }
    
    public function viewAny(User $user)
    {
        // Alleen staff mag klanten lijst zien
        return $user->isBeheerder() || $user->isMedewerker() || $user->isSuperAdmin();
    }
    
    public function create(User $user)
    {
        return $user->isBeheerder() || $user->isMedewerker() || $user->isSuperAdmin();
    }
    
    public function update(User $user, Klant $klant)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isBeheerder() || $user->isMedewerker()) {
            return $user->organisatie_id === $klant->organisatie_id;
        }
        
        return false;
    }
    
    public function delete(User $user, Klant $klant)
    {
        // Alleen Admin mag verwijderen
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->isBeheerder() && $user->organisatie_id === $klant->organisatie_id;
    }
}
```

#### **Stap 4: Controllers Beveiligen**

**Bestanden te wijzigen:**
- Alle controllers in `app/Http/Controllers/`

**Voorbeeld:**
```php
class KlantController extends Controller
{
    public function index()
    {
        // Authorization check
        $this->authorize('viewAny', Klant::class);
        
        // Scope naar organisatie
        $klanten = Klant::where('organisatie_id', auth()->user()->organisatie_id)
            ->orderBy('achternaam')
            ->paginate(20);
            
        return view('klanten.index', compact('klanten'));
    }
    
    public function show(Klant $klant)
    {
        // Policy check
        $this->authorize('view', $klant);
        
        // Extra organisatie check
        if ($klant->organisatie_id !== auth()->user()->organisatie_id && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }
        
        return view('klanten.show', compact('klant'));
    }
}
```

---

### **FASE 2: Database Security (Week 2)** 🛡️

#### **Stap 1: Global Scopes Toevoegen**

**Bestanden te wijzigen:**
- `app/Models/Klant.php`
- `app/Models/Bikefit.php`
- etc...

**Voorbeeld:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Klant extends Model
{
    // Global scope: Filter altijd op organisatie_id (behalve voor SuperAdmin)
    protected static function booted()
    {
        static::addGlobalScope('organisatie', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->isSuperAdmin()) {
                $builder->where('organisatie_id', auth()->user()->organisatie_id);
            }
        });
    }
}
```

#### **Stap 2: Mass Assignment Protection**

**Alle models checken:**
```php
protected $fillable = [
    // Alleen velden die safe zijn voor mass assignment
];

protected $guarded = [
    'id',
    'organisatie_id', // Nooit via mass assignment!
    'created_at',
    'updated_at',
];
```

---

### **FASE 3: View & UI Security (Week 3)** 🎨

#### **Stap 1: Blade Directives voor Roles**

**Bestand te wijzigen:**
- `app/Providers/AppServiceProvider.php`

**Custom Blade Directives:**
```php
use Illuminate\Support\Facades\Blade;

Blade::if('superadmin', function () {
    return auth()->check() && auth()->user()->isSuperAdmin();
});

Blade::if('admin', function () {
    return auth()->check() && auth()->user()->isBeheerder();
});

Blade::if('medewerker', function () {
    return auth()->check() && auth()->user()->isMedewerker();
});

Blade::if('klant', function () {
    return auth()->check() && auth()->user()->isKlant();
});
```

**Gebruik in views:**
```blade
@superadmin
    <a href="/organisaties">Organisaties Beheren</a>
@endsuperadmin

@admin
    <a href="/users">Gebruikers Beheren</a>
@endadmin

@medewerker
    <a href="/klanten">Klanten Bekijken</a>
@endmedewerker
```

---

## 🧪 Testing Strategy

### **Security Tests Aanmaken:**

**Bestanden aan te maken:**
- `tests/Feature/Security/RoleAuthorizationTest.php`
- `tests/Feature/Security/OrganisatieScopeTest.php`

**Test Cases:**
```php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Klant;

class RoleAuthorizationTest extends TestCase
{
    /** @test */
    public function klant_cannot_access_admin_routes()
    {
        $klant = User::factory()->create(['role' => 'klant']);
        
        $this->actingAs($klant)
            ->get('/users')
            ->assertStatus(403);
            
        $this->actingAs($klant)
            ->get('/testzadels')
            ->assertStatus(403);
    }
    
    /** @test */
    public function klant_cannot_view_other_klanten()
    {
        $klant1 = User::factory()->create(['role' => 'klant']);
        $klant2 = Klant::factory()->create();
        
        $this->actingAs($klant1)
            ->get("/klanten/{$klant2->id}")
            ->assertStatus(403);
    }
    
    /** @test */
    public function medewerker_cannot_view_other_organisatie_data()
    {
        $org1 = Organisatie::factory()->create();
        $org2 = Organisatie::factory()->create();
        
        $medewerker = User::factory()->create([
            'role' => 'medewerker',
            'organisatie_id' => $org1->id
        ]);
        
        $klantOrg2 = Klant::factory()->create([
            'organisatie_id' => $org2->id
        ]);
        
        $this->actingAs($medewerker)
            ->get("/klanten/{$klantOrg2->id}")
            ->assertStatus(403);
    }
}
```

---

## 📝 Implementation Checklist

### **Week 1: Critical Security**
- [ ] CheckRole middleware aanmaken
- [ ] CheckOrganisatie middleware aanmaken
- [ ] Routes groeperen en beschermen
- [ ] Policy classes aanmaken voor alle models
- [ ] Controllers voorzien van authorize() checks

### **Week 2: Database Security**
- [ ] Global scopes toevoegen
- [ ] Mass assignment protection checken
- [ ] Cross-organisatie queries fixen
- [ ] Query scope tests schrijven

### **Week 3: Testing & UI**
- [ ] Security tests schrijven
- [ ] Blade directives implementeren
- [ ] UI elements verbergen voor unauthorized users
- [ ] Manual penetration testing

### **Week 4: Final Audit**
- [ ] Code review
- [ ] Security audit
- [ ] Performance testing
- [ ] Documentation updaten

---

## 🚨 Security Best Practices

### **DO's:**
✅ Altijd authorize() gebruiken in controllers
✅ Policy classes gebruiken voor complexe logica
✅ Queries filteren op organisatie_id
✅ CSRF protection enabled houden
✅ Input validation op alle forms
✅ Rate limiting op kritieke routes
✅ Logging van authorization failures

### **DON'Ts:**
❌ Nooit role checks in views alleen
❌ Nooit organisatie_id accepteren via form input
❌ Nooit direct user input in queries
❌ Nooit authorization checks skippen "voor gemak"
❌ Nooit hard-coded credentials

---

## 📧 Contact & Support

Voor vragen over de security implementatie:
- Technical Lead: [naam]
- Security Officer: [naam]

---

**Status:** 🔴 KRITIEK - Moet geïmplementeerd worden voor productie
**Laatste Update:** 27 oktober 2025
**Versie:** 1.0
