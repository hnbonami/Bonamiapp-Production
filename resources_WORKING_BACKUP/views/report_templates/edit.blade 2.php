@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Bewerk template</h1>
    <form id="template-form" method="POST" action="{{ $template->exists ? route('report_templates.update', $template->id) : route('report_templates.store') }}">
        @csrf
        @if($template->exists) @method('PUT') @endif
        <div class="mb-4">
            <label class="block">Naam</label>
            <input type="text" name="name" value="{{ $template->name }}" class="border rounded p-2 w-full" />
        </div>
        <div class="mb-4">
            <label class="block">Layout JSON (auto gevuld door editor)</label>
            <textarea id="json_layout" name="json_layout" rows="8" class="border rounded w-full p-2">{{ $template->json_layout }}</textarea>
        </div>
        <div class="mb-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <h3 class="font-semibold">Palette</h3>
                    <div class="mt-2 space-y-2">
                        <button type="button" data-type="cover" class="bg-gray-100 p-2 w-full add-block">Cover image</button>
                        <button type="button" data-type="measurements" class="bg-gray-100 p-2 w-full add-block">Measurements</button>
                        <button type="button" data-type="photo_gallery" class="bg-gray-100 p-2 w-full add-block">Photo gallery</button>
                        <button type="button" data-type="text" class="bg-gray-100 p-2 w-full add-block">Free text</button>
                    </div>
                </div>
                <div class="col-span-1">
                    <h3 class="font-semibold">Canvas</h3>
                    <div id="editor-canvas" class="border p-4 min-h-[300px] bg-white">
                        <!-- blocks will be inserted here -->
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold">Properties</h3>
                    <div id="block-properties" class="border p-3 bg-gray-50 min-h-[300px]">
                        <div id="no-selection" class="text-sm text-gray-500">Selecteer een blok om eigenschappen te bewerken.</div>
                        <div id="props-form" style="display:none">
                            <div class="mb-2">
                                <label class="block text-xs">Type</label>
                                <input id="prop-type" class="border rounded p-1 w-full" disabled />
                            </div>
                            <div class="mb-2">
                                <label class="block text-xs">Label</label>
                                <input id="prop-label" class="border rounded p-1 w-full" />
                            </div>
                            <div class="mb-2" id="prop-text-wrap" style="display:none">
                                <label class="block text-xs">Tekst</label>
                                <textarea id="prop-text" rows="4" class="border rounded w-full p-1"></textarea>
                            </div>
                            <div class="flex gap-2 mt-2">
                                <button id="prop-save" class="px-2 py-1 bg-blue-600 text-white rounded">Opslaan</button>
                                <button id="prop-delete" class="px-2 py-1 bg-red-600 text-white rounded">Verwijder blok</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" style="background:#c8e1eb;color:#111;padding:0.25em 0.9em;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;border:none;cursor:pointer;">Opslaan</button>
            @if($template->exists)
                <a href="{{ route('report_templates.index') }}" class="px-3 py-2 bg-gray-200 rounded">Terug</a>
            @endif
        </div>
    </form>
    <hr class="my-4" />
    <h3 class="text-lg">Preview</h3>
    <iframe id="preview-frame" style="width:100%;height:600px;border:1px solid #ddd;margin-top:8px"></iframe>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/gridstack@8.0.0/dist/gridstack-all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/gridstack@8.0.0/dist/gridstack.min.css" rel="stylesheet" />
<script>
document.addEventListener('DOMContentLoaded', function(){
    const canvas = document.getElementById('editor-canvas');
    const jsonArea = document.getElementById('json_layout');
    // properties UI
    const propsForm = document.getElementById('props-form');
    const noSelection = document.getElementById('no-selection');
    const propType = document.getElementById('prop-type');
    const propLabel = document.getElementById('prop-label');
    const propTextWrap = document.getElementById('prop-text-wrap');
    const propText = document.getElementById('prop-text');
    const propSave = document.getElementById('prop-save');
    const propDelete = document.getElementById('prop-delete');
    let selectedIndex = null;

    function loadFromJson(){
        try{ const arr = JSON.parse(jsonArea.value || '[]'); renderCanvas(arr); }catch(e){ /* ignore */ }
    }

    function renderCanvas(arr){
        canvas.innerHTML = '';
        arr.forEach(function(b){
            const el = document.createElement('div'); el.className='p-2 border mb-2 bg-gray-50';
            el.textContent = b.type + (b.label?(': '+b.label):'');
            el.dataset.block = JSON.stringify(b);
            canvas.appendChild(el);
        });
    triggerPreview();
    }

    // make canvas sortable (drag to reorder)
    const s = Sortable.create(canvas, {
        animation: 150,
        onEnd: function(){
            // update JSON order from DOM
            const arr = Array.from(canvas.children).map(c => JSON.parse(c.dataset.block));
            jsonArea.value = JSON.stringify(arr, null, 2);
            triggerPreview();
        }
    });

    document.querySelectorAll('.add-block').forEach(function(btn){
        btn.addEventListener('click', function(){
            const t = this.getAttribute('data-type');
            const arr = JSON.parse(jsonArea.value || '[]');
            arr.push({ type: t, label: t, props: {} });
            jsonArea.value = JSON.stringify(arr, null, 2);
            renderCanvas(arr);
        });
    });

    canvas.addEventListener('click', function(ev){
        const b = ev.target.closest('[data-block]'); if(!b) return;
        const idx = Array.from(canvas.children).indexOf(b);
        selectBlock(idx);
    });

    function selectBlock(idx){
        const arr = JSON.parse(jsonArea.value || '[]');
        const obj = arr[idx]; if(!obj) return;
        selectedIndex = idx;
        noSelection.style.display = 'none'; propsForm.style.display = 'block';
        propType.value = obj.type || '';
        propLabel.value = obj.label || '';
        if(obj.type === 'text') { propTextWrap.style.display = 'block'; propText.value = obj.props?.text || ''; } else { propTextWrap.style.display = 'none'; propText.value = ''; }
    }

    propSave.addEventListener('click', function(ev){ ev.preventDefault(); if(selectedIndex === null) return; const arr = JSON.parse(jsonArea.value || '[]'); const obj = arr[selectedIndex]; obj.label = propLabel.value; if(obj.type === 'text'){ obj.props = obj.props || {}; obj.props.text = propText.value; } arr[selectedIndex] = obj; jsonArea.value = JSON.stringify(arr, null, 2); renderCanvas(arr); selectBlock(selectedIndex); });
    propDelete.addEventListener('click', function(ev){ ev.preventDefault(); if(selectedIndex === null) return; const arr = JSON.parse(jsonArea.value || '[]'); arr.splice(selectedIndex,1); jsonArea.value = JSON.stringify(arr, null, 2); renderCanvas(arr); selectedIndex = null; propsForm.style.display='none'; noSelection.style.display='block'; });

    // initial load
    loadFromJson();
    // preview implementation: POST JSON layout to preview endpoint and fill iframe
    const previewFrame = document.getElementById('preview-frame');
    let previewTimer = null;
    function triggerPreview(){
        clearTimeout(previewTimer);
        previewTimer = setTimeout(()=>{
            const layout = jsonArea.value || '[]';
            fetch('{{ route('report_templates.preview') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({ layout })
            }).then(r=>r.text()).then(html=>{
                previewFrame.srcdoc = html;
            }).catch(e=>{
                previewFrame.srcdoc = '<div style="padding:16px;color:#900">Preview error</div>';
            });
        }, 400);
    }

    jsonArea.addEventListener('input', triggerPreview);
    // initial preview
    triggerPreview();
});
</script>
@endpush

@endsection
