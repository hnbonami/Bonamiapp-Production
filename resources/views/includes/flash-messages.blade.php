@if(session('success'))
    <div class="alert alert-success" style="background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;border:1px solid #a7f3d0;">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="background:#fee2e2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;border:1px solid #fca5a5;">
        ❌ {{ session('error') }}
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning" style="background:#fef3c7;color:#d97706;padding:1em;margin-bottom:1em;border-radius:5px;border:1px solid #fde68a;">
        ⚠️ {{ session('warning') }}
    </div>
@endif