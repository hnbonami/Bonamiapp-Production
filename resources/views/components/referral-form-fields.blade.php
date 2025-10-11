<!-- Referral System Form Fields -->
<div class="referral-section mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-medium text-gray-900 mb-4">📝 Hoe heeft deze klant ons gevonden?</h3>
    
    <div class="mb-4">
        <label for="referral_source" class="block text-sm font-medium text-gray-700">Hoe bent u bij ons terechtgekomen?</label>
        <select name="referral_source" id="referral_source" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                onchange="toggleReferringCustomerField()">
            <option value="">Selecteer een optie...</option>
            @if(isset($referralSources) && is_array($referralSources))
                @foreach($referralSources as $key => $label)
                    <option value="{{ $key }}" {{ old('referral_source') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            @else
                <option value="via_internet">Via internet/Google</option>
                <option value="mond_aan_mond">Mond aan mond</option>
                <option value="sociale_media">Sociale media</option>
                <option value="andere">Andere</option>
            @endif
        </select>
    </div>

    <!-- Referring Customer Selection (alleen zichtbaar bij "mond aan mond") -->
    <div id="referring_customer_section" class="mb-4" style="display: none;">
        <label for="referring_customer_id" class="block text-sm font-medium text-gray-700">
            🤝 Welke klant heeft u doorverwezen?
        </label>
        <select name="referring_customer_id" id="referring_customer_id" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Selecteer een klant...</option>
            @if(isset($availableReferringCustomers))
                @foreach($availableReferringCustomers as $customer)
                    <option value="{{ $customer['id'] }}" {{ old('referring_customer_id') == $customer['id'] ? 'selected' : '' }}>
                        {{ $customer['name'] }} ({{ $customer['email'] }})
                    </option>
                @endforeach
            @endif
        </select>
        <p class="mt-1 text-sm text-gray-500">
            💌 De geselecteerde klant ontvangt automatisch een bedankmail voor de doorverwijzing.
        </p>
    </div>

    <!-- Referral Notes (optioneel) -->
    <div class="mb-4">
        <label for="referral_notes" class="block text-sm font-medium text-gray-700 mb-2">
            Opmerkingen over doorverwijzing (optioneel)
        </label>
        <textarea name="referral_notes" id="referral_notes" rows="2" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Bijv. via Facebook post, na gesprek op café, etc...">{{ old('referral_notes') }}</textarea>
    </div>
</div>

<script>
function toggleReferringCustomerField() {
    const sourceSelect = document.getElementById('referral_source');
    const customerSection = document.getElementById('referring_customer_section');
    
    if (sourceSelect && customerSection) {
        if (sourceSelect.value === 'mond_aan_mond') {
            customerSection.style.display = 'block';
        } else {
            customerSection.style.display = 'none';
            // Reset customer selection when hiding
            const customerSelect = document.getElementById('referring_customer_id');
            if (customerSelect) {
                customerSelect.value = '';
            }
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleReferringCustomerField();
});
</script>