@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Klanten Importeren</h1>
            <a href="/klanten" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Terug naar Klanten
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Validatiefouten:</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Import Form -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Excel Bestand Uploaden</h2>
                
                <form action="{{ route('klanten.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Selecteer Excel Bestand (.xlsx, .xls, .csv)
                        </label>
                        <input type="file" 
                               name="excel_file" 
                               id="excel_file" 
                               accept=".xlsx,.xls,.csv"
                               required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">
                        Klanten Importeren
                    </button>
                </form>
            </div>

            <!-- Instructions -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Instructies</h2>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h3 class="font-semibold text-blue-800 mb-2">Vereiste Kolommen:</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• <strong>naam</strong> (verplicht)</li>
                        <li>• email</li>
                        <li>• telefoon</li>
                        <li>• adres</li>
                        <li>• postcode</li>
                        <li>• plaats</li>
                        <li>• geboortedatum (YYYY-MM-DD)</li>
                        <li>• geslacht (man/vrouw/anders)</li>
                        <li>• lengte_cm</li>
                        <li>• gewicht_kg</li>
                        <li>• sport</li>
                        <li>• niveau</li>
                        <li>• doelen</li>
                        <li>• medische_info</li>
                        <li>• opmerkingen</li>
                    </ul>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <h3 class="font-semibold text-yellow-800 mb-2">Let op:</h3>
                    <ul class="text-sm text-yellow-700 space-y-1">
                        <li>• De eerste rij moet de kolomnamen bevatten</li>
                        <li>• Alleen 'naam' is verplicht, andere velden zijn optioneel</li>
                        <li>• Maximaal 10MB bestandsgrootte</li>
                        <li>• Ondersteunde formaten: .xlsx, .xls, .csv</li>
                    </ul>
                </div>

                <div class="mt-6 space-y-3">
                    <a href="/import/klanten/template" 
                       class="block w-full text-center text-white font-bold py-3 px-6 rounded-lg border border-green-700"
                       style="background-color: #059669 !important; color: white !important;">
                        📥 Download Klanten Template
                    </a>
                    
                    <a href="/import/bikefits" 
                       class="block w-full text-center text-white font-bold py-3 px-6 rounded-lg border border-blue-700"
                       style="background-color: #2563eb !important; color: white !important;">
                        🚴 Bikefits Importeren
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection