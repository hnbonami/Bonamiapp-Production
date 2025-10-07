<script>
function insertKey(key) {
    if (window.CKEDITOR && CKEDITOR.instances) {
        for (var name in CKEDITOR.instances) {
            if (CKEDITOR.instances.hasOwnProperty(name)) {
                var editor = CKEDITOR.instances[name];
                if (editor.focusManager.hasFocus) {
                    editor.insertText(key);
                    return;
                }
            }
        }
        // Fallback: insert in the last editor
        var lastEditor = null;
        for (var name in CKEDITOR.instances) {
            if (CKEDITOR.instances.hasOwnProperty(name)) {
                lastEditor = CKEDITOR.instances[name];
            }
        }
        if (lastEditor) {
            lastEditor.focus();
            lastEditor.insertText(key);
            return;
        }
    }
    // Fallback: textarea
    var textareas = document.querySelectorAll('.wysiwyg-content');
    if (textareas.length > 0) {
        var textarea = textareas[textareas.length - 1];
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var value = textarea.value;
        textarea.value = value.substring(0, start) + key + value.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + key.length;
    }
}
</script>
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
@extends('layouts.app')

@section('content')
<div class="min-h-screen flex justify-center items-start" style="background: #fff;">
    <div class="flex w-full max-w-6xl mt-10" style="gap:0; align-items:flex-start; min-height:600px;">
        <div style="width:256px; min-width:200px; margin-top:24px; margin-left:-8px;">
            <div class="rounded-2xl p-4 flex flex-col" style="background:#fff; box-shadow:none;">
                <!-- Sidebar ...existing code... -->
            </div>
        </div>
    <div style="flex:1 1 0%; min-width:500px; margin-right:32px; margin-left:-8px;">
            <div class="rounded-2xl p-8" style="background:#fff; box-shadow:none;">
                <h1 class="text-3xl font-bold mb-4 text-left" style="margin-top:32px;">Sjabloon bewerken</h1>
                <form action="{{ route('temp.show', $template) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-6">
                        <label for="type" class="block font-semibold mb-2">Type *</label>
                        <input type="text" name="type" id="type" class="form-input max-w-xs border border-gray-300 rounded px-3 py-2" value="{{ $template->type }}" required>
                    </div>
                    <div class="mb-6">
                        <label for="name" class="block font-semibold mb-2">Naam *</label>
                        <input type="text" name="name" id="name" class="form-input max-w-xs border border-gray-300 rounded px-3 py-2" value="{{ $template->name }}" required>
                    </div>
                    <div class="mb-6">
                        <label class="block font-semibold mb-1">Sjabloon inhoud *</label>
                        <div class="a4-editor-wrapper" style="max-width:210mm; margin-left:0; margin-top:18px;">
                        @php
                            $pages = [];
                            if ($template->html_contents) {
                                $decoded = json_decode($template->html_contents, true);
                                if (is_array($decoded)) {
                                    $pages = $decoded;
                                } elseif (is_string($decoded)) {
                                    $pages = [$decoded];
                                }
                            } elseif ($template->html_content) {
                                $pages = [$template->html_content];
                            }
                            if (empty($pages) && $template->html_content) {
                                $pages = [$template->html_content];
                            }
                            $backgrounds = [];
                            if ($template->background_images) {
                                $arr = json_decode($template->background_images, true);
                                foreach ($arr as $img) {
                                    $backgrounds[] = $img['path'] ?? '';
                                }
                            }
                        @endphp
                    <div class="mb-6">
                        <label for="name" class="block font-semibold mb-2">Naam *</label>
                        <input type="text" name="name" id="name" class="form-input w-full border border-gray-300 rounded px-3 py-2" value="{{ $template->name }}" required>
                    </div>
                    <div class="mb-6">
                        <label class="block font-semibold mb-2">Sjabloon inhoud *</label>
                        <div class="a4-editor-wrapper" style="max-width:210mm; margin-left:auto; margin-right:auto;">
                        @php
                            $pages = [];
                            if ($template->html_contents) {
                                $decoded = json_decode($template->html_contents, true);
                                if (is_array($decoded)) {
                                    $pages = $decoded;
                                } elseif (is_string($decoded)) {
                                    $pages = [$decoded];
                                }
                            } elseif ($template->html_content) {
                                $pages = [$template->html_content];
                            }
                            if (empty($pages) && $template->html_content) {
                                $pages = [$template->html_content];
                            }
                            $backgrounds = [];
                            if ($template->background_images) {
                                $arr = json_decode($template->background_images, true);
                                foreach ($arr as $img) {
                                    $backgrounds[] = $img['path'] ?? '';
                                }
                            }
                        @endphp
                        <!-- DEBUG: Toon inhoud van html_contents en pages -->
                        <div id="ckeditor-pages-container">
                            @foreach($pages as $i => $page)
                                <textarea name="html_contents[]" id="wysiwyg-content-{{ $i + 1 }}" class="form-input w-full border border-gray-300 rounded px-3 py-2 wysiwyg-content" rows="10">{{ $page }}</textarea>
                                <input type="hidden" name="background_urls[]" id="background_url_{{ $i + 1 }}" value="{{ isset($backgrounds[$i]) ? $backgrounds[$i] : '' }}">
                                <div class="flex gap-2 mt-2 editor-btn-row" data-index="{{ $i + 1 }}"></div>
                            @endforeach
                        </div>
                        </div>
                        <button type="button" id="add-page-btn" class="mt-2 px-4 py-2 bg-blue-100 text-blue-900 rounded">Pagina toevoegen</button>
                        <script src="/ckeditor/ckeditor/ckeditor.js"></script>
                        <script>
                        let pageCount = {{ count($pages) }};
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
                        // Initialiseer CKEditor voor alle bestaande textarea's
                        for (let i = 1; i <= pageCount; i++) {
                            if (!CKEDITOR.instances['wysiwyg-content-' + i]) {
                                CKEDITOR.replace('wysiwyg-content-' + i, {
                                    height: 1123,
                                    width: '794px',
                                    contentsCss: '/ckeditor/ckeditor/contents.css',
                                    bodyClass: 'a4-body',
                                });
                            }
                            addEditorButtons(i);
                        }
                        document.getElementById('add-page-btn').addEventListener('click', addPage);
                        // CKEditor sync bij submit
                        document.querySelector('form').addEventListener('submit', function(e) {
                            if (window.CKEDITOR) {
                                for (var instanceName in CKEDITOR.instances) {
                                    if (CKEDITOR.instances.hasOwnProperty(instanceName)) {
                                        CKEDITOR.instances[instanceName].updateElement();
                                    }
                                }
                            }
                        });
                        </script>
                    </div>
                    <div class="flex gap-3 justify-start mt-6">
                        <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded shadow hover:bg-blue-700 transition">Opslaan</button>
                        <a href="{{ route('templates.index') }}" class="px-5 py-2 bg-gray-100 text-gray-900 font-semibold rounded shadow hover:bg-gray-200 transition">Annuleer</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="w-64 flex flex-col absolute right-0 top-0" style="background:transparent; box-shadow:none; margin-top:120px; margin-right:32px;">
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
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.lengte_cm$')">$Bikefit.lengte_cm$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.binnenbeenlengte_cm$')">$Bikefit.binnenbeenlengte_cm$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.schoenmaat$')">$Bikefit.schoenmaat$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.voetbreedte$')">$Bikefit.voetbreedte$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.voetpositie$')">$Bikefit.voetpositie$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.aanpassingen_setback$')">$Bikefit.aanpassingen_setback$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.aanpassingen_reach$')">$Bikefit.aanpassingen_reach$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.aanpassingen_drop$')">$Bikefit.aanpassingen_drop$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.zadel_trapas_hoek$')">$Bikefit.zadel_trapas_hoek$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.zadel_trapas_afstand$')">$Bikefit.zadel_trapas_afstand$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.stuur_trapas_hoek$')">$Bikefit.stuur_trapas_hoek$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.stuur_trapas_afstand$')">$Bikefit.stuur_trapas_afstand$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.zadeltil$')">$Bikefit.zadeltil$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.zadelbreedte$')">$Bikefit.zadelbreedte$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.opmerkingen$')">$Bikefit.opmerkingen$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.datum$')">$Bikefit.datum$</button>
                    </div>
                </div>
                <div id="tab-inspanningstest" class="tab-content" style="display:none;">
                    <div class="flex flex-col gap-2">
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Inspanningstest.naam$')">$Inspanningstest.naam$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Inspanningstest.datum$')">$Inspanningstest.datum$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Inspanningstest.max_vermogen$')">$Inspanningstest.max_vermogen$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Inspanningstest.vo2max$')">$Inspanningstest.vo2max$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Inspanningstest.anaerobe_drempel$')">$Inspanningstest.anaerobe_drempel$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Inspanningstest.hartslag_max$')">$Inspanningstest.hartslag_max$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Inspanningstest.hartslag_drempel$')">$Inspanningstest.hartslag_drempel$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Inspanningstest.opmerkingen$')">$Inspanningstest.opmerkingen$</button>
                    </div>
                </div>
                <div id="tab-overig" class="tab-content" style="display:none;">
                    <div class="flex flex-col gap-2">
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$ResultatenVoor$')">$ResultatenVoor$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$ResultatenNa$')">$ResultatenNa$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$MobiliteitTabel$')">$MobiliteitTabel$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.prognose_zitpositie_html$')">$Bikefit.prognose_zitpositie_html$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$Bikefit.body_measurements_block_html$')">$Bikefit.body_measurements_block_html$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$mobiliteit_tabel_html$')">$mobiliteit_tabel_html$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$mobility_table_report$')">$mobility_table_report$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$_mobility_table_report$')">$_mobility_table_report$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$_mobility_results$')">$_mobility_results$</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$MobiliteitTabel1$')">$MobiliteitTabel1$ (versie 1)</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$MobiliteitTabel2$')">$MobiliteitTabel2$ (versie 2)</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$MobiliteitTabel3$')">$MobiliteitTabel3$ (versie 3)</button>
                        <button type="button" class="sleutel-item px-2 py-1 rounded bg-gray-100 mb-1 text-left w-full" onclick="insertKey('$mobiliteitklant$')">$mobiliteitklant$ (NIEUWE VERSIE!)</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
