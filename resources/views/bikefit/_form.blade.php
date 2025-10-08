@php

    $b = $bikefit ?? null;
@endphp

<!-- Fields for bikefit (used by create and edit). Parent form must provide @csrf and method. -->
<div class="mb-8 p-4 bg-gray-50 rounded border border-gray-200">
    <h2 class="text-2xl font-bold mb-4">Algemene gegevens</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label for="testtype" class="block font-medium">Testtype</label>
            <select name="testtype" id="testtype" class="mt-1 block w-full border-gray-300 rounded" required>
                <option value="standaard bikefit" {{ old('testtype', optional($b)->testtype ?? '') == 'standaard bikefit' ? 'selected' : '' }}>Standaard bikefit</option>
                <option value="professionele bikefit" {{ old('testtype', optional($b)->testtype ?? '') == 'professionele bikefit' ? 'selected' : '' }}>Professionele bikefit</option>
                <option value="maten bepalen" {{ old('testtype', optional($b)->testtype ?? '') == 'maten bepalen' ? 'selected' : '' }}>Maten bepalen</option>
                <option value="zadeldrukmeting" {{ old('testtype', optional($b)->testtype ?? '') == 'zadeldrukmeting' ? 'selected' : '' }}>Zadeldrukmeting</option>
            </select>
        </div>
        <div>
            <label for="type_fitting" class="block font-medium">Type fitting</label>
            <select name="type_fitting" id="type_fitting" class="mt-1 block w-full border-gray-300 rounded" required>
                <option value="comfort" {{ old('type_fitting', $b->type_fitting ?? '') == 'comfort' ? 'selected' : '' }}>Comfort</option>
                <option value="sportief" {{ old('type_fitting', $b->type_fitting ?? '') == 'sportief' ? 'selected' : '' }}>Sportief</option>
                <option value="race" {{ old('type_fitting', $b->type_fitting ?? '') == 'race' ? 'selected' : '' }}>Race</option>
                <option value="mountainbike" {{ old('type_fitting', $b->type_fitting ?? '') == 'mountainbike' ? 'selected' : '' }}>Mountainbike</option>
                <option value="tijdritfiets" {{ old('type_fitting', $b->type_fitting ?? '') == 'tijdritfiets' ? 'selected' : '' }}>Tijdritfiets</option>
            </select>
        </div>
        <div>
            <label for="datum" class="block font-medium">Datum test</label>
            <input type="date" name="datum" id="datum" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('datum', optional($b)->datum ? optional($b)->datum->format('Y-m-d') : now()->format('Y-m-d')) }}">
        </div>
    </div>
    <!-- Dubbele velden verwijderd: Kadermaat, Frametype, Type fitting -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label for="fietsmerk" class="block font-medium">Fietsmerk</label>
            <input type="text" name="fietsmerk" id="fietsmerk" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('fietsmerk', $b->fietsmerk ?? '') }}">
        </div>
        <div>
            <label for="kadermaat" class="block font-medium">Kadermaat</label>
            <input type="text" name="kadermaat" id="kadermaat" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('kadermaat', $b->kadermaat ?? '') }}">
        </div>
        <div>
            <label for="frametype" class="block font-medium">Frametype</label>
            <input type="text" name="frametype" id="frametype" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('frametype', $b->frametype ?? '') }}">
        </div>
    </div>
    <!-- Dubbele losse velden verwijderd: Kadermaat, Frametype, Type fitting -->
    </div>
    <!-- Dubbele velden verwijderd: Kadermaat, Frametype, Type fitting -->

    <!-- Anamnese blok netjes onder algemene gegevens, in één kolom -->
    <h2 class="text-2xl font-bold mt-8 mb-4">Anamnese</h2>
    <div class="mb-6">
        <label class="block mb-2">Algemene klachten:</label>
        <textarea name="algemene_klachten" class="border rounded w-full p-2 mb-4">{{ old('algemene_klachten', $b->algemene_klachten ?? '') }}</textarea>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block mb-2">Beenlengteverschil</label>
                <select id="beenlengteverschil" name="beenlengteverschil" class="border rounded w-full p-2 mb-4">
                    <option value="0" {{ old('beenlengteverschil', $b->beenlengteverschil ?? '0') == '0' ? 'selected' : '' }}>Neen</option>
                    <option value="1" {{ old('beenlengteverschil', $b->beenlengteverschil ?? '0') == '1' ? 'selected' : '' }}>Ja</option>
                </select>
                <div id="beenlengte_cm_wrap" style="display:{{ old('beenlengteverschil', $b->beenlengteverschil ?? '0') == '1' ? 'block' : 'none' }};" class="mb-4">
                    <label class="block mb-2">Beenlengte verschil:</label>
                    <input type="text" name="beenlengteverschil_cm" id="beenlengteverschil_cm" value="{{ old('beenlengteverschil_cm', $b->beenlengteverschil_cm ?? '') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="bijv. 2 cm of links korter">
                </div>
            </div>
            <div>
                <label class="block mb-2">Lenigheid hamstrings:</label>
                <input type="text" name="lenigheid_hamstrings" class="border rounded w-full p-2 mb-4" value="{{ old('lenigheid_hamstrings', $b->lenigheid_hamstrings ?? '') }}">
            </div>
            <div>
                <label class="block mb-2">Steunzolen:</label>
                <select id="steunzolen" name="steunzolen" class="border rounded w-full p-2 mb-4">
                    <option value="0" {{ old('steunzolen', $b->steunzolen ?? '0') == '0' ? 'selected' : '' }}>Neen</option>
                    <option value="1" {{ old('steunzolen', $b->steunzolen ?? '0') == '1' ? 'selected' : '' }}>Ja</option>
                </select>
                <div id="steunzolen_reden_wrap" style="display:{{ old('steunzolen', $b->steunzolen ?? '0') == '1' ? 'block' : 'none' }};" class="mb-4">
                    <label class="block mb-2">Reden steunzolen:</label>
                    <input type="text" name="steunzolen_reden" class="border rounded w-full p-2" value="{{ old('steunzolen_reden', $b->steunzolen_reden ?? '') }}">
                </div>
            </div>
        </div>
    </div>


            <!-- Mobiliteitstabel over volledige breedte -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4">Functionele controle / Mobiliteit</h2>
                @php
                        $mobilityFields = [
                                'straight_leg_raise' => 'Straight Leg Raise (hamstrings)',
                                'knieflexie' => 'Knieflexie (rectus femoris)',
                                'heup_endorotatie' => 'Heup endorotatie',
                                'heup_exorotatie' => 'Heup exorotatie',
                                'enkeldorsiflexie' => 'Enkeldorsiflexie',
                                'one_leg_squat' => 'One leg squat',
                        ];
                        $mobilityOptions = ['Heel laag','Laag','Gemiddeld','Hoog','Heel hoog'];
                @endphp
                <table class="w-full border border-gray-200 rounded-xl bg-white">
                    <thead>
                        <tr style="background:rgba(193,223,235,0.85);">
                            <th class="py-3 px-4 text-left text-lg font-semibold">Test</th>
                            <th class="py-3 px-4 text-center text-lg font-semibold">Links</th>
                            <th class="py-3 px-4 text-center text-lg font-semibold">Rechts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mobilityFields as $key => $label)
                        <tr class="border-b border-gray-200">
                            <td class="py-3 px-4 font-semibold text-base align-top">{{ $label }}</td>
                            <td class="py-3 px-4 text-center align-top">
                                <select name="{{ $key }}_links" class="border rounded w-full p-2">
                                    <option value="">-</option>
                                    @foreach($mobilityOptions as $opt)
                                        <option value="{{ $opt }}" {{ old($key.'_links', $b->{$key.'_links'} ?? ($defaultMobility[$key.'_links'] ?? '_')) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-3 px-4 text-center align-top">
                                <select name="{{ $key }}_rechts" class="border rounded w-full p-2">
                                    <option value="">-</option>
                                    @foreach($mobilityOptions as $opt)
                                        <option value="{{ $opt }}" {{ old($key.'_rechts', $b->{$key.'_rechts'} ?? ($defaultMobility[$key.'_rechts'] ?? '_')) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

</div>

<!-- Voetmeting -->
<h2 class="text-2xl font-bold mt-4 mb-4">Voetmeting</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
    <div>
        <label>Schoenmaat:</label>
        <select name="schoenmaat" class="border rounded w-full p-2">
            @for($i=35;$i<=50;$i+=0.5)
                <option value="{{ $i }}" {{ old('schoenmaat', $b->schoenmaat ?? '') == $i ? 'selected' : '' }}>{{ $i }}</option>
            @endfor
        </select>
    </div>
    <div>
        <label>Voetbreedte (cm):</label>
        <select name="voetbreedte" class="border rounded w-full p-2">
            @for($v=6;$v<=13;$v+=0.5)
                <option value="{{ $v }}" {{ (string)old('voetbreedte', $b->voetbreedte ?? '') === (string)$v ? 'selected' : '' }}>{{ $v }} cm</option>
            @endfor
        </select>
    </div>
</div>
<div class="mt-3">
    <label class="block text-sm">Voetpositie</label>
    <div class="flex gap-4 mt-2">
        <label><input type="radio" name="voetpositie" value="neutraal" {{ old('voetpositie', $b->voetpositie ?? '')=='neutraal' ? 'checked' : '' }}> Neutraal</label>
        <label><input type="radio" name="voetpositie" value="pronatie" {{ old('voetpositie', $b->voetpositie ?? '')=='pronatie' ? 'checked' : '' }}> Pronatie</label>
        <label><input type="radio" name="voetpositie" value="supinatie" {{ old('voetpositie', $b->voetpositie ?? '')=='supinatie' ? 'checked' : '' }}> Supinatie</label>
    </div>
</div>

<h2 class="text-2xl font-bold mt-20 mb-6">Lichaamsmaten</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    <div><label>Lengte (cm):</label><input type="number" step="0.1" name="lengte_cm" class="border rounded w-full p-2" value="{{ old('lengte_cm', $b->lengte_cm ?? '') }}"></div>
    <div><label>Binnenbeenlengte (cm):</label><input type="number" step="0.1" name="binnenbeenlengte_cm" class="border rounded w-full p-2" value="{{ old('binnenbeenlengte_cm', $b->binnenbeenlengte_cm ?? '') }}"></div>
    <div><label>Armlengte (cm):</label><input type="number" step="0.1" name="armlengte_cm" class="border rounded w-full p-2" value="{{ old('armlengte_cm', $b->armlengte_cm ?? '') }}"></div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div><label>Romplengte (cm):</label><input type="number" step="0.1" name="romplengte_cm" class="border rounded w-full p-2" value="{{ old('romplengte_cm', $b->romplengte_cm ?? '') }}"></div>
    <div><label>Schouderbreedte (cm):</label><input type="number" step="0.1" name="schouderbreedte_cm" class="border rounded w-full p-2" value="{{ old('schouderbreedte_cm', $b->schouderbreedte_cm ?? '') }}"></div>
</div>
<h2 class="text-2xl font-bold mt-6 mb-4">Gemeten zitpositie</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div><label>Zadel-trapas hoek (graden):</label><input type="number" step="0.1" name="zadel_trapas_hoek" class="border rounded w-full p-2" value="{{ old('zadel_trapas_hoek', $b->zadel_trapas_hoek ?? '') }}"></div>
    <div><label>Zadel-trapas afstand (cm):</label><input type="number" step="0.1" name="zadel_trapas_afstand" class="border rounded w-full p-2" value="{{ old('zadel_trapas_afstand', $b->zadel_trapas_afstand ?? '') }}"></div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div><label>Stuur-trapas hoek (graden):</label><input type="number" step="0.1" name="stuur_trapas_hoek" class="border rounded w-full p-2" value="{{ old('stuur_trapas_hoek', $b->stuur_trapas_hoek ?? '') }}"></div>
    <div><label>Stuur-trapas afstand (cm):</label><input type="number" step="0.1" name="stuur_trapas_afstand" class="border rounded w-full p-2" value="{{ old('stuur_trapas_afstand', $b->stuur_trapas_afstand ?? '') }}"></div>
</div>
<div class="grid grid-cols-1 gap-4">
    <div><label for="zadel_lengte_center_top" class="block font-medium">Zadel lengte (center-top in cm):</label><input type="number" step="0.1" name="zadel_lengte_center_top" id="zadel_lengte_center_top" class="border rounded w-full p-2" value="{{ old('zadel_lengte_center_top', $b->zadel_lengte_center_top ?? '') }}"></div>
</div>


<h2 class="text-2xl font-bold mt-6 mb-4">Aanpassingen fiets</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="aanpassingen_zadel" class="block font-medium">Aanpassing zadel (cm)</label>
        <input type="number" step="0.1" name="aanpassingen_zadel" id="aanpassingen_zadel" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('aanpassingen_zadel', $b->aanpassingen_zadel ?? '') }}">
    </div>
    <div>
        <label for="aanpassingen_setback" class="block font-medium">Aanpassing setback (cm)</label>
        <input type="number" step="0.1" name="aanpassingen_setback" id="aanpassingen_setback" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('aanpassingen_setback', $b->aanpassingen_setback ?? '') }}">
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="aanpassingen_reach" class="block font-medium">Aanpassing reach (cm)</label>
        <input type="number" step="0.1" name="aanpassingen_reach" id="aanpassingen_reach" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('aanpassingen_reach', $b->aanpassingen_reach ?? '') }}">
    </div>
    <div>
        <label for="aanpassingen_drop" class="block font-medium">Aanpassing drop (cm)</label>
        <input type="number" step="0.1" name="aanpassingen_drop" id="aanpassingen_drop" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('aanpassingen_drop', $b->aanpassingen_drop ?? '') }}">
    </div>
</div>

<!-- BEGIN: Stuurpen aanpassing (vervangt oude blok) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="aanpassingen_stuurpen_aan" class="block font-medium">Aanpassing stuurpen</label>
        <select name="aanpassingen_stuurpen_aan" id="aanpassingen_stuurpen_aan" class="mt-1 block w-full border-gray-300 rounded">
            <option value="0" {{ (string)old('aanpassingen_stuurpen_aan', (string)($b->aanpassingen_stuurpen_aan ?? '0')) === '0' ? 'selected' : '' }}>Neen</option>
            <option value="1" {{ (string)old('aanpassingen_stuurpen_aan', (string)($b->aanpassingen_stuurpen_aan ?? '0')) === '1' ? 'selected' : '' }}>Ja</option>
        </select>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" id="stuurpen_lengte_wrap" style="display:{{ (string)old('aanpassingen_stuurpen_aan', (string)($b->aanpassingen_stuurpen_aan ?? '0')) === '1' ? 'flex' : 'none' }};">
    <div>
        <label for="aanpassingen_stuurpen_pre" class="block font-medium">Stuurpenlengte voor (mm)</label>
        <input type="number" step="1" name="aanpassingen_stuurpen_pre" id="aanpassingen_stuurpen_pre" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('aanpassingen_stuurpen_pre', $b->aanpassingen_stuurpen_pre ?? '') }}">
    </div>
    <div>
        <label for="aanpassingen_stuurpen_post" class="block font-medium">Stuurpenlengte na (mm)</label>
        <input type="number" step="1" name="aanpassingen_stuurpen_post" id="aanpassingen_stuurpen_post" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('aanpassingen_stuurpen_post', $b->aanpassingen_stuurpen_post ?? '') }}">
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('aanpassingen_stuurpen_aan');
    var wrap = document.getElementById('stuurpen_lengte_wrap');
    if(select && wrap) {
        select.addEventListener('change', function() {
            wrap.style.display = select.value === '1' ? 'flex' : 'none';
        });
    }
});
</script>
<!-- EINDE: Stuurpen aanpassing -->

<h2 class="text-2xl font-bold mt-6 mb-4">Zadel</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    <div>
        <label class="block font-medium">Type zadel</label>
        <input type="text" name="type_zadel" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('type_zadel', $b->type_zadel ?? '') }}">
    </div>
    <div>
        <label class="block font-medium">Zadeltil (graden)</label>
        <input type="number" step="0.1" name="zadeltil" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('zadeltil', $b->zadeltil ?? '') }}">
    </div>
    <div>
        <label class="block font-medium">Zadelbreedte (mm)</label>
        <input type="number" name="zadelbreedte" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('zadelbreedte', $b->zadelbreedte ?? '') }}">
    </div>
</div>
<div class="grid grid-cols-1 gap-4 mb-4">
    <div>
        <label class="block font-medium">Nieuw uitleensysteem (zie onderaan form)</label>
        <input type="text" name="nieuw_testzadel" style="display: none;" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('nieuw_testzadel', $b->nieuw_testzadel ?? '') }}">
    </div>
</div>

<h2 class="text-2xl font-bold mt-6 mb-4">Schoenplaatjes</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block font-medium">Rotatie aanpassingen</label>
        <input type="text" name="rotatie_aanpassingen" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('rotatie_aanpassingen', $b->rotatie_aanpassingen ?? '') ?: 'nvt.' }}">
    </div>
    <div>
        <label class="block font-medium">Inclinatie aanpassingen</label>
        <input type="text" name="inclinatie_aanpassingen" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('inclinatie_aanpassingen', $b->inclinatie_aanpassingen ?? '') ?: 'nvt.' }}">
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block font-medium">Ophoging links (mm)</label>
        <input type="number" name="ophoging_li" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('ophoging_li', $b->ophoging_li ?? '') ?: 'nvt.' }}">
    </div>
    <div>
        <label class="block font-medium">Ophoging rechts (mm)</label>
        <input type="number" name="ophoging_re" class="mt-1 block w-full border-gray-300 rounded" value="{{ old('ophoging_re', $b->ophoging_re ?? '') ?: 'nvt.' }}">
    </div>
</div>
<div class="md:col-span-3">
<h2 class="text-2xl font-bold mt-8 mb-4">Opmerkingen</h2>
<div class="md:col-span-3">
    <label class="block font-medium">Opmerkingen</label>
    <textarea name="opmerkingen" class="mt-1 block w-full border-gray-300 rounded">{{ old('opmerkingen', $b->opmerkingen ?? '') }}</textarea>
</div>
<div class="md:col-span-3">
    <label class="block font-medium">Interne opmerkingen</label>
    <textarea name="interne_opmerkingen" class="mt-1 block w-full border-gray-300 rounded">{{ old('interne_opmerkingen', $b->interne_opmerkingen ?? '') }}</textarea>
</div>

@if(!isset($isEdit) || !$isEdit)
    <div class="mb-4 bg-gray-100 border border-gray-300 rounded p-4 text-gray-600">
        <strong>Let op:</strong> Bestanden uploaden kan pas nadat de bikefit is aangemaakt. Sla eerst de bikefit op en voeg daarna je bestanden toe via de bewerkpagina.
    </div>
@endif

<!-- Verwijder knoppen en forms onderaan, deze staan nu in edit.blade.php -->
