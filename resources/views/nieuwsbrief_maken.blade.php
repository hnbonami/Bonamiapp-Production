@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-4">Nieuw nieuwsbrief ontwerp</h1>

<div class="flex flex-wrap gap-3 mb-4">
    <button id="btn-alle-medewerkers" class="rounded-full px-4 py-1 bg-gray-100 text-gray-800 font-bold text-sm inline-flex items-center gap-2">
        <span>+ Alle medewerkers toevoegen</span>
    </button>
    <button id="btn-preview" class="rounded-full px-4 py-1 bg-blue-100 text-blue-800 font-bold text-sm inline-flex items-center gap-2">
        <span>👁️ Voorbeeld</span>
    </button>
    <button id="btn-testmail" class="rounded-full px-4 py-1 bg-emerald-100 text-emerald-800 font-bold text-sm inline-flex items-center gap-2">
        <span>✉️ Testmail sturen</span>
    </button>
</div>

<div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 md:col-span-4">
        <div class="bg-white rounded-xl shadow p-3">
            <h2 class="font-semibold mb-2">Blokken</h2>
            <div class="space-y-2" id="block-palette">
                <div class="draggable block-item" draggable="true" data-type="title" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Titel</div>
                <div class="draggable block-item" draggable="true" data-type="text" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Tekst</div>
                <div class="draggable block-item" draggable="true" data-type="image" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Afbeelding</div>
                <div class="draggable block-item" draggable="true" data-type="button" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Knop</div>
                <div class="draggable block-item" draggable="true" data-type="spacer" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Witruimte</div>
            </div>
        </div>
    </div>

    <div class="col-span-12 md:col-span-8">
        <div class="bg-white rounded-xl shadow p-3">
            <h2 class="font-semibold mb-2">Canvas</h2>
            <div id="canvas" style="min-height:480px;border:2px dashed #e5e7eb;border-radius:12px;padding:12px;" class="dropzone">
                <p class="text-gray-500">Sleep blokken hierheen…</p>
            </div>
        </div>
    </div>
</div>

<!-- Preview modal -->
<div id="previewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;z-index:50;">
    <div style="background:#fff;border-radius:12px;max-width:900px;width:90%;max-height:85vh;overflow:auto;padding:16px;">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Voorbeeld nieuwsbrief</h3>
            <button onclick="closePreview()" class="rounded-full px-3 py-1 bg-gray-100">Sluiten</button>
        </div>
        <div id="previewContent" class="prose"></div>
    </div>
</div>

<script>
// Eenvoudige drag & drop builder
const palette = document.getElementById('block-palette');
const canvas = document.getElementById('canvas');
let dragType = null;

palette.querySelectorAll('.draggable').forEach(el => {
  el.addEventListener('dragstart', e => { dragType = e.target.dataset.type; });
});

canvas.addEventListener('dragover', e => { e.preventDefault(); });
canvas.addEventListener('drop', e => {
  e.preventDefault();
  if (!dragType) return;
  addBlock(dragType);
  dragType = null;
});

function addBlock(type){
  const wrapper = document.createElement('div');
  wrapper.style.border = '1px solid #e5e7eb';
  wrapper.style.borderRadius = '10px';
  wrapper.style.padding = '10px';
  wrapper.style.margin = '8px 4px';
  wrapper.style.background = '#fafafa';

  const header = document.createElement('div');
  header.style.display = 'flex';
  header.style.justifyContent = 'space-between';
  header.style.alignItems = 'center';
  header.style.marginBottom = '6px';
  header.innerHTML = `<span style="font-weight:600;color:#374151">${type.toUpperCase()}</span>
    <span>
      <button onclick="moveUp(this)" class="rounded-full px-2 py-0.5 bg-gray-100 text-xs">▲</button>
      <button onclick="moveDown(this)" class="rounded-full px-2 py-0.5 bg-gray-100 text-xs">▼</button>
      <button onclick="this.closest('.newsletter-block').remove()" class="rounded-full px-2 py-0.5 bg-rose-100 text-rose-700 text-xs">Verwijder</button>
    </span>`;

  const content = document.createElement('div');
  content.contentEditable = true;
  content.style.outline = 'none';
  content.style.minHeight = '28px';
  content.style.padding = '6px 8px';
  content.style.background = '#fff';
  content.innerHTML = defaultContent(type);

  wrapper.className = 'newsletter-block';
  wrapper.dataset.type = type;
  wrapper.appendChild(header);
  wrapper.appendChild(content);
  canvas.appendChild(wrapper);
}

function defaultContent(type){
  switch(type){
    case 'title': return '<h2 style="margin:0">Nieuwe titel</h2>';
    case 'text': return '<p>Je tekst hier…</p>';
    case 'image': return '<p><em>Plaats hier later een afbeelding</em></p>';
    case 'button': return '<a href="#" style="display:inline-block;background:#111;color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none;">Call to action</a>';
    case 'spacer': return '<div style="height:24px"></div>';
    default: return '<p>Blok</p>';
  }
}

function moveUp(btn){
  const block = btn.closest('.newsletter-block');
  if (block.previousElementSibling) {
    canvas.insertBefore(block, block.previousElementSibling);
  }
}
function moveDown(btn){
  const block = btn.closest('.newsletter-block');
  if (block.nextElementSibling) {
    canvas.insertBefore(block.nextElementSibling, block);
  }
}

// Preview
function closePreview(){ document.getElementById('previewModal').style.display='none'; }
function openPreview(){
  const html = Array.from(canvas.querySelectorAll('.newsletter-block > div:last-child'))
    .map(div => div.innerHTML).join('');
  document.getElementById('previewContent').innerHTML = html || '<p><em>Geen inhoud</em></p>';
  document.getElementById('previewModal').style.display='flex';
}

document.getElementById('btn-preview').addEventListener('click', openPreview);

document.getElementById('btn-testmail').addEventListener('click', () => {
  alert('Testmail wordt later toegevoegd. Dit is een placeholder.');
});

document.getElementById('btn-alle-medewerkers').addEventListener('click', () => {
  alert('Alle medewerkers toevoegen (placeholder). Later koppelen aan selectie van medewerkers.');
});
</script>
@endsection
