<!-- Huidige Trainingstatus -->
<div class="bg-white p-6 rounded-lg shadow mb-6">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Huidige Trainingstatus</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <!-- Slaapkwaliteit -->
        <div>
            <label for="slaapkwaliteit" class="block text-sm font-medium text-gray-700 mb-1">
                Slaapkwaliteit
                <span class="text-xs text-gray-500">(0 = slecht, 10 = perfect)</span>
            </label>
            <input type="range" 
                   id="slaapkwaliteit" 
                   name="slaapkwaliteit" 
                   min="0" 
                   max="10" 
                   value="{{ old('slaapkwaliteit', 5) }}"
                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                   oninput="updateScoreDisplay('slaapkwaliteit')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (slecht)</span>
                <span id="slaapkwaliteit_value" class="font-semibold">5</span>
                <span>10 (perfect)</span>
            </div>
        </div>

        <!-- Eetlust -->
        <div>
            <label for="eetlust" class="block text-sm font-medium text-gray-700 mb-1">
                Eetlust
                <span class="text-xs text-gray-500">(0 = slecht, 10 = perfect)</span>
            </label>
            <input type="range" 
                   id="eetlust" 
                   name="eetlust" 
                   min="0" 
                   max="10" 
                   value="{{ old('eetlust', 5) }}"
                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                   oninput="updateScoreDisplay('eetlust')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (slecht)</span>
                <span id="eetlust_value" class="font-semibold">5</span>
                <span>10 (perfect)</span>
            </div>
        </div>

        <!-- Gevoel op training -->
        <div>
            <label for="gevoel_op_training" class="block text-sm font-medium text-gray-700 mb-1">
                Gevoel op training
                <span class="text-xs text-gray-500">(0 = slecht, 10 = perfect)</span>
            </label>
            <input type="range" 
                   id="gevoel_op_training" 
                   name="gevoel_op_training" 
                   min="0" 
                   max="10" 
                   value="{{ old('gevoel_op_training', 5) }}"
                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                   oninput="updateScoreDisplay('gevoel_op_training')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (slecht)</span>
                <span id="gevoel_op_training_value" class="font-semibold">5</span>
                <span>10 (perfect)</span>
            </div>
        </div>

        <!-- Stressniveau -->
        <div>
            <label for="stressniveau" class="block text-sm font-medium text-gray-700 mb-1">
                Stressniveau
                <span class="text-xs text-gray-500">(0 = veel stress, 10 = geen stress)</span>
            </label>
            <input type="range" 
                   id="stressniveau" 
                   name="stressniveau" 
                   min="0" 
                   max="10" 
                   value="{{ old('stressniveau', 5) }}"
                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                   oninput="updateScoreDisplay('stressniveau')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (veel)</span>
                <span id="stressniveau_value" class="font-semibold">5</span>
                <span>10 (geen)</span>
            </div>
        </div>
    </div>

    <!-- Gemiddelde Score -->
    <div class="bg-blue-50 p-4 rounded-lg mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Gemiddelde Score</label>
        <div class="text-3xl font-bold text-blue-600" id="gemiddelde_display">5.0</div>
        <input type="hidden" name="gemiddelde_trainingstatus" id="gemiddelde_trainingstatus" value="5.0">
        <p class="text-xs text-gray-500 mt-1">Automatisch berekend gemiddelde van bovenstaande scores</p>
    </div>

    <!-- Training dag voor test -->
    <div class="mb-4">
        <label for="training_dag_voor_test" class="block text-sm font-medium text-gray-700 mb-1">
            Training dag voor de test
        </label>
        <textarea id="training_dag_voor_test" 
                  name="training_dag_voor_test" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Beschrijf de training van 1 dag voor de test...">{{ old('training_dag_voor_test') }}</textarea>
    </div>

    <!-- Training 2 dagen voor test -->
    <div>
        <label for="training_2d_voor_test" class="block text-sm font-medium text-gray-700 mb-1">
            Training 2 dagen voor de test
        </label>
        <textarea id="training_2d_voor_test" 
                  name="training_2d_voor_test" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Beschrijf de training van 2 dagen voor de test...">{{ old('training_2d_voor_test') }}</textarea>
    </div>
</div>

<script>
// Update score display en bereken gemiddelde
function updateScoreDisplay(fieldId) {
    const slider = document.getElementById(fieldId);
    const display = document.getElementById(fieldId + '_value');
    display.textContent = slider.value;
    
    // Bereken gemiddelde
    berekenGemiddelde();
}

function berekenGemiddelde() {
    const slaap = parseInt(document.getElementById('slaapkwaliteit').value) || 0;
    const eetlust = parseInt(document.getElementById('eetlust').value) || 0;
    const gevoel = parseInt(document.getElementById('gevoel_op_training').value) || 0;
    const stress = parseInt(document.getElementById('stressniveau').value) || 0;
    
    const gemiddelde = ((slaap + eetlust + gevoel + stress) / 4).toFixed(1);
    
    document.getElementById('gemiddelde_display').textContent = gemiddelde;
    document.getElementById('gemiddelde_trainingstatus').value = gemiddelde;
}

// Initialiseer bij laden
document.addEventListener('DOMContentLoaded', function() {
    updateScoreDisplay('slaapkwaliteit');
    updateScoreDisplay('eetlust');
    updateScoreDisplay('gevoel_op_training');
    updateScoreDisplay('stressniveau');
});
</script>