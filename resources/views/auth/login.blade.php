@php
    // Haal branding instellingen op voor organisatie 1 (standaard)
    $branding = \App\Models\OrganisatieBranding::where('organisatie_id', 1)->first();
    
    // Fallback naar default kleuren
    $loginTextColor = $branding->login_text_color ?? '#374151';
    $loginButtonColor = $branding->login_button_color ?? '#c8e1eb';
    $loginButtonHoverColor = $branding->login_button_hover_color ?? '#9bb3bd';
    $loginLinkColor = $branding->login_link_color ?? '#c8e1eb';
    $loginBackgroundImage = $branding && $branding->login_background_image 
        ? asset('storage/' . $branding->login_background_image) 
        : null;
@endphp

<x-guest-layout :background-image="$loginBackgroundImage">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-mailadres" :style="'color: ' . $loginTextColor" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Wachtwoord" :style="'color: ' . $loginTextColor" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 shadow-sm focus:ring-2" style="color: {{ $loginButtonColor }}; --tw-ring-color: {{ $loginButtonColor }}" name="remember">
                <span class="ms-2 text-sm" style="color: {{ $loginTextColor }}">Onthoud mij</span>
            </label>
        </div>

        <div class="flex flex-col gap-3 mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2" 
                   style="color: {{ $loginLinkColor }}; --tw-ring-color: {{ $loginLinkColor }}"
                   href="{{ route('password.request') }}">
                    Wachtwoord vergeten?
                </a>
            @endif
            <x-primary-button class="w-full text-black border-0 transition-colors duration-200" 
                              style="background-color: {{ $loginButtonColor }}"
                              onmouseover="this.style.backgroundColor='{{ $loginButtonHoverColor }}'"
                              onmouseout="this.style.backgroundColor='{{ $loginButtonColor }}'">
                Inloggen
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
