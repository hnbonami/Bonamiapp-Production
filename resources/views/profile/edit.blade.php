<x-app-layout>
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
    @endphp
    
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profile') }}
            </h2>
            <div>
                <button
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'profile-modal')"
                    class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 focus:outline-none focus:ring ring-indigo-300"
                >
                    Profiel bewerken
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>
    </div>

    <x-modal name="profile-modal" id="profile-modal" focusable>
        @include('profile.modal')
    </x-modal>

    <style>
        /* GLOBALE fix voor menubalk avatar - zeer specifiek */
        body nav img,
        body header img,
        html nav img,
        html header img,
        .rounded-full img,
        img.rounded-full,
        .w-8 img,
        .h-8 img,
        .w-10 img,
        .h-10 img,
        .w-12 img,
        .h-12 img {
            max-width: 40px !important;
            max-height: 40px !important;
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            min-height: 40px !important;
            object-fit: cover !important;
        }

        /* Specifiek voor de rechtsboven avatar in menubalk */
        .flex.items-center img,
        .ml-3 img,
        .mr-3 img,
        button img,
        .dropdown-toggle img {
            max-width: 40px !important;
            max-height: 40px !important;
            width: 40px !important;
            height: 40px !important;
            object-fit: cover !important;
        }
    </style>
</x-app-layout>
