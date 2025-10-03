<x-guest-layout container-max-w="800px" container-h="460px" image-flex="0 0 45%" form-padding="p-8" form-top="40px">
    <div class="mb-4 text-sm text-gray-600">
        Wachtwoord vergeten? Geen probleem. Geef je e-mailadres op en je ontvangt een link om je wachtwoord te resetten.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-mailadres" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="bg-[#c8e1eb] hover:bg-[#9bb3bd] text-black border-0">
                Resetlink per e-mail sturen
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
