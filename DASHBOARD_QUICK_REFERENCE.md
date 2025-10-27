# 🚀 Dashboard Widget System - Quick Reference

## 📋 Rol Rechten Matrix

```
┌─────────────────┬──────────┬────────────┬────────┬──────────┬──────────┬────────────┐
│ Rol             │ Zien     │ Drag&Drop  │ Resize │ Aanmaken │ Bewerken │ Verwijderen│
├─────────────────┼──────────┼────────────┼────────┼──────────┼──────────┼────────────┤
│ Klant           │ Eigen org│     ✅     │   ❌   │    ❌    │    ❌    │     ❌     │
│ Medewerker      │ Eigen org│     ✅     │ ✅ Eigen│    ✅    │ ✅ Eigen │  ✅ Eigen  │
│ Admin           │ Eigen org│     ✅     │ ✅ Alles│    ✅    │ ✅ Alles │  ✅ Alles  │
│ Super Admin     │  Org 1   │     ✅     │ ✅ Alles│    ✅    │ ✅ Alles │  ✅ Alles  │
└─────────────────┴──────────┴────────────┴────────┴──────────┴──────────┴────────────┘
```

---

## 🔐 Policy Methods

```php
// In Controller
$this->authorize('view', $widget);
$this->authorize('create', DashboardWidget::class);
$this->authorize('update', $widget);
$this->authorize('delete', $widget);
$this->authorize('drag', $widget);
$this->authorize('resize', $widget);

// In Blade
@can('update', $widget)
    <!-- edit button -->
@endcan

// Programmatisch
if ($widget->canBeEditedBy($user)) { ... }
if ($widget->canBeResizedBy($user)) { ... }
```

---

## 🎯 Model Helper Methods

```php
$widget = DashboardWidget::find(1);

// Check rechten
$widget->canBeViewedBy($user);      // bool
$widget->canBeEditedBy($user);      // bool
$widget->canBeDeletedBy($user);     // bool
$widget->canBeDraggedBy($user);     // bool
$widget->canBeResizedBy($user);     // bool

// Scopes
DashboardWidget::visibleFor($user)->get();
DashboardWidget::forOrganisatie($orgId)->get();
DashboardWidget::active()->get();
```

---

## 📊 Controller Examples

### Index (lijst)
```php
public function index()
{
    $widgets = DashboardWidget::visibleFor(auth()->user())
        ->active()
        ->get();
    
    return view('dashboard.index', compact('widgets'));
}
```

### Store (aanmaken)
```php
public function store(Request $request)
{
    $this->authorize('create', DashboardWidget::class);
    
    $validated = $request->validate([...]);
    $validated['created_by'] = auth()->id();
    $validated['organisatie_id'] = auth()->user()->organisatie_id;
    
    $widget = DashboardWidget::create($validated);
    
    return redirect()->route('dashboard.index');
}
```

### Update (bewerken)
```php
public function update(Request $request, DashboardWidget $widget)
{
    $this->authorize('update', $widget);
    
    $widget->update($request->validated());
    
    return redirect()->route('dashboard.index');
}
```

---

## 🎨 Blade Examples

### Widget met rechten checks
```blade
<div class="widget">
    <h3>{{ $widget->title }}</h3>
    
    <div class="controls">
        @can('update', $widget)
            <a href="{{ route('dashboard.widgets.edit', $widget) }}">Bewerken</a>
        @endcan
        
        @can('delete', $widget)
            <form action="{{ route('dashboard.widgets.destroy', $widget) }}" method="POST">
                @csrf @method('DELETE')
                <button>Verwijderen</button>
            </form>
        @endcan
    </div>
</div>
```

### Gridstack permissions
```javascript
const widgets = @json($layouts->map(function($item) {
    return [
        'id' => $item['widget']->id,
        'canResize' => $item['widget']->canBeResizedBy(auth()->user()),
        'canDrag' => $item['widget']->canBeDraggedBy(auth()->user())
    ];
}));

grid.update(item, {
    noResize: !widgetPerms?.canResize,
    noMove: !widgetPerms?.canDrag,
});
```

---

## 🧪 Testing Checklist

```bash
# Als Klant (user_id = X, role = 'klant')
php artisan tinker
>>> $user = User::find(X)
>>> $widget = DashboardWidget::first()
>>> $widget->canBeEditedBy($user)      # false
>>> $widget->canBeResizedBy($user)     # false
>>> $widget->canBeDraggedBy($user)     # true

# Als Medewerker (eigen widget)
>>> $user = User::find(Y)
>>> $widget = DashboardWidget::where('created_by', Y)->first()
>>> $widget->canBeEditedBy($user)      # true
>>> $widget->canBeResizedBy($user)     # true

# Als Admin (andere organisatie)
>>> $admin = User::where('role', 'admin')->where('organisatie_id', 2)->first()
>>> $widget = DashboardWidget::where('organisatie_id', 1)->first()
>>> $widget->canBeEditedBy($admin)     # false (andere org!)

# Als Super Admin (organisatie 1)
>>> $super = User::where('role', 'superadmin')->first()
>>> $widget1 = DashboardWidget::where('organisatie_id', 1)->first()
>>> $widget2 = DashboardWidget::where('organisatie_id', 2)->first()
>>> $widget1->canBeEditedBy($super)    # true
>>> $widget2->canBeEditedBy($super)    # false (niet org 1!)
```

---

## 🔧 Debugging Commands

```bash
# Check widget organisaties
php artisan tinker
>>> DashboardWidget::select('id','title','organisatie_id')->get()

# Check user organisaties
>>> User::select('id','name','role','organisatie_id')->get()

# Check widgets zonder organisatie
>>> DashboardWidget::whereNull('organisatie_id')->count()

# Test policy
>>> Gate::forUser($user)->allows('update', $widget)

# Check routes
>>> Route::getRoutes()->match(Request::create('/dashboard/widgets/1/edit'))->getName()
```

---

## 🚨 Common Errors & Fixes

### Error: "Policy not found"
```bash
php artisan optimize:clear
# Check AuthServiceProvider.php $policies array
```

### Error: Widget niet zichtbaar
```php
// Check:
1. $widget->organisatie_id === $user->organisatie_id
2. $widget->is_active === true
3. Visibility instellingen correct
```

### Error: Resize werkt niet
```javascript
// Browser console:
console.log('Widget permissions:', widgets);
// Moet tonen: canResize: true
```

### Error: Super admin ziet alles
```php
// Check user org:
User::where('role', 'superadmin')->first()->organisatie_id
// Moet 1 zijn
```

---

## 📝 Database Queries

```sql
-- Check widgets per organisatie
SELECT organisatie_id, COUNT(*) as aantal
FROM dashboard_widgets
GROUP BY organisatie_id;

-- Check widgets zonder organisatie
SELECT id, title, created_by 
FROM dashboard_widgets 
WHERE organisatie_id IS NULL;

-- Update widgets naar user's organisatie
UPDATE dashboard_widgets dw
JOIN users u ON dw.created_by = u.id
SET dw.organisatie_id = u.organisatie_id
WHERE dw.organisatie_id IS NULL;
```

---

## 🎯 Key Takeaways

1. **Altijd organisatie check**: Super admin = org 1 only
2. **Policy gebruiken**: Niet zelf rol-checks doen
3. **JavaScript rechten**: Per widget dynamisch zetten
4. **Logging**: Alle acties loggen voor audit
5. **Testing**: Test met alle 4 rollen!

---

## 📞 Quick Help

**Problem**: Rechten werken niet  
**Solution**: Check Policy + organisatie_id + clear cache

**Problem**: Drag/resize werkt niet  
**Solution**: Check browser console + widget permissions log

**Problem**: Super admin ziet alles  
**Solution**: Check user.organisatie_id === 1

---

*Last updated: 2024*
