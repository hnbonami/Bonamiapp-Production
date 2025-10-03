<div class="modal">
    <h2>Profile Modal</h2>
    <p>Profile modal content goes here</p>
</div>
<div class="p-4">
    <h3 class="text-lg font-medium text-gray-900 mb-3">Profiel wijzigen</h3>
    <div class="space-y-4">
        @include('profile.partials.update-profile-information-form')
        @include('profile.partials.update-password-form')
    </div>
</div>
