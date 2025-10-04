@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-6">
    <h2 class="text-xl font-bold mb-4">Upload test</h2>
    <form action="/uploads" method="post" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700">Selecteer bestand</label>
            <input type="file" name="file" required class="mt-1" />
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700">Koppel aan bikefit id (optioneel)</label>
            <input type="text" name="bikefit_id" class="mt-1 w-full border rounded px-2 py-1" placeholder="bv. 123" />
        </div>
        <div class="mb-3">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_cover" value="1" class="mr-2" />
                <span class="text-sm">Markeer als cover</span>
            </label>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Upload</button>
        </div>
    </form>
</div>
@endsection
