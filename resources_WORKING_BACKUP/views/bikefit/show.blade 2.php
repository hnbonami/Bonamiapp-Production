{{-- Layout gelijk aan inspanningstest, geen debug of overbodige checks --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Bikefit van {{ optional($klant)->voornaam }} {{ optional($klant)->naam }}
        </h2>
    {{-- Datum niet meer in header, maar bij details --}}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- @if($klant && $bikefit) --}}
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Klantgegevens</h3>
                                <p><strong>Naam:</strong> {{ $klant->voornaam }} {{ $klant->naam }}</p>
                                <p><strong>Email:</strong> {{ $klant->email ?? 'N/A' }}</p>
                                <p><strong>Telefoon:</strong> {{ $klant->telefoon ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Bikefit Details</h3>
                                @if(isset($bikefit->datum))
                                    <p class="mb-2"><strong>Datum:</strong> {{ $bikefit->datum ? $bikefit->datum->format('d-m-Y') : '' }}</p>
                                @endif
                                <p><strong>Lengte (cm):</strong> {{ $bikefit->lengte_cm ?? 'N/A' }}</p>
                                <p><strong>Binnenbeenlengte (cm):</strong> {{ $bikefit->binnenbeenlengte_cm ?? 'N/A' }}</p>
                                <p><strong>Armlengte (cm):</strong> {{ $bikefit->armlengte_cm ?? 'N/A' }}</p>
                                <p><strong>Romplengte (cm):</strong> {{ $bikefit->romplengte_cm ?? 'N/A' }}</p>
                                <p><strong>Schouderbreedte (cm):</strong> {{ $bikefit->schouderbreedte_cm ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-4">Metingen</h3>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <p><strong>Zadel-trapas hoek:</strong> {{ $bikefit->zadel_trapas_hoek ?? 'N/A' }}</p>
                                    <p><strong>Zadel-trapas afstand:</strong> {{ $bikefit->zadel_trapas_afstand ?? 'N/A' }}</p>
                                    <p><strong>Stuur-trapas hoek:</strong> {{ $bikefit->stuur_trapas_hoek ?? 'N/A' }}</p>
                                    <p><strong>Stuur-trapas afstand:</strong> {{ $bikefit->stuur_trapas_afstand ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p><strong>Type zadel:</strong> {{ $bikefit->type_zadel ?? 'N/A' }}</p>
                                    <p><strong>Zadeltil:</strong> {{ $bikefit->zadeltil ?? 'N/A' }}</p>
                                    <p><strong>Zadelbreedte:</strong> {{ $bikefit->zadelbreedte ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-4">Info fiets</h3>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <p><strong>Fietsmerk:</strong> {{ $bikefit->fietsmerk ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p><strong>Kadermaat:</strong> {{ $bikefit->kadermaat ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p><strong>Bouwjaar:</strong> {{ $bikefit->bouwjaar ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-4">Anamnese</h3>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <p><strong>Algemene klachten:</strong> {{ $bikefit->algemene_klachten ?? 'Geen' }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <p><strong>Beenlengteverschil:</strong> {{ $bikefit->beenlengteverschil ? 'Ja' : 'Neen' }}</p>
                                    </div>
                                    <div>
                                        <p><strong>Beenlengte verschil (cm):</strong> {{ $bikefit->beenlengteverschil_cm ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <p><strong>Lenigheid hamstrings:</strong> {{ $bikefit->lenigheid_hamstrings ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p><strong>Steunzolen:</strong> {{ $bikefit->steunzolen ? 'Ja' : 'Neen' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <p><strong>Reden steunzolen:</strong> {{ $bikefit->steunzolen_reden ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-lg font-semibold mb-4">Voetmeting</h3>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <p><strong>Schoenmaat:</strong> {{ $bikefit->schoenmaat ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p><strong>Voetbreedte:</strong> {{ $bikefit->voetbreedte ? $bikefit->voetbreedte . ' cm' : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <p><strong>Voetpositie:</strong> {{ $bikefit->voetpositie ? ucfirst($bikefit->voetpositie) : 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-lg font-semibold mb-4">Stuurpen aanpassingen</h3>
                                <p><strong>Aanwezig:</strong> {{ $bikefit->aanpassingen_stuurpen_aan ? 'Ja' : 'Neen' }}</p>
                                @if($bikefit->aanpassingen_stuurpen_aan)
                                    <p><strong>Pre:</strong> {{ $bikefit->aanpassingen_stuurpen_pre ?? 'N/A' }} mm</p>
                                    <p><strong>Post:</strong> {{ $bikefit->aanpassingen_stuurpen_post ?? 'N/A' }} mm</p>
                                @endif
                            </div>
                            </div>
                        </div>

                         <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-4">Aanpassingen</h3>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <p><strong>Aanpassingen zadel:</strong> {{ $bikefit->aanpassingen_zadel ?? 'N/A' }}</p>
                                    <p><strong>Aanpassingen setback:</strong> {{ $bikefit->aanpassingen_setback ?? 'N/A' }}</p>
                                    <p><strong>Aanpassingen reach:</strong> {{ $bikefit->aanpassingen_reach ?? 'N/A' }}</p>
                                    <p><strong>Aanpassingen drop:</strong> {{ $bikefit->aanpassingen_drop ?? 'N/A' }}</p>
                                    <!-- old free-text aanpassingen_stuurpen removed; structured values shown above -->
                                </div>
                                <div>
                                    <p><strong>Rotatie aanpassingen:</strong> {{ $bikefit->rotatie_aanpassingen ?? 'N/A' }}</p>
                                    <p><strong>Inclinatie aanpassingen:</strong> {{ $bikefit->inclinatie_aanpassingen ?? 'N/A' }}</p>
                                    <p><strong>Ophoging links:</strong> {{ $bikefit->ophoging_li ?? 'N/A' }}</p>
                                    <p><strong>Ophoging rechts:</strong> {{ $bikefit->ophoging_re ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-4">Opmerkingen</h3>
                            <p><strong>Opmerkingen:</strong> {{ $bikefit->opmerkingen ?? 'Geen opmerkingen.' }}</p>
                            <p><strong>Interne opmerkingen:</strong> {{ $bikefit->interne_opmerkingen ?? 'Geen interne opmerkingen.' }}</p>
                        </div>

                        <div class="flex gap-3 justify-end mt-6">
                            <a href="{{ route('klanten.show', $klant->id) }}" class="rounded-full px-4 py-1 bg-blue-100 text-blue-800 font-bold text-sm flex items-center justify-center">Terug naar profiel</a>
                            <a href="{{ route('bikefit.edit', ['klant' => $klant->id, 'bikefit' => $bikefit->id]) }}" class="rounded-full px-4 py-1 bg-orange-100 text-orange-800 font-bold text-sm flex items-center justify-center">Bewerk</a>
                        </div>
                    @else
                        <p>De gevraagde bikefit-gegevens konden niet worden geladen.</p>
                    {{-- @endif --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
