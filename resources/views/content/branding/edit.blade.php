<!-- Login Logo Upload -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Login Logo
    </label>
    @if($branding->login_logo)
        <div class="mb-3">
            <img src="{{ asset('storage/' . $branding->login_logo) }}" alt="Huidig logo" class="h-16 w-auto border rounded">
        </div>
    @endif
    <input 
        type="file" 
        name="login_logo" 
        accept="image/*"
        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
    />
    <p class="mt-1 text-sm text-gray-500">Aanbevolen: transparant PNG, max 500KB</p>
</div>

<!-- Login Achtergrondafbeelding Upload -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Login Achtergrondafbeelding (rechts)
    </label>
    @if($branding->login_background_image)
        <div class="mb-3">
            <img src="{{ asset('storage/' . $branding->login_background_image) }}" alt="Huidige achtergrond" class="h-32 w-auto border rounded">
        </div>
    @endif
    <input 
        type="file" 
        name="login_background_image" 
        accept="image/*"
        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
    />
    <p class="mt-1 text-sm text-gray-500">Aanbevolen: hoge kwaliteit foto, portrait/vierkant formaat, max 2MB</p>
</div>