@php $s = $inspanningstest ?? null; @endphp
<div class="mb-8 p-4 bg-gray-50 rounded border border-gray-200">
    <h2 class="text-2xl font-bold mb-4">Algemene gegevens</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="testtype" class="block font-medium">Testtype</label>
            <select name="testtype" id="testtype" class="mt-1 block w-full border-gray-300 rounded" required>
                <option value="looptest" {{ old('testtype', optional($s)->testtype) == 'looptest' ? 'selected' : '' }}>Looptest</option>
                <option value="fietstest" {{ old('testtype', optional($s)->testtype) == 'fietstest' ? 'selected' : '' }}>Fietstest</option>
            </select>
        </div>
        <div>
            <label class="block font-medium">Sjabloon type</label>
            <select name="template_kind" class="mt-1 block w-full border-gray-300 rounded">
                @foreach(report_template_kinds() as $key => $label)
                    <option value="{{ $key }}" {{ old('template_kind', optional($s)->template_kind) == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="testdatum" class="block font-medium">Testdatum</label>
            <input type="date" name="testdatum" id="testdatum" value="{{ old('testdatum', optional(optional($s)->testdatum)->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded">
        </div>
    </div>
</div>

<!-- Additional fields specific to inspanningstest can be placed here -->

{{-- action buttons moved to parent views (create/edit) to avoid duplicates --}}
