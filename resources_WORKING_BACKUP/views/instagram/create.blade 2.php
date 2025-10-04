@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold" style="margin-top:6px;margin-bottom:2.2em;">Instagram post maken</h1>
<div style="margin-bottom:2.5em;display:flex;flex-wrap:wrap;gap:1.2em;justify-content:flex-start;">
  <a href="{{ route('instagram.index') }}" style="background:#c8e1eb;color:#111;padding:0.5em 1.2em;border-radius:7px;text-decoration:none;font-weight:600;font-size:1em;box-shadow:0 1px 3px #e0e7ff;">Opgeslagen posts</a>
  <button id="downloadBtn" style="background:#c8e1eb;color:#111;padding:0.5em 1.2em;border-radius:7px;text-decoration:none;font-weight:600;font-size:1em;box-shadow:0 1px 3px #e0e7ff;" title="Download voor Instagram (1080×1080)">Download voor Instagram</button>
  <button id="shareBtn" style="background:#c8e1eb;color:#111;padding:0.5em 1.2em;border-radius:7px;text-decoration:none;font-weight:600;font-size:1em;box-shadow:0 1px 3px #e0e7ff;" title="Deel via apps (indien ondersteund)">Delen…</button>
  <button id="saveBtn" style="background:#c8e1eb;color:#111;padding:0.5em 1.2em;border-radius:7px;text-decoration:none;font-weight:600;font-size:1em;box-shadow:0 1px 3px #e0e7ff;">@if(isset($post)) Bijwerken @else Opslaan @endif</button>
</div>

<div class="max-w-7xl mx-auto px-4">
<div class="flex flex-row items-start gap-6">
  <div class="flex-1 min-w-[320px]">
    <div class="bg-white rounded-xl shadow p-3">
      <div id="canvasWrapper" class="relative mx-auto md:mx-0" style="aspect-ratio:1/1;width:520px;">
        <div id="canvas" class="absolute inset-0 rounded-lg overflow-hidden bg-gray-100" style="touch-action:none;">
  <img id="bgImage" src="" alt="achtergrond" class="w-full h-full object-cover select-none pointer-events-none" />
  <div id="titleLayer" class="absolute cursor-move" style="left:5%;top:10%;max-width:95%;width:90%;box-sizing:border-box;outline:1px solid rgba(255,255,255,0.95);box-shadow:0 0 0 1px rgba(0,0,0,0.25);border-radius:6px;">
          <div class="select-none" id="titleText" style="color:#111;font-size:48px;font-weight:800;line-height:1.2;font-family:'Figtree', Arial, sans-serif;text-align:left;">Titel</div>
          <div id="titleResize" class="absolute" style="right:-6px;bottom:-6px;width:12px;height:12px;background:#3b82f6;border-radius:2px;cursor:se-resize;"></div>
  <div id="bodyLayer" class="absolute cursor-move" style="left:5%;top:35%;max-width:95%;width:90%;box-sizing:border-box;outline:1px solid rgba(255,255,255,0.95);box-shadow:0 0 0 1px rgba(0,0,0,0.25);border-radius:6px;">
          <div class="select-none" id="bodyText" style="color:#111;font-size:28px;font-weight:500;line-height:1.4;font-family:'Figtree', Arial, sans-serif;text-align:left;">Je tekst hier…</div>
          <div id="bodyResize" class="absolute" style="right:-6px;bottom:-6px;width:12px;height:12px;background:#3b82f6;border-radius:2px;cursor:se-resize;"></div>
        </div>
        </div>
      </div>
      <div id="previewNote" class="text-xs text-gray-500 mt-2 text-center">Voorvertoning; export is 1080×1080.</div>
    </div>
  </div>

  <div class="w-[340px] shrink-0">
    <div class="bg-white rounded-xl shadow p-4 sticky top-8 mb-24 pb-6">
      <h2 class="font-semibold mb-3">Canvas</h2>
      <div class="mb-3">
        <label for="canvasVariant" class="block text-sm text-gray-600 mb-1">Formaat</label>
        <select id="canvasVariant" class="w-full border rounded-md px-3 py-2">
          <option value="square">Vierkant — 1080×1080</option>
          <option value="portrait">Staand — 1080×1350</option>
          <option value="landscape">Liggend — 1080×566</option>
          <option value="story">Story — 1080×1920</option>
        </select>
      </div>
      <hr class="my-4">
      <h2 class="font-semibold mb-3">Achtergrond</h2>
  <div class="flex flex-wrap items-center gap-2 mb-2">
        <input type="file" id="bgInput" accept="image/*" class="hidden" />
        <button id="bgUploadBtn" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-gray-100 hover:bg-gray-200" title="Achtergrond uploaden" aria-label="Achtergrond uploaden">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2-2h4a2 2 0 012 2v6m-2 4H5a2 2 0 01-2-2V7m18 10l-3-3-4 4-3-3-4 4"/></svg>
        </button>
        <button id="bgClearBtn" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-gray-100 hover:bg-gray-200" title="Achtergrond verwijderen" aria-label="Achtergrond verwijderen">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2m1 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7m3 4v6m4-6v6"/></svg>
        </button>
      </div>
  <div id="uploadStatus" class="text-xs text-gray-500 mb-3">Tip: we verkleinen grote afbeeldingen automatisch voor upload.</div>
      <div class="mb-3 space-y-3">
        <div class="flex items-center gap-3">
          <label for="bgColor" class="text-sm text-gray-600 whitespace-nowrap">Achtergrondkleur</label>
          <input id="bgColor" type="color" value="#f3f4f6" class="w-10 h-8 p-0 border rounded" />
        </div>
        <div>
          <label for="bgOpacity" class="block text-sm text-gray-600">Transparantie foto</label>
          <input id="bgOpacity" type="range" min="0" max="100" value="100" class="w-full" />
          <div class="text-[11px] text-gray-500">0% = volledig transparant, 100% = volledig zichtbaar</div>
        </div>
      </div>
      

      <hr class="my-4">
      <h2 class="font-semibold mb-2">Titel</h2>
      <div class="space-y-2">
        <input id="titleInput" type="text" class="w-full border rounded-md px-3 py-2" value="Titel" />
  <div class="flex flex-wrap items-center gap-1">
          <label class="text-sm text-gray-600">Kleur</label>
          <input id="titleColor" type="color" value="#111111" class="w-8 h-8 p-0 border rounded" />
          <label class="text-sm text-gray-600 ml-3">Grootte</label>
          <input id="titleSize" type="number" min="10" max="160" value="48" class="w-20 border rounded px-2 py-1" />
        </div>
  <div class="flex flex-wrap items-center gap-1">
          <label class="text-sm text-gray-600">Lettertype</label>
          <select id="titleFont" class="border rounded px-2 py-1">
            <option value="Figtree, Arial, sans-serif">Figtree</option>
            <option value="Arial, Helvetica, sans-serif">Arial</option>
            <option value="'Times New Roman', Times, serif">Times</option>
            <option value="Georgia, serif">Georgia</option>
            <option value="'Courier New', Courier, monospace">Courier</option>
          </select>
          <button id="titleBold" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm" title="Vet">B</button>
          <button id="titleItalic" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm italic" title="Cursief">I</button>
          <button id="titleUnderline" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm underline" title="Onderlijn">U</button>
          <span class="text-sm text-gray-600 ml-2">Uitlijning</span>
          <button id="titleAlignLeft" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm" title="Links">L</button>
          <button id="titleAlignCenter" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm" title="Centreren">C</button>
          <button id="titleAlignRight" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm" title="Rechts">R</button>
        </div>
        <div class="flex items-center gap-2 mt-2">
          <button id="bgUploadBtnTitle" class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-gray-100 hover:bg-gray-200" title="Achtergrond uploaden" aria-label="Achtergrond uploaden">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2-2h4a2 2 0 012 2v6m-2 4H5a2 2 0 01-2-2V7m18 10l-3-3-4 4-3-3-4 4"/></svg>
          </button>
          <button id="bgClearBtnTitle" class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-gray-100 hover:bg-gray-200" title="Achtergrond verwijderen" aria-label="Achtergrond verwijderen">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2m1 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7m3 4v6m4-6v6"/></svg>
          </button>
        </div>
      </div>

      <hr class="my-4">
      <h2 class="font-semibold mb-2">Tekst</h2>
      <div class="space-y-2">
        <textarea id="bodyInput" rows="4" class="w-full border rounded-md px-3 py-2">Je tekst hier…</textarea>
  <div class="flex flex-wrap items-center gap-1">
          <label class="text-sm text-gray-600">Kleur</label>
          <input id="bodyColor" type="color" value="#111111" class="w-8 h-8 p-0 border rounded" />
          <label class="text-sm text-gray-600 ml-3">Grootte</label>
          <input id="bodySize" type="number" min="10" max="120" value="28" class="w-20 border rounded px-2 py-1" />
        </div>
  <div class="flex flex-wrap items-center gap-1">
          <label class="text-sm text-gray-600">Lettertype</label>
          <select id="bodyFont" class="border rounded px-2 py-1">
            <option value="Figtree, Arial, sans-serif">Figtree</option>
            <option value="Arial, Helvetica, sans-serif">Arial</option>
            <option value="'Times New Roman', Times, serif">Times</option>
            <option value="Georgia, serif">Georgia</option>
            <option value="'Courier New', Courier, monospace">Courier</option>
          </select>
          <button id="bodyBold" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm" title="Vet">B</button>
          <button id="bodyItalic" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm italic" title="Cursief">I</button>
          <button id="bodyUnderline" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm underline" title="Onderlijn">U</button>
          <span class="text-sm text-gray-600 ml-2">Uitlijning</span>
          <button id="bodyAlignLeft" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm" title="Links">L</button>
          <button id="bodyAlignCenter" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm" title="Centreren">C</button>
          <button id="bodyAlignRight" class="inline-flex items-center justify-center w-6 h-6 text-[10px] leading-none border rounded-sm" title="Rechts">R</button>
        </div>
        <div class="flex items-center gap-2 mt-2">
          <button id="bgUploadBtnBody" class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-gray-100 hover:bg-gray-200" title="Achtergrond uploaden" aria-label="Achtergrond uploaden">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2-2h4a2 2 0 012 2v6m-2 4H5a2 2 0 01-2-2V7m18 10l-3-3-4 4-3-3-4 4"/></svg>
          </button>
          <button id="bgClearBtnBody" class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-gray-100 hover:bg-gray-200" title="Achtergrond verwijderen" aria-label="Achtergrond verwijderen">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2m1 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7m3 4v6m4-6v6"/></svg>
          </button>
        </div>
      </div>

      <hr class="my-4">
      <label class="text-sm text-gray-600">Post titel (optioneel)</label>
      <input id="postTitle" type="text" class="w-full border rounded-md px-3 py-2" placeholder="Interne titel" />
    </div>
  </div>
</div>
</div>

<!-- html2canvas voor export -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
  const csrf = '{{ csrf_token() }}';
  const uploadUrl = "{{ route('instagram.upload') }}";
  const storeUrl = "{{ route('instagram.store') }}";
  const editId = @json(isset($post) ? $post->id : null);

  const canvas = document.getElementById('canvas');
  const canvasWrapper = document.getElementById('canvasWrapper');
  const bgImage = document.getElementById('bgImage');
  const titleLayer = document.getElementById('titleLayer');
  const bodyLayer = document.getElementById('bodyLayer');
  const titleText = document.getElementById('titleText');
  const bodyText = document.getElementById('bodyText');

  // Inputs
  const bgInput = document.getElementById('bgInput');
  const bgUploadBtn = document.getElementById('bgUploadBtn');
  const bgClearBtn = document.getElementById('bgClearBtn');
  const bgUploadBtnTitle = document.getElementById('bgUploadBtnTitle');
  const bgClearBtnTitle = document.getElementById('bgClearBtnTitle');
  const bgUploadBtnBody = document.getElementById('bgUploadBtnBody');
  const bgClearBtnBody = document.getElementById('bgClearBtnBody');
  const bgColorInput = document.getElementById('bgColor');
  const bgOpacityInput = document.getElementById('bgOpacity');
  const canvasVariantSelect = document.getElementById('canvasVariant');
  const titleInput = document.getElementById('titleInput');
  const titleColor = document.getElementById('titleColor');
  const titleSize = document.getElementById('titleSize');
  const titleFont = document.getElementById('titleFont');
  const titleBold = document.getElementById('titleBold');
  const titleItalic = document.getElementById('titleItalic');
  const titleUnderline = document.getElementById('titleUnderline');
  const titleAlignLeft = document.getElementById('titleAlignLeft');
  const titleAlignCenter = document.getElementById('titleAlignCenter');
  const titleAlignRight = document.getElementById('titleAlignRight');
  const bodyInput = document.getElementById('bodyInput');
  const bodyColor = document.getElementById('bodyColor');
  const bodySize = document.getElementById('bodySize');
  const bodyFont = document.getElementById('bodyFont');
  const bodyBold = document.getElementById('bodyBold');
  const bodyItalic = document.getElementById('bodyItalic');
  const bodyUnderline = document.getElementById('bodyUnderline');
  const bodyAlignLeft = document.getElementById('bodyAlignLeft');
  const bodyAlignCenter = document.getElementById('bodyAlignCenter');
  const bodyAlignRight = document.getElementById('bodyAlignRight');
  const titleResize = document.getElementById('titleResize');
  const bodyResize = document.getElementById('bodyResize');
  const editLayers = [document.getElementById('titleLayer'), document.getElementById('bodyLayer')];
  const postTitle = document.getElementById('postTitle');
  const downloadBtn = document.getElementById('downloadBtn');
  const shareBtn = document.getElementById('shareBtn');
  const saveBtn = document.getElementById('saveBtn');

  let bgPath = null;
  let bgTemplate = null; // legacy: template feature removed
  // Canvas variants
  const CANVAS_VARIANTS = {
    square:  { key:'square',  width:1080, height:1080, ratio:'1 / 1' },
    portrait: { key:'portrait', width:1080, height:1350, ratio:'1080 / 1350' },
    landscape:{ key:'landscape', width:1080, height:566,  ratio:'1080 / 566' },
    story:   { key:'story',   width:1080, height:1920, ratio:'1080 / 1920' }
  };
  let currentVariant = CANVAS_VARIANTS.square;
  function applyCanvasVariant(key){
    const cfg = CANVAS_VARIANTS[key] || CANVAS_VARIANTS.square;
    currentVariant = cfg;
    if (canvasWrapper && cfg?.ratio) {
      canvasWrapper.style.aspectRatio = cfg.ratio;
    }
    const btn = document.getElementById('downloadBtn');
    if (btn) btn.title = `Download (${cfg.width}×${cfg.height})`;
    const note = document.getElementById('previewNote');
    if (note) note.textContent = `Voorvertoning; export is ${cfg.width}×${cfg.height}.`;
  }
  applyCanvasVariant('square');
  canvasVariantSelect?.addEventListener('change', (e)=> applyCanvasVariant(e.target.value));
  // Initialize background color and opacity
  (function initBackgroundControls(){
    const initialColor = bgColorInput?.value || '#f3f4f6';
    document.getElementById('canvas').style.backgroundColor = initialColor;
    if (bgOpacityInput) bgImage.style.opacity = (parseInt(bgOpacityInput.value||'100',10)/100).toString();
  })();

  // Helper: downscale large images client-side to avoid server limits
  async function downscaleIfNeeded(file, maxSide=1600, quality=0.85){
    if (!/^image\//.test(file.type)) return file;
    // If smaller than ~1.8MB, skip downscale
    if (file.size < 1800 * 1024) return file;
    const dataUrl = await new Promise((resolve, reject)=>{
      const fr = new FileReader();
      fr.onload = () => resolve(fr.result);
      fr.onerror = reject;
      fr.readAsDataURL(file);
    });
    const img = await new Promise((resolve, reject)=>{
      const i = new Image(); i.onload = () => resolve(i); i.onerror = reject; i.src = dataUrl;
    });
    const w = img.naturalWidth, h = img.naturalHeight;
    const scale = Math.min(1, maxSide / Math.max(w, h));
    const cw = Math.max(1, Math.round(w * scale));
    const ch = Math.max(1, Math.round(h * scale));
    const c = document.createElement('canvas'); c.width = cw; c.height = ch;
    const ctx = c.getContext('2d');
    ctx.drawImage(img, 0, 0, cw, ch);
    const blob = await new Promise((resolve)=> c.toBlob(resolve, 'image/jpeg', quality));
    if (!blob) return file;
    return new File([blob], (file.name || 'upload') + '.jpg', { type: 'image/jpeg' });
  }

  // Upload background
  bgUploadBtn.addEventListener('click', () => bgInput.click());
  bgUploadBtnTitle?.addEventListener('click', () => bgInput.click());
  bgUploadBtnBody?.addEventListener('click', () => bgInput.click());
  bgInput.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const status = document.getElementById('uploadStatus');
    status.textContent = 'Bezig met verwerken…';
    const fd = new FormData();
    let toSend = file;
    try { toSend = await downscaleIfNeeded(file); } catch(_) {}
    fd.append('image', toSend);
    try {
      const res = await fetch(uploadUrl, { method:'POST', headers:{'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest'}, body: fd });
      if (!res.ok) {
        const err = await res.text();
        throw new Error(err || 'Upload mislukt');
      }
      const data = await res.json();
      if (!data || !data.url) throw new Error('Geen geldige server-respons');
      bgImage.style.opacity = '1';
      bgImage.src = data.url;
      bgImage.onload = () => { bgImage.style.opacity = '1'; };
      bgImage.onerror = () => { bgImage.style.opacity = '1'; };
      bgPath = data.path;
      status.textContent = 'Afbeelding ingesteld.';
    } catch (err) {
      console.error(err);
      status.textContent = 'Upload mislukt. Probeer een kleinere afbeelding.';
    }
  });
  bgClearBtn.addEventListener('click', () => { bgImage.style.opacity = '1'; bgImage.src = ''; bgPath = null; });
  bgClearBtnTitle?.addEventListener('click', () => { bgImage.style.opacity = '1'; bgImage.src = ''; bgPath = null; });
  bgClearBtnBody?.addEventListener('click', () => { bgImage.style.opacity = '1'; bgImage.src = ''; bgPath = null; });

  // Background color and opacity handlers
  bgColorInput?.addEventListener('input', () => {
    document.getElementById('canvas').style.backgroundColor = bgColorInput.value;
  });
  bgOpacityInput?.addEventListener('input', () => {
    const v = Math.max(0, Math.min(100, parseInt(bgOpacityInput.value||'100',10)));
    bgImage.style.opacity = (v/100).toString();
  });

  // Template slots removed

  // Normalize initial % positions to px so dragging doesn't jump
  function normalizeLayerPosition(el){
    const rect = canvas.getBoundingClientRect();
    const cs = window.getComputedStyle(el);
    const left = cs.left || '0px';
    const top = cs.top || '0px';
    if (left.includes('%')) {
      const pct = parseFloat(left);
      const px = Math.round((pct / 100) * rect.width);
      el.style.left = px + 'px';
    }
    if (top.includes('%')) {
      const pct = parseFloat(top);
      const px = Math.round((pct / 100) * rect.height);
      el.style.top = px + 'px';
    }
  }

  // Normalize width from % to px for precise drag/resize
  function normalizeLayerWidth(el){
    const rect = canvas.getBoundingClientRect();
    const cs = window.getComputedStyle(el);
    const w = cs.width || '0px';
    if (w.includes('%')) {
      const pct = parseFloat(w);
      const px = Math.round((pct / 100) * rect.width);
      el.style.width = px + 'px';
    }
  }

  normalizeLayerPosition(titleLayer);
  normalizeLayerPosition(bodyLayer);
  normalizeLayerWidth(titleLayer);
  normalizeLayerWidth(bodyLayer);

  // Helpers to enable dragging on a layer
  let resizingLayer = null;
  function makeDraggable(el){
    let dragging = false, startX = 0, startY = 0, baseLeft = 0, baseTop = 0;
    const onDown = (e) => {
      if (resizingLayer) return; // don't drag while resizing
      dragging = true;
      const rect = el.parentElement.getBoundingClientRect();
      const ev = e.touches ? e.touches[0] : e;
      startX = ev.clientX; startY = ev.clientY;
      const style = window.getComputedStyle(el);
      baseLeft = parseFloat(style.left || '0');
      baseTop = parseFloat(style.top || '0');
      e.preventDefault();
    };
    const onMove = (e) => {
      if (!dragging) return;
      const ev = e.touches ? e.touches[0] : e;
      const dx = ev.clientX - startX;
      const dy = ev.clientY - startY;
      el.style.left = (baseLeft + dx) + 'px';
      el.style.top = (baseTop + dy) + 'px';
    };
    const onUp = () => dragging = false;
    el.addEventListener('mousedown', onDown);
    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onUp);
    el.addEventListener('touchstart', onDown, {passive:false});
    window.addEventListener('touchmove', onMove, {passive:false});
    window.addEventListener('touchend', onUp);
  }
  makeDraggable(titleLayer);
  makeDraggable(bodyLayer);

  // Bind text + style controls
  function applyTextStyles(target, {color, size, font, bold, italic, underline, align}){
    if (color) target.style.color = color;
    if (size) target.style.fontSize = size + 'px';
    if (font) target.style.fontFamily = font;
    target.style.fontWeight = bold ? '800' : '500';
    target.style.fontStyle = italic ? 'italic' : 'normal';
    target.style.textDecoration = underline ? 'underline' : 'none';
    if (align) target.style.textAlign = align;
  }
  titleInput.addEventListener('input', () => titleText.textContent = titleInput.value);
  titleColor.addEventListener('input', () => applyTextStyles(titleText, {color:titleColor.value}));
  titleSize.addEventListener('input', () => applyTextStyles(titleText, {size: parseInt(titleSize.value||48)}));
  titleFont.addEventListener('change', () => applyTextStyles(titleText, {font:titleFont.value}));
  let titleState = { bold:true, italic:false, underline:false, align:'left' };
  titleBold.addEventListener('click', () => { titleState.bold = !titleState.bold; applyTextStyles(titleText, {...titleState}); });
  titleItalic.addEventListener('click', () => { titleState.italic = !titleState.italic; applyTextStyles(titleText, {...titleState}); });
  titleUnderline.addEventListener('click', () => { titleState.underline = !titleState.underline; applyTextStyles(titleText, {...titleState}); });
  titleAlignLeft.addEventListener('click', () => { titleState.align = 'left'; applyTextStyles(titleText, {...titleState}); });
  titleAlignCenter.addEventListener('click', () => { titleState.align = 'center'; applyTextStyles(titleText, {...titleState}); });
  titleAlignRight.addEventListener('click', () => { titleState.align = 'right'; applyTextStyles(titleText, {...titleState}); });

  bodyInput.addEventListener('input', () => bodyText.textContent = bodyInput.value);
  bodyColor.addEventListener('input', () => applyTextStyles(bodyText, {color:bodyColor.value}));
  bodySize.addEventListener('input', () => applyTextStyles(bodyText, {size: parseInt(bodySize.value||28)}));
  bodyFont.addEventListener('change', () => applyTextStyles(bodyText, {font:bodyFont.value}));
  let bodyState = { bold:false, italic:false, underline:false, align:'left' };
  bodyBold.addEventListener('click', () => { bodyState.bold = !bodyState.bold; applyTextStyles(bodyText, {...bodyState}); });
  bodyItalic.addEventListener('click', () => { bodyState.italic = !bodyState.italic; applyTextStyles(bodyText, {...bodyState}); });
  bodyUnderline.addEventListener('click', () => { bodyState.underline = !bodyState.underline; applyTextStyles(bodyText, {...bodyState}); });
  bodyAlignLeft.addEventListener('click', () => { bodyState.align = 'left'; applyTextStyles(bodyText, {...bodyState}); });
  bodyAlignCenter.addEventListener('click', () => { bodyState.align = 'center'; applyTextStyles(bodyText, {...bodyState}); });
  bodyAlignRight.addEventListener('click', () => { bodyState.align = 'right'; applyTextStyles(bodyText, {...bodyState}); });

  // Resizing logic for layers
  function makeResizable(layer, handle){
    let startX=0, startY=0, startW=0, startH=0;
    const onDown=(e)=>{
      const ev = e.touches ? e.touches[0] : e;
      resizingLayer = layer;
      e.stopPropagation();
      e.preventDefault();
      const cs = window.getComputedStyle(layer);
      startW = parseFloat(cs.width || '0');
      startH = parseFloat(cs.height || layer.scrollHeight || '0');
      startX = ev.clientX;
      startY = ev.clientY;
      window.addEventListener('mousemove', onMove);
      window.addEventListener('mouseup', onUp);
      window.addEventListener('touchmove', onMove, {passive:false});
      window.addEventListener('touchend', onUp);
    };
    const onMove=(e)=>{
      if (resizingLayer !== layer) return;
      const ev = e.touches ? e.touches[0] : e;
      const dx = ev.clientX - startX;
      const dy = ev.clientY - startY;
      const canvasRect = canvas.getBoundingClientRect();
      const leftPx = parseFloat(layer.style.left || '0');
      const topPx = parseFloat(layer.style.top || '0');
      const maxW = Math.max(40, canvasRect.width - leftPx - 10);
      const maxH = Math.max(40, canvasRect.height - topPx - 10);
      const w = Math.min(maxW, Math.max(80, startW + dx));
      const h = Math.min(maxH, Math.max(40, startH + dy));
      layer.style.width = w + 'px';
      layer.style.height = h + 'px';
      e.preventDefault();
    };
    const onUp=()=>{
      resizingLayer = null;
      window.removeEventListener('mousemove', onMove);
      window.removeEventListener('mouseup', onUp);
      window.removeEventListener('touchmove', onMove);
      window.removeEventListener('touchend', onUp);
    };
    handle.addEventListener('mousedown', onDown);
    handle.addEventListener('touchstart', onDown, {passive:false});
  }
  makeResizable(titleLayer, titleResize);
  makeResizable(bodyLayer, bodyResize);

  // Ensure absolute px positions for export consistency
  function getLayerPxPosition(el){
    const style = window.getComputedStyle(el);
    return { left: parseFloat(style.left||'0'), top: parseFloat(style.top||'0') };
  }

  function getLayerWidth(el){
    const style = window.getComputedStyle(el);
    return parseFloat(style.width||'0');
  }
  function getLayerHeight(el){
    const style = window.getComputedStyle(el);
    const h = parseFloat(style.height||'0');
    if (h) return h;
    // fallback to content height if explicit height not set
    return el.scrollHeight;
  }

  function computeExportScale(){
    const width = canvas.getBoundingClientRect().width || 540; // fallback
    const target = currentVariant.width || 1080;
    return Math.max(1, target / width);
  }

  // Download
  downloadBtn.addEventListener('click', async () => {
  // hide handles during export
  const prevTitleDisp = titleResize.style.display;
  const prevBodyDisp = bodyResize.style.display;
  const prevOutline = editLayers.map(el => el.style.outline);
  const prevShadow = editLayers.map(el => el.style.boxShadow);
  titleResize.style.display = 'none';
  bodyResize.style.display = 'none';
  editLayers.forEach(el => { el.style.outline = 'none'; el.style.boxShadow = 'none'; });
  const node = canvas;
  const scale = computeExportScale();
  const png = await html2canvas(node, { backgroundColor: null, useCORS: true, scale }).then(c => c.toDataURL('image/png'));
  // restore handles
  titleResize.style.display = prevTitleDisp || '';
  bodyResize.style.display = prevBodyDisp || '';
  editLayers.forEach((el, i) => { el.style.outline = prevOutline[i] || ''; el.style.boxShadow = prevShadow[i] || ''; });
    const a = document.createElement('a');
    a.href = png; a.download = (postTitle.value || 'instagram-post') + '.png';
    a.click();
  });

  // Share via Web Share API (if supported); fallback to download
  shareBtn?.addEventListener('click', async () => {
    try {
      const prevTitleDisp = titleResize.style.display;
      const prevBodyDisp = bodyResize.style.display;
      const prevOutline = editLayers.map(el => el.style.outline);
      const prevShadow = editLayers.map(el => el.style.boxShadow);
      titleResize.style.display = 'none';
      bodyResize.style.display = 'none';
      editLayers.forEach(el => { el.style.outline = 'none'; el.style.boxShadow = 'none'; });
  const scale = computeExportScale();
  const dataUrl = await html2canvas(canvas, { backgroundColor: null, useCORS: true, scale }).then(c => c.toDataURL('image/png'));
      titleResize.style.display = prevTitleDisp || '';
      bodyResize.style.display = prevBodyDisp || '';
      editLayers.forEach((el, i) => { el.style.outline = prevOutline[i] || ''; el.style.boxShadow = prevShadow[i] || ''; });

      const res = await fetch(dataUrl);
      const blob = await res.blob();
      const fileName = (postTitle.value || 'instagram-post') + '.png';
      const file = new File([blob], fileName, { type: 'image/png' });
      if (navigator.canShare && navigator.canShare({ files: [file] })) {
        await navigator.share({ files: [file], title: postTitle.value || 'Instagram post', text: '' });
      } else {
        // Fallback download
        const a = document.createElement('a');
        a.href = dataUrl; a.download = fileName;
        a.click();
      }
    } catch (e) {
      // Fallback download on error
      downloadBtn.click();
    }
  });

  // Save or update to backend
  saveBtn.addEventListener('click', async () => {
    // Create preview first
  const prevTitleDisp = titleResize.style.display;
  const prevBodyDisp = bodyResize.style.display;
  const prevOutline = editLayers.map(el => el.style.outline);
  const prevShadow = editLayers.map(el => el.style.boxShadow);
  titleResize.style.display = 'none';
  bodyResize.style.display = 'none';
  editLayers.forEach(el => { el.style.outline = 'none'; el.style.boxShadow = 'none'; });
  const scale = computeExportScale();
  const preview = await html2canvas(canvas, { backgroundColor: null, useCORS: true, scale }).then(c => c.toDataURL('image/png'));
  titleResize.style.display = prevTitleDisp || '';
  bodyResize.style.display = prevBodyDisp || '';
  editLayers.forEach((el, i) => { el.style.outline = prevOutline[i] || ''; el.style.boxShadow = prevShadow[i] || ''; });
    const tPos = getLayerPxPosition(titleLayer);
    const bPos = getLayerPxPosition(bodyLayer);
  const tWidth = getLayerWidth(titleLayer);
  const bWidth = getLayerWidth(bodyLayer);
  const tHeight = getLayerHeight(titleLayer);
  const bHeight = getLayerHeight(bodyLayer);
  const payload = {
      title: postTitle.value || null,
      design: {
        size: currentVariant.width || 1080,
        canvas: { variant: currentVariant.key, width: currentVariant.width, height: currentVariant.height },
        background: (function(){
          const bg = { color: bgColorInput?.value || '#f3f4f6', opacity: (parseInt(bgOpacityInput?.value||'100',10)/100) };
          if (bgPath) bg.path = bgPath;
          if (bgTemplate) bg.template = bgTemplate; // legacy/no-op
          return bg;
        })(),
        title: {
          text: titleInput.value,
          left: tPos.left, top: tPos.top,
          width: tWidth,
          height: tHeight,
          color: titleColor.value,
          fontSize: parseInt(titleSize.value||48),
          fontFamily: titleFont.value,
      bold: !!titleState.bold, italic: !!titleState.italic, underline: !!titleState.underline,
      align: titleState.align || 'left'
        },
        text: {
          text: bodyInput.value,
          left: bPos.left, top: bPos.top,
          width: bWidth,
          height: bHeight,
          color: bodyColor.value,
          fontSize: parseInt(bodySize.value||28),
          fontFamily: bodyFont.value,
      bold: !!bodyState.bold, italic: !!bodyState.italic, underline: !!bodyState.underline,
      align: bodyState.align || 'left'
        }
      },
      preview_data_url: preview
    };

    const url = editId ? `{{ url('/instagram') }}/${editId}` : storeUrl;
    const method = editId ? 'PUT' : 'POST';
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data && (data.ok || method==='PUT')) {
      window.location.href = "{{ route('instagram.index') }}";
    } else {
      alert('Opslaan mislukt');
    }
  });

  // If editing, preload design into canvas and controls
  @if(isset($post))
    try {
      const design = @json($post->design ?? []);
      if (design?.canvas?.variant) {
        if (canvasVariantSelect) canvasVariantSelect.value = design.canvas.variant;
        applyCanvasVariant(design.canvas.variant);
      } else if (design?.size) {
        // back-compat: size==1080 implies square
        if (canvasVariantSelect) canvasVariantSelect.value = 'square';
        applyCanvasVariant('square');
      }
      if (design?.background?.path) {
        bgPath = design.background.path;
        bgImage.style.opacity = '1';
        bgImage.src = `/storage/${bgPath}`;
        bgImage.onload = () => { bgImage.style.opacity = '1'; };
        bgImage.onerror = () => { bgImage.style.opacity = '1'; };
      } else if (design?.background?.template) {
        bgTemplate = design.background.template;
        bgImage.src = bgTemplate;
        bgImage.style.opacity = '1';
      }
      // Apply background color and opacity if present
      if (design?.background?.color) {
        document.getElementById('canvas').style.backgroundColor = design.background.color;
        if (bgColorInput) bgColorInput.value = design.background.color;
      }
      if (typeof design?.background?.opacity === 'number') {
        const o = Math.max(0, Math.min(1, design.background.opacity));
        bgImage.style.opacity = o.toString();
        if (bgOpacityInput) bgOpacityInput.value = Math.round(o*100).toString();
      }
      if (design?.title) {
        titleInput.value = design.title.text || 'Titel';
        titleText.textContent = titleInput.value;
        titleColor.value = design.title.color || '#111111';
        titleSize.value = design.title.fontSize || 48;
        titleFont.value = design.title.fontFamily || 'Figtree, Arial, sans-serif';
        titleLayer.style.left = (design.title.left||20) + 'px';
        titleLayer.style.top = (design.title.top||20) + 'px';
  if (design.title.width) titleLayer.style.width = design.title.width + 'px';
  if (design.title.height) titleLayer.style.height = design.title.height + 'px';
        titleText.style.color = titleColor.value;
        titleText.style.fontSize = titleSize.value + 'px';
        titleText.style.fontFamily = titleFont.value;
        titleState.align = design.title.align || 'left';
        applyTextStyles(titleText, { align: titleState.align });
      }
      if (design?.text) {
        bodyInput.value = design.text.text || 'Je tekst hier…';
        bodyText.textContent = bodyInput.value;
        bodyColor.value = design.text.color || '#111111';
        bodySize.value = design.text.fontSize || 28;
        bodyFont.value = design.text.fontFamily || 'Figtree, Arial, sans-serif';
        bodyLayer.style.left = (design.text.left||20) + 'px';
        bodyLayer.style.top = (design.text.top||120) + 'px';
  if (design.text.width) bodyLayer.style.width = design.text.width + 'px';
  if (design.text.height) bodyLayer.style.height = design.text.height + 'px';
        bodyText.style.color = bodyColor.value;
        bodyText.style.fontSize = bodySize.value + 'px';
        bodyText.style.fontFamily = bodyFont.value;
        bodyState.align = design.text.align || 'left';
        applyTextStyles(bodyText, { align: bodyState.align });
      }
      postTitle.value = @json($post->title ?? '');
    } catch (e) { console.warn('Kon ontwerp niet laden', e); }
  @endif
</script>
<div class="max-w-7xl mx-auto px-4 mt-12">
  <h2 class="text-lg font-semibold mb-3">Recent opgeslagen posts</h2>
  <div class="grid grid-cols-4 md:grid-cols-8 lg:grid-cols-12 gap-2">
    @forelse($recent as $p)
      <div class="relative group bg-white rounded-md shadow-sm overflow-hidden">
        <div class="relative w-full" style="padding-top:100%;">
        @if($p->preview_path)
          <img src="{{ asset('storage/'.$p->preview_path) }}" alt="preview" class="absolute inset-0 w-full h-full object-cover" />
        @else
          <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-[10px]">Geen preview</div>
        @endif
        </div>
        <div class="px-1 py-1 flex items-center justify-end gap-1">
          <a href="{{ route('instagram.edit', $p) }}" aria-label="Bewerken" class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-white text-black ring ring-black/10 shadow">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 20h4l10.243-10.243a2.5 2.5 0 10-3.536-3.536L4 16v4z"/></svg>
          </a>
          <form action="{{ route('instagram.destroy', $p) }}" method="POST" onsubmit="return confirm('Verwijderen?');" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" aria-label="Verwijderen" class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-100 text-rose-700 ring ring-black/10 shadow">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 001 1h8a1 1 0 001-1m-10 0V6a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="text-gray-500 text-sm">Nog geen posts opgeslagen.</div>
    @endforelse
  </div>
</div>
@endsection
