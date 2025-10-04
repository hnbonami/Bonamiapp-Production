<x-guest-layout container-max-w="800px" container-h="460px" image-flex="0 0 45%" form-padding="p-8" form-top="40px">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-mailadres" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Wachtwoord" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#c8e1eb] shadow-sm focus:ring-[#c8e1eb]" name="remember">
                <span class="ms-2 text-sm text-gray-600">Onthoud mij</span>
            </label>
        </div>

        <div class="flex flex-col gap-3 mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-[#c8e1eb] hover:text-[#9bb3bd] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#c8e1eb]" href="{{ route('password.request') }}">
                    Wachtwoord vergeten?
                </a>
            @endif
            <x-primary-button class="w-full bg-[#c8e1eb] hover:bg-[#9bb3bd] text-black border-0">
                Inloggen
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
