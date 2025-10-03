<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Persoonlijke Gegevens') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update je account's profiel informatie en emailadres.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update-personal') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Naam velden -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" :value="__('Voornaam')" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="$user->voornaam" autofocus autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>
            <div>
                <x-input-label for="last_name" :value="__('Achternaam')" />
                <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="$user->naam" autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="$user->email" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Telefoonnummer -->
        <div>
            <x-input-label for="phone" :value="__('Telefoonnummer')" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="$user->telefoonnummer" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <!-- Geboortedatum -->
        <div>
            <x-input-label for="birth_date" :value="__('Geboortedatum')" />
            <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="$user->geboortedatum ? $user->geboortedatum->format('Y-m-d') : ''" />
            <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
        </div>

        <!-- Adres -->
        <div>
            <x-input-label for="address" :value="__('Adres')" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="$user->adres" autocomplete="street-address" />
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <!-- Stad en Postcode -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="city" :value="__('Stad')" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="$user->stad" autocomplete="address-level2" />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>
            <div>
                <x-input-label for="postal_code" :value="__('Postcode')" />
                <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="$user->postcode" autocomplete="postal-code" />
                <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Opslaan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Opgeslagen.') }}</p>
            @endif
        </div>
    </form>
</section>
