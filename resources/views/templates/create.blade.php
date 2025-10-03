<script>
// Tab-functionaliteit voor sleutels
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    if(tabButtons.length && tabContents.length) {
        // Standaard eerste tab tonen
        tabContents.forEach(tc => tc.style.display = 'none');
        if(tabContents[0]) tabContents[0].style.display = 'block';
        tabButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const tab = btn.getAttribute('data-tab');
                tabContents.forEach(tc => tc.style.display = 'none');
                const active = document.getElementById('tab-' + tab);
                if(active) active.style.display = 'block';
            });
        });
    }
});
</script>
<!-- Verwijderd: dubbel upload-formulier bovenaan -->
@extends('layouts.app')

@section('content')



<div class="min-h-screen flex justify-center items-start" style="background: #fff;">
    <div class="flex w-full max-w-6xl mt-10" style="gap:0; align-items:flex-start; min-height:600px;">
        <div style="width:256px; min-width:200px; margin-top:24px; margin-left:-8px;">
            <div class="rounded-2xl p-4 flex flex-col" style="background:#fff; box-shadow:none;">
                <!-- Sidebar ...existing code... -->
            </div>
        </div>
        <div style="flex:1 1 0%; min-width:500px; margin-right:32px;">
            <div class="rounded-2xl p-8" style="background:#fff; box-shadow:none;">
                <h1 class="text-3xl font-bold mb-4 text-left" style="margin-top:32px;">Sjabloon aanmaken</h1>
                <form action="{{ route('sjablonen.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label for="type" class="block font-semibold mb-2">Type *</label>
                        <input type="text" name="type" id="type" class="form-input max-w-xs border border-gray-300 rounded px-3 py-2" required>
                    </div>
                    <div class="mb-6">
                        <label for="name" class="block font-semibold mb-2">Naam *</label>
                        <input type="text" name="name" id="name" class="form-input max-w-xs border border-gray-300 rounded px-3 py-2" required>
                    </div>
                    <div class="mb-6">
                        <label class="block font-semibold mb-1">Sjabloon inhoud *</label>
                        <div class="a4-editor-wrapper" style="max-width:210mm; margin-left:0; margin-top:18px;">
                        <div id="ckeditor-pages-container">
                            <div class="mb-4">
                                <textarea name="html_contents[]" id="wysiwyg-content-1" class="form-input w-full border border-gray-300 rounded px-3 py-2 wysiwyg-content" rows="10"></textarea>
                                <input type="hidden" name="background_urls[]" id="background_url_1" value="">
                                <div class="flex gap-2 mt-2 editor-btn-row" data-index="1"></div>
                            </div>
                        </div>
                        </div>
                    <!-- Alleen CKEditor, geen upload- of voorbeeldinstructies meer -->
                        <button type="button" id="add-page-btn" class="mt-2 px-4 py-2 bg-blue-100 text-blue-900 rounded">Pagina toevoegen</button>
                        <!-- CKEditor script en logica alleen onderaan, geen dubbele initialisatie -->
                    </div>
                    <div class="flex gap-3 justify-start mt-6">
                        <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded shadow hover:bg-blue-700 transition">Opslaan</button>
                        <a href="{{ route('sjablonen.index') }}" class="px-5 py-2 bg-gray-100 text-gray-900 font-semibold rounded shadow hover:bg-gray-200 transition">Annuleer</a>
                    </div>
                </form>
                <!-- Background upload JS removed -->
            </div>
        </div>
    </div>
    <script src="/ckeditor/ckeditor/ckeditor.js"></script>
    <script>
    // Alleen dynamische CKEditor per pagina, geen dubbele initialisatie!
    document.addEventListener('DOMContentLoaded', function() {
        let pageCount = 1;
        function addEditorButtons(index) {
            var btnRow = document.querySelector('.editor-btn-row[data-index="' + index + '"]');
            if (!btnRow) return;
            btnRow.innerHTML = '';
            var keyBtn = document.createElement('button');
            keyBtn.type = 'button';
            keyBtn.className = 'insert-key-btn px-3 py-1 bg-gray-200 rounded';
            keyBtn.setAttribute('data-index', index);
            keyBtn.innerText = 'Sleutel invoegen';
            keyBtn.onclick = function() {
                var idx = this.getAttribute('data-index');
                var key = prompt('Voer de sleutel in die je wilt invoegen (bijv. $Klant.naam$):');
                if(key && window.CKEDITOR && CKEDITOR.instances['wysiwyg-content-' + idx]) {
                    CKEDITOR.instances['wysiwyg-content-' + idx].insertText(key);
                }
            };
            var bgBtn = document.createElement('button');
            bgBtn.type = 'button';
            bgBtn.className = 'set-bg-btn px-3 py-1 bg-blue-200 text-blue-900 rounded';
            bgBtn.setAttribute('data-index', index);
            bgBtn.innerText = 'Achtergrond instellen';
            bgBtn.onclick = function() {
                var idx = this.getAttribute('data-index');
                var url = prompt('URL van PNG-achtergrond (bijv. /backgrounds/voorbeeld.png):');
                if (!url) return;
                var bgInput = document.getElementById('background_url_' + idx);
                if (bgInput) {
                    bgInput.value = url;
                    alert('Achtergrond-URL opgeslagen voor pagina ' + idx + '.');
                }
            };
            btnRow.appendChild(keyBtn);
            btnRow.appendChild(bgBtn);
        }

        function addPage() {
            pageCount++;
            const container = document.getElementById('ckeditor-pages-container');
            const wrapper = document.createElement('div');
            wrapper.className = 'mb-4';
            // Geen hr meer toevoegen, voorkomt lege ruimte/veld

// Zorg dat CKEditor altijd synchroniseert bij submit (maar voeg de handler maar één keer toe)
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form[action*="sjablonen.store"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (window.CKEDITOR) {
                for (var instanceName in CKEDITOR.instances) {
                    if (CKEDITOR.instances.hasOwnProperty(instanceName)) {
                        CKEDITOR.instances[instanceName].updateElement();
                    }
                }
            }
        });
    }
});
            const textarea = document.createElement('textarea');
            textarea.name = 'html_contents[]';
            textarea.id = 'wysiwyg-content-' + pageCount;
            textarea.className = 'form-input w-full border border-gray-300 rounded px-3 py-2 wysiwyg-content';
            textarea.rows = 10;
            const bgInput = document.createElement('input');
            bgInput.type = 'hidden';
            bgInput.name = 'background_urls[]';
            bgInput.id = 'background_url_' + pageCount;
            const btnRow = document.createElement('div');
            btnRow.className = 'flex gap-2 mt-2 editor-btn-row';
            btnRow.setAttribute('data-index', pageCount);
            wrapper.appendChild(textarea);
            wrapper.appendChild(bgInput);
            wrapper.appendChild(btnRow);
            container.appendChild(wrapper);
            setTimeout(function() {
                CKEDITOR.replace(textarea.id, {
                    height: 1123,
                    width: '794px',
                    contentsCss: '/ckeditor/ckeditor/contents.css',
                    bodyClass: 'a4-body',
                });
                addEditorButtons(pageCount);
            }, 100);
        }

        CKEDITOR.replace('wysiwyg-content-1', {
            height: 1123,
            width: '794px',
            contentsCss: '/ckeditor/ckeditor/contents.css',
            bodyClass: 'a4-body',
        });
        addEditorButtons(1);
        document.getElementById('add-page-btn').addEventListener('click', addPage);

    // Sleutels direct invoegen in actieve CKEditor (per pagina) - event handler verwijderd om dubbele invoeging te voorkomen
    });
    </script>
        <script>
        // Categoriseer sleutels
        const keyCategories = {
            'Klant': /^\$Klant\./,
            'Bikefit': /^\$Bikefit\./,
            'Overig': /.*/,
            'Overige': /^\$(ResultatenVoor|ResultatenNa|MobiliteitTabel|Bikefit\.prognose_zitpositie_html)\$/,
        };
        document.addEventListener('DOMContentLoaded', function() {
            var keysList = document.getElementById('all-keys-list');
            if (window.PREVIEW_KEYS && keysList) {
                // Categoriseer
                    <div class="mb-6">
                        <label for="background_pdf" class="block font-semibold mb-2">Achtergrond PDF uploaden</label>
                        <input type="file" name="background_pdf" id="background_pdf" accept=".pdf" class="border rounded px-3 py-2" style="max-width:300px;">
                        @if(session('success'))
                            <div class="mt-2 text-green-600 font-semibold">{{ session('success') }}</div>
                        @endif
                    </div>
                    if (!found) categorized['Overig'].push(key);
                });
                // Render
                Object.keys(categorized).forEach(function(cat) {
                    if (categorized[cat].length === 0) return;
                    // Section
                    const section = document.createElement('div');
                    section.className = 'mb-2';
                    // Header
                    const header = document.createElement('button');
                    header.type = 'button';
                    header.className = 'w-full text-left font-semibold px-2 py-1 bg-gray-100 rounded mb-1';
                    header.textContent = cat;
                    // Toggle
                    const keysContainer = document.createElement('div');
                    keysContainer.style.display = 'none';
                    categorized[cat].forEach(function(key) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'bg-gray-200 rounded px-2 py-1 text-xs font-mono key-btn mb-1 w-full text-left';
                        btn.textContent = key;
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            if (window.CKEDITOR && CKEDITOR.instances['wysiwyg-content']) {
                                CKEDITOR.instances['wysiwyg-content'].focus();
                                CKEDITOR.instances['wysiwyg-content'].insertText(key);
                            } else {
                                var textarea = document.getElementById('wysiwyg-content');
                                if (textarea) {
                                    var start = textarea.selectionStart;
                                    var end = textarea.selectionEnd;
                                    var value = textarea.value;
                                    textarea.value = value.substring(0, start) + key + value.substring(end);
                                    textarea.focus();
                                    textarea.selectionStart = textarea.selectionEnd = start + key.length;
                                }
                            }
                        });
                        keysContainer.appendChild(btn);
                    });
                    header.addEventListener('click', function() {
                        keysContainer.style.display = keysContainer.style.display === 'none' ? 'block' : 'none';
                    });
                    section.appendChild(header);
                    section.appendChild(keysContainer);
                    keysList.appendChild(section);
                });
            }
        });
        </script>
    <!-- Sleutellijst zijpaneel -->
    <div class="w-64 flex flex-col" style="background:transparent; box-shadow:none; margin-top:120px; margin-left:-32px;">
    <h2 class="text-xl font-bold mb-4 text-[#0d9488]">Sleutels</h2>
        <div class="mb-4">
            <div class="flex flex-col gap-2 mb-2">
                <button type="button" class="tab-btn px-3 py-1 rounded text-left shadow" style="background:#c1dfeb; color:#222; width:75%;" data-tab="klant">Klant</button>
                <button type="button" class="tab-btn px-3 py-1 rounded text-left shadow" style="background:#c1dfeb; color:#222; width:75%;" data-tab="bikefit">Bikefit</button>
                <button type="button" class="tab-btn px-3 py-1 rounded text-left shadow" style="background:#c1dfeb; color:#222; width:75%;" data-tab="inspanningstest">Inspanningstest</button>
                <button type="button" class="tab-btn px-3 py-1 rounded text-left shadow" style="background:#c1dfeb; color:#222; width:75%;" data-tab="overig">Overige sleutels</button>
            </div>
            <div id="tab-klant" class="tab-content" style="display:none;">
                <div class="flex flex-col gap-2">
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.voornaam$')">$Klant.voornaam$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.naam$')">$Klant.naam$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.email$')">$Klant.email$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.geboortedatum$')">$Klant.geboortedatum$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.geslacht$')">$Klant.geslacht$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.sport$')">$Klant.sport$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.niveau$')">$Klant.niveau$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.club$')">$Klant.club$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.herkomst$')">$Klant.herkomst$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.status$')">$Klant.status$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.laatste_afspraak$')">$Klant.laatste_afspraak$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Klant.avatar_path$')">$Klant.avatar_path$</button>
                </div>
            </div>
            <div id="tab-bikefit" class="tab-content" style="display:none;">
                <div class="flex flex-col gap-2">
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.fietsmerk$')">$Bikefit.fietsmerk$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.kadermaat$')">$Bikefit.kadermaat$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.bouwjaar$')">$Bikefit.bouwjaar$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.schoenmaat$')">$Bikefit.schoenmaat$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.voetbreedte$')">$Bikefit.voetbreedte$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.voetpositie$')">$Bikefit.voetpositie$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.lengte_cm$')">$Bikefit.lengte_cm$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.binnenbeenlengte_cm$')">$Bikefit.binnenbeenlengte_cm$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.armlengte_cm$')">$Bikefit.armlengte_cm$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.romplengte_cm$')">$Bikefit.romplengte_cm$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.schouderbreedte_cm$')">$Bikefit.schouderbreedte_cm$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.zadel_trapas_hoek$')">$Bikefit.zadel_trapas_hoek$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.zadel_trapas_afstand$')">$Bikefit.zadel_trapas_afstand$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.stuur_trapas_hoek$')">$Bikefit.stuur_trapas_hoek$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.stuur_trapas_afstand$')">$Bikefit.stuur_trapas_afstand$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.zadel_lengte_center_top$')">$Bikefit.zadel_lengte_center_top$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.aanpassingen_zadel$')">$Bikefit.aanpassingen_zadel$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.aanpassingen_setback$')">$Bikefit.aanpassingen_setback$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.aanpassingen_reach$')">$Bikefit.aanpassingen_reach$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.aanpassingen_drop$')">$Bikefit.aanpassingen_drop$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.aanpassingen_stuurpen_aan$')">$Bikefit.aanpassingen_stuurpen_aan$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.aanpassingen_stuurpen_pre$')">$Bikefit.aanpassingen_stuurpen_pre$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.aanpassingen_stuurpen_post$')">$Bikefit.aanpassingen_stuurpen_post$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.type_zadel$')">$Bikefit.type_zadel$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.zadeltil$')">$Bikefit.zadeltil$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.zadelbreedte$')">$Bikefit.zadelbreedte$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.rotatie_aanpassingen$')">$Bikefit.rotatie_aanpassingen$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.inclinatie_aanpassingen$')">$Bikefit.inclinatie_aanpassingen$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.ophoging_li$')">$Bikefit.ophoging_li$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.ophoging_re$')">$Bikefit.ophoging_re$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.opmerkingen$')">$Bikefit.opmerkingen$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.interne_opmerkingen$')">$Bikefit.interne_opmerkingen$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.algemene_klachten$')">$Bikefit.algemene_klachten$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.beenlengteverschil$')">$Bikefit.beenlengteverschil$</button>
                    <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.beenlengteverschil_cm$')">$Bikefit.beenlengteverschil_cm$</button>
                    <div class="mt-4 p-2 bg-blue-50 rounded">
                        <div class="font-semibold text-blue-900 mb-2">Alle beschikbare Bikefit sleutels:</div>
                        <div class="grid grid-cols-1 gap-1 text-xs font-mono">
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.fietsmerk$')">$Bikefit.fietsmerk$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.kadermaat$')">$Bikefit.kadermaat$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.bouwjaar$')">$Bikefit.bouwjaar$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.schoenmaat$')">$Bikefit.schoenmaat$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.voetbreedte$')">$Bikefit.voetbreedte$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.voetpositie$')">$Bikefit.voetpositie$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.lengte_cm$')">$Bikefit.lengte_cm$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.binnenbeenlengte_cm$')">$Bikefit.binnenbeenlengte_cm$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.armlengte_cm$')">$Bikefit.armlengte_cm$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.romplengte_cm$')">$Bikefit.romplengte_cm$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.schouderbreedte_cm$')">$Bikefit.schouderbreedte_cm$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.zadel_trapas_hoek$')">$Bikefit.zadel_trapas_hoek$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.zadel_trapas_afstand$')">$Bikefit.zadel_trapas_afstand$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.stuur_trapas_hoek$')">$Bikefit.stuur_trapas_hoek$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.stuur_trapas_afstand$')">$Bikefit.stuur_trapas_afstand$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.zadel_lengte_center_top$')">$Bikefit.zadel_lengte_center_top$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.aanpassingen_zadel$')">$Bikefit.aanpassingen_zadel$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.aanpassingen_setback$')">$Bikefit.aanpassingen_setback$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.aanpassingen_reach$')">$Bikefit.aanpassingen_reach$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.aanpassingen_drop$')">$Bikefit.aanpassingen_drop$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.aanpassingen_stuurpen_aan$')">$Bikefit.aanpassingen_stuurpen_aan$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.aanpassingen_stuurpen_pre$')">$Bikefit.aanpassingen_stuurpen_pre$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.aanpassingen_stuurpen_post$')">$Bikefit.aanpassingen_stuurpen_post$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.type_zadel$')">$Bikefit.type_zadel$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w-full text-left" onclick="insertKey('$Bikefit.zadeltil$')">$Bikefit.zadeltil$</button>
                            <button type="button" class="bg-gray-200 rounded px-2 py-1 key-btn mb-1 w

