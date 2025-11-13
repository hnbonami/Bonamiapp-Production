@php
    $user = auth()->user();
    
    // GEBRUIK KLANT AVATAR indien klant rol
    if ($user->role === 'klant' && $user->klant_id) {
        $klant = \App\Models\Klant::find($user->klant_id);
        $avatar = $klant ? $klant->avatar_path : null;
    } else {
        // Voor beheerders/medewerkers
        $avatar = $user->avatar_path ?? $user->avatar;
    }
    
    // Genereer correcte avatar URL op basis van environment
    if ($avatar) {
        $avatarUrl = app()->environment('production') 
            ? asset('uploads/' . $avatar)
            : asset('storage/' . $avatar);
    } else {
        $avatarUrl = null;
    }
    
    $cacheKey = time();
@endphp

<div class="p-6 max-w-2xl">
    <h3 class="text-lg font-medium text-gray-900 mb-6">Profiel bewerken</h3>
    
    {{-- Avatar Upload Formulier --}}
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mb-6">
        @csrf
        @method('PATCH')
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Profielfoto</label>
            <div class="flex items-center gap-4">
                {{-- Current Avatar --}}
                <div class="relative">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}?t={{ $cacheKey }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200" id="modal-avatar-preview" />
                    @else
                        <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-semibold text-2xl border-2 border-gray-200" id="modal-avatar-placeholder">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                </div>
                
                {{-- Upload Button --}}
                <div>
                    <label for="modal-avatar-upload" class="cursor-pointer inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Nieuwe foto uploaden
                    </label>
                    <input type="file" id="modal-avatar-upload" name="avatar" accept="image/*" class="hidden" onchange="previewModalAvatar(event); this.form.submit();">
                    <p class="mt-2 text-xs text-gray-500">JPG, PNG of GIF. Max 2MB.</p>
                </div>
            </div>
            @error('avatar')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </form>
    
    <hr class="my-6">
    
    {{-- Profiel Informatie Formulier --}}
    <div class="space-y-6">
        @include('profile.partials.update-profile-information-form')
    </div>
    
    <hr class="my-6">
    
    {{-- Wachtwoord Wijzigen Formulier --}}
    <div class="space-y-6">
        @include('profile.partials.update-password-form')
    </div>
</div>

<script>
function previewModalAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('modal-avatar-preview');
            const placeholder = document.getElementById('modal-avatar-placeholder');
            
            if (preview) {
                preview.src = e.target.result;
            } else if (placeholder) {
                // Vervang placeholder met img
                const img = document.createElement('img');
                img.id = 'modal-avatar-preview';
                img.src = e.target.result;
                img.className = 'w-20 h-20 rounded-full object-cover border-2 border-gray-200';
                placeholder.parentNode.replaceChild(img, placeholder);
            }
        };
        reader.readAsDataURL(file);
    }
}
</script>
