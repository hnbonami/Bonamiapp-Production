<div id="userDropdown" style="position:relative;display:inline-block;">
    <button id="userDropdownButton" type="button" style="background:none;border:none;color:#4F46E5;cursor:pointer;font-weight:600;font-size:1.2em;outline:none;">{{ Auth::user()->voornaam ?? Auth::user()->name }} &#9662;</button>
    <div id="userDropdownMenu" style="display:none;position:absolute;right:0;top:110%;background:#fff;border:1px solid #e5e7eb;box-shadow:0 4px 16px #0001;border-radius:8px;min-width:180px;z-index:1000;padding:0.5em 0;">
    <a href="#" style="display:block;padding:0.7em 1.2em;color:#222;text-decoration:none;font-weight:500;">Profiel wijzigen</a>
    <a href="#" style="display:block;padding:0.7em 1.2em;color:#222;text-decoration:none;font-weight:500;">Instellingen</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" style="width:100%;text-align:right;padding:0.7em 1.2em;color:#ef4444;background:none;border:none;font-weight:500;cursor:pointer;font-size:1em;">Uitloggen</button>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('userDropdownButton');
    const menu = document.getElementById('userDropdownMenu');
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
    document.addEventListener('click', function() {
        menu.style.display = 'none';
    });
});
</script>
