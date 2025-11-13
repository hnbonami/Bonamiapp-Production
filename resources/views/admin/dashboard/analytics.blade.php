@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header met filters --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex flex-col gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📊 Analytics Dashboard</h1>
                <p class="text-sm text-gray-600 mt-1">Prestatie statistieken en trends</p>
            </div>
            
            {{-- Filters op één rij zoals overzicht --}}
            <div class="flex flex-wrap gap-3">
                {{-- Periode snelkeuze --}}
                <div class="flex items-center gap-2 flex-1 min-w-[160px]">
                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Periode:</label>
                    <select id="periode-filter" class="flex-1 border-gray-300 rounded-lg text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="custom">Aangepast</option>
                        <option value="vandaag">Vandaag</option>
                        <option value="gisteren">Gisteren</option>
                        <option value="deze-week">Deze week</option>
                        <option value="vorige-week">Vorige week</option>
                        <option value="deze-maand">Deze maand</option>
                        <option value="vorige-maand">Vorige maand</option>
                        <option value="dit-kwartaal">Dit kwartaal (Q{{ ceil(now()->month / 3) }})</option>
                        <option value="vorig-kwartaal">Vorig kwartaal</option>
                        <option value="dit-jaar">Dit jaar ({{ now()->year }})</option>
                        <option value="vorig-jaar">Vorig jaar ({{ now()->year - 1 }})</option>
                        <option value="laatste-7-dagen">Laatste 7 dagen</option>
                        <option value="laatste-30-dagen" selected>Laatste 30 dagen</option>
                        <option value="laatste-90-dagen">Laatste 90 dagen</option>
                    </select>
                </div>
                
                <div class="flex items-center gap-2 flex-1 min-w-[180px]">
                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Filter:</label>
                    <select id="scope-filter" class="flex-1 border-gray-300 rounded-lg text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @if(auth()->user()->is_super_admin || (auth()->user()->email && in_array(auth()->user()->email, ['info@bonami-sportcoaching.be', 'admin@bonami-sportcoaching.be'])))
                            <option value="auto">Automatisch</option>
                            <option value="organisatie">Mijn Organisatie</option>
                            <option value="medewerker">Alleen Ik</option>
                            <option value="all">Alle Organisaties</option>
                        @elseif(in_array(auth()->user()->role, ['admin', 'organisatie_admin']))
                            <option value="organisatie" selected>Mijn Organisatie</option>
                            <option value="medewerker">Alleen Ik</option>
                        @else
                            {{-- Medewerkers zien alleen "Alleen Ik" --}}
                            <option value="medewerker" selected>Alleen Ik</option>
                        @endif
                    </select>
                </div>
                
                <div class="flex items-center gap-2 flex-1 min-w-[160px]">
                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Van:</label>
                    <input type="date" id="start-datum" class="flex-1 border-gray-300 rounded-lg text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ now()->subDays(30)->format('Y-m-d') }}">
                </div>
                
                <div class="flex items-center gap-2 flex-1 min-w-[160px]">
                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Tot:</label>
                    <input type="date" id="eind-datum" class="flex-1 border-gray-300 rounded-lg text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ now()->format('Y-m-d') }}">
                </div>
                
                <button onclick="laadAnalyticsData()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Vernieuwen
                </button>
                
                {{-- Grafiek visibility toggle --}}
                <div class="relative">
                    <button id="grafiek-toggle-btn" type="button" 
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Grafieken</span>
                    </button>
                    
                    <div id="grafiek-toggle-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <div class="p-3">
                            <div class="text-sm font-semibold text-gray-700 mb-2">Toon grafieken:</div>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" class="grafiek-toggle rounded border-gray-300 text-blue-600" data-grafiek="diensten" checked>
                                    <span class="ml-2 text-sm text-gray-700">🎯 Diensten Verdeling</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="grafiek-toggle rounded border-gray-300 text-blue-600" data-grafiek="status" checked>
                                    <span class="ml-2 text-sm text-gray-700">✅ Prestatie Status</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="grafiek-toggle rounded border-gray-300 text-blue-600" data-grafiek="omzet" checked>
                                    <span class="ml-2 text-sm text-gray-700">📈 Omzet Trend</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="grafiek-toggle rounded border-gray-300 text-blue-600" data-grafiek="medewerker" checked>
                                    <span class="ml-2 text-sm text-gray-700">🏆 Top Medewerkers</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="grafiek-toggle rounded border-gray-300 text-blue-600" data-grafiek="commissie" checked>
                                    <span class="ml-2 text-sm text-gray-700">💰 Commissie Trend</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="grafiek-toggle rounded border-gray-300 text-blue-600" data-grafiek="bikefits-totaal" checked>
                                    <span class="ml-2 text-sm text-gray-700">🚴 Totaal Bikefits</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="grafiek-toggle rounded border-gray-300 text-blue-600" data-grafiek="bikefits-medewerker" checked>
                                    <span class="ml-2 text-sm text-gray-700">🚴 Bikefits per Medewerker</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="grafiek-toggle rounded border-gray-300 text-blue-600" data-grafiek="testen-totaal" checked>
                                    <span class="ml-2 text-sm text-gray-700">🏃 Totaal Inspanningstesten</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="grafiek-toggle rounded border-gray-300 text-blue-600" data-grafiek="testen-medewerker" checked>
                                    <span class="ml-2 text-sm text-gray-700">🏃 Testen per Medewerker</span>
                                </label>
                                @if(auth()->user()->is_super_admin || in_array(auth()->user()->email, ['info@bonami-sportcoaching.be', 'admin@bonami-sportcoaching.be']))
                                <label class="flex items-center">
                                    <input type="checkbox" class="grafiek-toggle rounded border-gray-300 text-blue-600" data-grafiek="organisaties" checked>
                                    <span class="ml-2 text-sm text-gray-700">🏢 Omzet per Organisatie</span>
                                </label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards - Draggable --}}
    <div id="kpi-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="kpi-card bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-lg shadow-sm p-4 hover:shadow-md transition cursor-move" draggable="true" data-kpi-id="bruto">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-gray-600 uppercase tracking-wide">Bruto Omzet</span>
                <div class="flex items-center gap-2">
                    <span class="text-xl">💰</span>
                    <button class="text-gray-400 hover:text-gray-600 transition-colors" title="Sleep om te verplaatsen">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                            <circle cx="4" cy="4" r="1.5"/><circle cx="12" cy="4" r="1.5"/>
                            <circle cx="4" cy="8" r="1.5"/><circle cx="12" cy="8" r="1.5"/>
                            <circle cx="4" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900" id="kpi-bruto">-</div>
        </div>
        
        <div class="kpi-card bg-gradient-to-br from-blue-50 to-white border border-blue-200 rounded-lg shadow-sm p-4 hover:shadow-md transition cursor-move" draggable="true" data-kpi-id="netto">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-blue-600 uppercase tracking-wide">Netto Omzet</span>
                <div class="flex items-center gap-2">
                    <span class="text-xl">💵</span>
                    <button class="text-gray-400 hover:text-gray-600 transition-colors" title="Sleep om te verplaatsen">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                            <circle cx="4" cy="4" r="1.5"/><circle cx="12" cy="4" r="1.5"/>
                            <circle cx="4" cy="8" r="1.5"/><circle cx="12" cy="8" r="1.5"/>
                            <circle cx="4" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="text-2xl font-bold text-blue-600" id="kpi-netto">-</div>
        </div>
        
        <div class="kpi-card bg-gradient-to-br from-orange-50 to-white border border-orange-200 rounded-lg shadow-sm p-4 hover:shadow-md transition cursor-move" draggable="true" data-kpi-id="commissie">
            <div class="flex items-center justify-between mb-2"> 
                <span class="text-xs font-medium text-orange-600 uppercase tracking-wide">Commissie organisatie</span>
                <div class="flex items-center gap-2">
                    <span class="text-xl">🏢</span>
                    <button class="text-gray-400 hover:text-gray-600 transition-colors" title="Sleep om te verplaatsen">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                            <circle cx="4" cy="4" r="1.5"/><circle cx="12" cy="4" r="1.5"/>
                            <circle cx="4" cy="8" r="1.5"/><circle cx="12" cy="8" r="1.5"/>
                            <circle cx="4" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="text-2xl font-bold text-orange-600" id="kpi-commissie">-</div>
        </div>
        
        <div class="kpi-card bg-gradient-to-br from-green-50 to-white border border-green-200 rounded-lg shadow-sm p-4 hover:shadow-md transition cursor-move" draggable="true" data-kpi-id="medewerker">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-green-600 uppercase tracking-wide">Medewerker</span>
                <div class="flex items-center gap-2">
                    <span class="text-xl">👥</span>
                    <button class="text-gray-400 hover:text-gray-600 transition-colors" title="Sleep om te verplaatsen">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                            <circle cx="4" cy="4" r="1.5"/><circle cx="12" cy="4" r="1.5"/>
                            <circle cx="4" cy="8" r="1.5"/><circle cx="12" cy="8" r="1.5"/>
                            <circle cx="4" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="text-2xl font-bold text-green-600" id="kpi-medewerker">-</div>
        </div>
    </div>

    {{-- Draggable Grafieken Grid - VASTE 4 kolommen op desktop --}}
    <div id="charts-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        {{-- Diensten Verdeling - klein (standaard 1 kolom) --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="diensten" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">🎯 Diensten</h3>
                <button onclick="toggleChartSize('diensten')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="dienstenChart"></canvas>
            </div>
        </div>

        {{-- Prestatie Status - klein (standaard 1 kolom) --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="status" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">✅ Status</h3>
                <button onclick="toggleChartSize('status')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        {{-- Omzet Trend - klein (standaard 1 kolom) --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="omzet" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">📈 Omzet</h3>
                <button onclick="toggleChartSize('omzet')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="omzetChart"></canvas>
            </div>
        </div>

        {{-- Top Medewerkers - klein (standaard 1 kolom) --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="medewerker" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">🏆 Medewerkers</h3>
                <button onclick="toggleChartSize('medewerker')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="medewerkerChart"></canvas>
            </div>
        </div>
        
        {{-- Commissie Trend - klein (standaard 1 kolom) --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="commissie" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">💰 Commissie</h3>
                <button onclick="toggleChartSize('commissie')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="commissieChart"></canvas>
            </div>
        </div>
        
        {{-- Totaal Bikefits Trend - klein --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="bikefits-totaal" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">🚴 Bikefits</h3>
                <button onclick="toggleChartSize('bikefits-totaal')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="bikefitsTotaalChart"></canvas>
            </div>
        </div>
        
        {{-- Bikefits per Medewerker - klein --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="bikefits-medewerker" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">🚴 Bikefits/Medewerker</h3>
                <button onclick="toggleChartSize('bikefits-medewerker')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="bikefitsMedewerkerChart"></canvas>
            </div>
        </div>
        
        {{-- Totaal Inspanningstesten Trend - klein --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="testen-totaal" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">🏃 Testen</h3>
                <button onclick="toggleChartSize('testen-totaal')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="testenTotaalChart"></canvas>
            </div>
        </div>
        
        {{-- Inspanningstesten per Medewerker - klein --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="testen-medewerker" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">🏃 Testen/Medewerker</h3>
                <button onclick="toggleChartSize('testen-medewerker')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="testenMedewerkerChart"></canvas>
            </div>
        </div>
        
        @if(auth()->user()->is_super_admin || in_array(auth()->user()->email, ['info@bonami-sportcoaching.be', 'admin@bonami-sportcoaching.be']))
        {{-- Omzet per Organisatie - alleen voor super admin --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="organisaties" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">🏢 Omzet/Organisatie</h3>
                <button onclick="toggleChartSize('organisaties')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="organisatiesChart"></canvas>
            </div>
        </div>
        @endif
    </div>

    {{-- Extra statistieken --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">🧾 BTW Overzicht</h3>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Incl. BTW:</span>
                    <span class="font-semibold" id="btw-incl">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Excl. BTW:</span>
                    <span class="font-semibold" id="btw-excl">-</span>
                </div>
                <div class="flex justify-between text-sm border-t pt-2">
                    <span class="text-gray-900 font-medium">BTW (21%):</span>
                    <span class="font-bold text-blue-600" id="btw-totaal">-</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">💼 Commissie Verdeling</h3>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Organisatie:</span>
                    <span class="font-semibold text-orange-600" id="commissie-org">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Medewerkers:</span>
                    <span class="font-semibold text-green-600" id="commissie-med">-</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
let charts = {};
let chartLayouts = JSON.parse(localStorage.getItem('analyticsChartLayout') || '[]');

// Gebruikersrol en permissions voor validatie
const isSuperAdmin = {{ (auth()->user()->is_super_admin || in_array(auth()->user()->email, ['info@bonami-sportcoaching.be', 'admin@bonami-sportcoaching.be'])) ? 'true' : 'false' }};
const heeftOrganisatie = {{ auth()->user()->organisatie_id ? 'true' : 'false' }};
const isMedewerker = {{ auth()->user()->is_medewerker ? 'true' : 'false' }};

// Valideer scope op basis van gebruikersrol
function valideerScope(scope) {
    // Super admin mag alles
    if (isSuperAdmin) {
        return scope;
    }
    
    // Admin (heeft organisatie EN is geen medewerker) mag alleen organisatie en medewerker
    if (heeftOrganisatie && !isMedewerker) {
        if (scope === 'all' || scope === 'auto') {
            console.warn('⚠️ Admin mag geen "Alle Organisaties" selecteren, terug naar "organisatie"');
            return 'organisatie';
        }
        return scope;
    }
    
    // Medewerker mag alleen medewerker scope
    console.warn('⚠️ Medewerker mag alleen eigen data bekijken');
    return 'medewerker';
}

// Draggable functionaliteit voor charts
const chartsContainer = document.getElementById('charts-container');
new Sortable(chartsContainer, {
    animation: 150,
    handle: '.chart-card',
    ghostClass: 'opacity-50',
    onEnd: function() {
        const order = Array.from(chartsContainer.children).map(el => el.dataset.chartId);
        localStorage.setItem('analyticsChartLayout', JSON.stringify(order));
        console.log('💾 Charts layout opgeslagen:', order);
    }
});

// Draggable functionaliteit voor KPI cards
const kpiContainer = document.getElementById('kpi-container');
new Sortable(kpiContainer, {
    animation: 150,
    handle: '.kpi-card',
    ghostClass: 'opacity-50',
    onEnd: function() {
        const order = Array.from(kpiContainer.children).map(el => el.dataset.kpiId);
        localStorage.setItem('analyticsKpiLayout', JSON.stringify(order));
        console.log('💾 KPI layout opgeslagen:', order);
    }
});

// Laad opgeslagen KPI layout
function loadKpiLayout() {
    const saved = localStorage.getItem('analyticsKpiLayout');
    if (saved) {
        try {
            const layout = JSON.parse(saved);
            
            // Sorteer KPI cards op basis van opgeslagen volgorde
            layout.forEach(kpiId => {
                const kpi = document.querySelector(`[data-kpi-id="${kpiId}"]`);
                if (kpi) {
                    kpiContainer.appendChild(kpi);
                }
            });
            
            console.log('✅ KPI layout geladen:', layout);
        } catch (e) {
            console.error('❌ Fout bij laden KPI layout:', e);
        }
    }
}

// Haal data op
function laadAnalyticsData() {
    const start = document.getElementById('start-datum').value;
    const eind = document.getElementById('eind-datum').value;
    let scope = document.getElementById('scope-filter').value;
    
    // Valideer en corrigeer scope indien nodig
    scope = valideerScope(scope);
    
    // Update dropdown indien gecorrigeerd
    if (document.getElementById('scope-filter').value !== scope) {
        document.getElementById('scope-filter').value = scope;
    }
    
    console.log('🔄 Analytics data laden...', { start, eind, scope, isSuperAdmin, heeftOrganisatie, isMedewerker });
    
    // Toon loading state
    document.getElementById('kpi-bruto').textContent = '...';
    document.getElementById('kpi-netto').textContent = '...';
    document.getElementById('kpi-commissie').textContent = '...';
    document.getElementById('kpi-medewerker').textContent = '...';
    
    fetch(`/api/dashboard/analytics?start=${start}&eind=${eind}&scope=${scope}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            console.log('📡 Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Data ontvangen:', data);
            if (data.success) {
                updateKPIs(data.kpis);
                updateCharts(data);
                updateExtra(data);
            } else {
                console.error('❌ Data ophalen mislukt:', data.message);
                alert('Fout bij laden data: ' + (data.message || 'Onbekende fout'));
            }
        })
        .catch(err => {
            console.error('❌ Fout bij laden data:', err);
            alert('Fout bij laden analytics data: ' + err.message);
        });
}

function updateKPIs(kpis) {
    console.log('📊 KPIs updaten:', kpis);
    document.getElementById('kpi-bruto').textContent = '€' + Number(kpis.brutoOmzet || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('kpi-netto').textContent = '€' + Number(kpis.nettoOmzet || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('kpi-commissie').textContent = '€' + Number(kpis.commissie || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('kpi-medewerker').textContent = '€' + Number(kpis.medewerkerInkomsten || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
}

function updateCharts(data) {
    console.log('📈 Charts updaten:', data);
    
    // Omzet Chart
    if (charts.omzet) charts.omzet.destroy();
    charts.omzet = new Chart(document.getElementById('omzetChart'), {
        type: 'line',
        data: {
            labels: data.omzetTrend.labels || [],
            datasets: [{
                label: 'Bruto', data: data.omzetTrend.bruto || [],
                borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4, fill: true
            }, {
                label: 'Netto', data: data.omzetTrend.netto || [],
                borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4, fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }}}
    });
    
    // Diensten Chart
    if (charts.diensten) charts.diensten.destroy();
    charts.diensten = new Chart(document.getElementById('dienstenChart'), {
        type: 'doughnut',
        data: {
            labels: data.dienstenVerdeling.labels || [],
            datasets: [{data: data.dienstenVerdeling.values || [], backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']}]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }}}
    });
    
    // Medewerker Chart
    if (charts.medewerker) charts.medewerker.destroy();
    charts.medewerker = new Chart(document.getElementById('medewerkerChart'), {
        type: 'bar',
        data: {
            labels: data.medewerkerPrestaties.labels || [],
            datasets: [{label: 'Prestaties', data: data.medewerkerPrestaties.values || [], backgroundColor: '#3b82f6'}]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }}}
    });
    
    // Status Chart
    if (charts.status) charts.status.destroy();
    charts.status = new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Uitgevoerd', 'Niet uitgevoerd'],
            datasets: [{data: [data.prestatieStatus.uitgevoerd || 0, data.prestatieStatus.nietUitgevoerd || 0], backgroundColor: ['#10b981', '#ef4444']}]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }}}
    });
    
    // Commissie Trend Chart
    if (charts.commissie) charts.commissie.destroy();
    charts.commissie = new Chart(document.getElementById('commissieChart'), {
        type: 'bar',
        data: {
            labels: data.commissieTrend.labels || [],
            datasets: [{
                label: 'Organisatie', 
                data: data.commissieTrend.organisatie || [],
                backgroundColor: '#f59e0b',
                borderColor: '#d97706',
                borderWidth: 1
            }, {
                label: 'Medewerkers', 
                data: data.commissieTrend.medewerkers || [],
                backgroundColor: '#10b981',
                borderColor: '#059669',
                borderWidth: 1
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { 
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += '€' + context.parsed.y.toLocaleString('nl-NL', {minimumFractionDigits: 2});
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: { stacked: false },
                y: { 
                    stacked: false,
                    ticks: {
                        callback: function(value) {
                            return '€' + value.toLocaleString('nl-NL');
                        }
                    }
                }
            }
        }
    });
    
    // Bikefits Totaal Trend Chart
    if (charts['bikefits-totaal']) charts['bikefits-totaal'].destroy();
    charts['bikefits-totaal'] = new Chart(document.getElementById('bikefitsTotaalChart'), {
        type: 'line',
        data: {
            labels: data.bikefitStats.trend.labels || [],
            datasets: [{
                label: 'Bikefits',
                data: data.bikefitStats.trend.values || [],
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { 
                legend: { display: false },
                title: { display: true, text: `Totaal: ${data.bikefitStats.totaal}` }
            }
        }
    });
    
    // Bikefits per Medewerker Chart
    if (charts['bikefits-medewerker']) charts['bikefits-medewerker'].destroy();
    charts['bikefits-medewerker'] = new Chart(document.getElementById('bikefitsMedewerkerChart'), {
        type: 'bar',
        data: {
            labels: data.bikefitStats.perMedewerker.labels || [],
            datasets: [{
                label: 'Bikefits',
                data: data.bikefitStats.perMedewerker.values || [],
                backgroundColor: '#8b5cf6'
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
    
    // Inspanningstesten Totaal Trend Chart
    if (charts['testen-totaal']) charts['testen-totaal'].destroy();
    charts['testen-totaal'] = new Chart(document.getElementById('testenTotaalChart'), {
        type: 'line',
        data: {
            labels: data.inspanningstestStats.trend.labels || [],
            datasets: [{
                label: 'Testen',
                data: data.inspanningstestStats.trend.values || [],
                borderColor: '#ec4899',
                backgroundColor: 'rgba(236, 72, 153, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { 
                legend: { display: false },
                title: { display: true, text: `Totaal: ${data.inspanningstestStats.totaal}` }
            }
        }
    });
    
    // Inspanningstesten per Medewerker Chart
    if (charts['testen-medewerker']) charts['testen-medewerker'].destroy();
    charts['testen-medewerker'] = new Chart(document.getElementById('testenMedewerkerChart'), {
        type: 'bar',
        data: {
            labels: data.inspanningstestStats.perMedewerker.labels || [],
            datasets: [{
                label: 'Testen',
                data: data.inspanningstestStats.perMedewerker.values || [],
                backgroundColor: '#ec4899'
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
    
    // Omzet per Organisatie Chart (alleen voor super admin)
    if (isSuperAdmin && data.omzetPerOrganisatie) {
        const organisatiesCanvas = document.getElementById('organisatiesChart');
        if (organisatiesCanvas) {
            if (charts['organisaties']) charts['organisaties'].destroy();
            
            console.log('🏢 Organisaties chart data:', data.omzetPerOrganisatie);
            
            charts['organisaties'] = new Chart(organisatiesCanvas, {
                type: 'bar',
                data: {
                    labels: data.omzetPerOrganisatie.labels || [],
                    datasets: [{
                        label: 'Bruto',
                        data: data.omzetPerOrganisatie.bruto || [],
                        backgroundColor: '#3b82f6',
                        borderColor: '#2563eb',
                        borderWidth: 1
                    }, {
                        label: 'Netto',
                        data: data.omzetPerOrganisatie.netto || [],
                        backgroundColor: '#10b981',
                        borderColor: '#059669',
                        borderWidth: 1
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { 
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += '€' + context.parsed.y.toLocaleString('nl-NL', {minimumFractionDigits: 2});
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '€' + value.toLocaleString('nl-NL');
                                }
                            }
                        } 
                    }
                }
            });
            
            console.log('✅ Organisaties chart succesvol aangemaakt');
        } else {
            console.warn('⚠️ Canvas element "organisatiesChart" niet gevonden');
        }
    }
}

function updateExtra(data) {
    console.log('📝 Extra data updaten:', data);
    document.getElementById('btw-incl').textContent = '€' + Number(data.btwOverzicht.incl || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('btw-excl').textContent = '€' + Number(data.btwOverzicht.excl || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('btw-totaal').textContent = '€' + Number(data.btwOverzicht.totaal || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('commissie-org').textContent = '€' + Number(data.commissieVerdeling.organisatie || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('commissie-med').textContent = '€' + Number(data.commissieVerdeling.medewerkers || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
}

function toggleChartSize(chartId) {
    const card = document.querySelector(`[data-chart-id="${chartId}"]`);
    const wrapper = card.querySelector('.chart-wrapper');
    const currentSize = card.getAttribute('data-size') || 'small';
    
    console.log('🔄 Toggle size voor', chartId, 'huidige grootte:', currentSize);
    
    // Verwijder ALLE col-span classes (inclusief de standaard col-span-1!)
    card.className = card.className.split(' ').filter(c => !c.includes('col-span')).join(' ');
    
    if (currentSize === 'small') {
        // Klein → Medium (2 kolommen)
        card.classList.add('lg:col-span-2', 'md:col-span-2', 'sm:col-span-2', 'col-span-1');
        card.setAttribute('data-size', 'medium');
        wrapper.style.height = '250px';
        console.log('→ Van Klein naar Medium (2 kolommen)');
    } else if (currentSize === 'medium') {
        // Medium → Groot (volle breedte = 4 kolommen)
        card.classList.add('lg:col-span-4', 'md:col-span-3', 'sm:col-span-2', 'col-span-1');
        card.setAttribute('data-size', 'large');
        wrapper.style.height = '350px';
        console.log('→ Van Medium naar Groot (volle breedte)');
    } else {
        // Groot → Klein (terug naar 1 kolom)
        card.classList.add('col-span-1');
        card.setAttribute('data-size', 'small');
        wrapper.style.height = '180px';
        console.log('→ Van Groot naar Klein (1 kolom)');
    }
    
    // Resize chart met delay voor smooth transition
    if (charts[chartId]) {
        setTimeout(() => {
            charts[chartId].resize();
            console.log('✅ Chart resized:', chartId);
        }, 200);
    }
}

// Laad data bij pagina load
document.addEventListener('DOMContentLoaded', function() {
    console.log('📊 Analytics dashboard geladen');
    loadKpiLayout(); // Laad KPI layout eerst
    laadAnalyticsData();
    
    // Periode snelkeuze functionaliteit
    const periodeFilter = document.getElementById('periode-filter');
    const startDatum = document.getElementById('start-datum');
    const eindDatum = document.getElementById('eind-datum');
    
    periodeFilter.addEventListener('change', function() {
        const periode = this.value;
        const vandaag = new Date();
        let start, eind;
        
        switch(periode) {
            case 'vandaag':
                start = eind = vandaag;
                break;
            case 'gisteren':
                start = eind = new Date(vandaag.setDate(vandaag.getDate() - 1));
                break;
            case 'deze-week':
                start = new Date(vandaag.setDate(vandaag.getDate() - vandaag.getDay() + 1)); // Maandag
                eind = new Date();
                break;
            case 'vorige-week':
                eind = new Date(vandaag.setDate(vandaag.getDate() - vandaag.getDay())); // Zondag vorige week
                start = new Date(eind.getTime() - 6 * 24 * 60 * 60 * 1000); // Maandag vorige week
                break;
            case 'deze-maand':
                start = new Date(vandaag.getFullYear(), vandaag.getMonth(), 1);
                eind = new Date();
                break;
            case 'vorige-maand':
                start = new Date(vandaag.getFullYear(), vandaag.getMonth() - 1, 1);
                eind = new Date(vandaag.getFullYear(), vandaag.getMonth(), 0);
                break;
            case 'dit-kwartaal':
                const kwartaal = Math.floor(vandaag.getMonth() / 3);
                start = new Date(vandaag.getFullYear(), kwartaal * 3, 1);
                eind = new Date();
                break;
            case 'vorig-kwartaal':
                const vorigKwartaal = Math.floor(vandaag.getMonth() / 3) - 1;
                if (vorigKwartaal < 0) {
                    start = new Date(vandaag.getFullYear() - 1, 9, 1); // Q4 vorig jaar
                    eind = new Date(vandaag.getFullYear() - 1, 11, 31);
                } else {
                    start = new Date(vandaag.getFullYear(), vorigKwartaal * 3, 1);
                    eind = new Date(vandaag.getFullYear(), vorigKwartaal * 3 + 3, 0);
                }
                break;
            case 'dit-jaar':
                start = new Date(vandaag.getFullYear(), 0, 1);
                eind = new Date();
                break;
            case 'vorig-jaar':
                start = new Date(vandaag.getFullYear() - 1, 0, 1);
                eind = new Date(vandaag.getFullYear() - 1, 11, 31);
                break;
            case 'laatste-7-dagen':
                eind = new Date();
                start = new Date(vandaag.setDate(vandaag.getDate() - 7));
                break;
            case 'laatste-30-dagen':
                eind = new Date();
                start = new Date(vandaag.setDate(vandaag.getDate() - 30));
                break;
            case 'laatste-90-dagen':
                eind = new Date();
                start = new Date(vandaag.setDate(vandaag.getDate() - 90));
                break;
            case 'custom':
                // Doe niets, gebruiker past handmatig aan
                return;
        }
        
        // Update datum velden
        if (start && eind) {
            startDatum.value = start.toISOString().split('T')[0];
            eindDatum.value = eind.toISOString().split('T')[0];
            laadAnalyticsData();
        }
    });
    
    // Als datums handmatig worden aangepast, zet periode naar "Aangepast"
    startDatum.addEventListener('change', function() {
        periodeFilter.value = 'custom';
        console.log('📅 Start datum changed:', this.value);
    });
    
    eindDatum.addEventListener('change', function() {
        periodeFilter.value = 'custom';
        console.log('📅 Eind datum changed:', this.value);
    });
    
    // Event listeners voor filters
    document.getElementById('scope-filter').addEventListener('change', function() {
        console.log('� Scope filter changed:', this.value);
        laadAnalyticsData();
    });
    
    // Grafiek visibility toggle functionaliteit
    const grafiekToggleBtn = document.getElementById('grafiek-toggle-btn');
    const grafiekToggleDropdown = document.getElementById('grafiek-toggle-dropdown');
    const grafiekToggles = document.querySelectorAll('.grafiek-toggle');
    
    // Toggle dropdown
    grafiekToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        grafiekToggleDropdown.classList.toggle('hidden');
    });
    
    // Sluit dropdown bij klik buiten
    document.addEventListener('click', function(e) {
        if (!grafiekToggleDropdown.contains(e.target) && e.target !== grafiekToggleBtn) {
            grafiekToggleDropdown.classList.add('hidden');
        }
    });
    
    // Laad opgeslagen grafiek voorkeuren
    function laadGrafiekVoorkeuren() {
        const voorkeuren = JSON.parse(localStorage.getItem('analyticsGrafiekVoorkeuren') || '{}');
        
        grafiekToggles.forEach(toggle => {
            const grafiekNaam = toggle.dataset.grafiek;
            const isZichtbaar = voorkeuren[grafiekNaam] !== undefined ? voorkeuren[grafiekNaam] : toggle.checked;
            
            toggle.checked = isZichtbaar;
            toggleGrafiek(grafiekNaam, isZichtbaar);
        });
    }
    
    // Toggle grafiek zichtbaarheid
    function toggleGrafiek(grafiekNaam, isZichtbaar) {
        const grafiekCard = document.querySelector(`[data-chart-id="${grafiekNaam}"]`);
        if (grafiekCard) {
            grafiekCard.style.display = isZichtbaar ? '' : 'none';
            
            // Resize chart na toggle
            if (charts[grafiekNaam] && isZichtbaar) {
                setTimeout(() => charts[grafiekNaam].resize(), 100);
            }
        }
    }
    
    // Sla grafiek voorkeuren op
    function slaGrafiekVoorkeurenOp() {
        const voorkeuren = {};
        grafiekToggles.forEach(toggle => {
            voorkeuren[toggle.dataset.grafiek] = toggle.checked;
        });
        localStorage.setItem('analyticsGrafiekVoorkeuren', JSON.stringify(voorkeuren));
    }
    
    // Event listeners voor grafiek toggles
    grafiekToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            toggleGrafiek(this.dataset.grafiek, this.checked);
            slaGrafiekVoorkeurenOp();
        });
    });
    
    // Laad voorkeuren bij pagina load
    laadGrafiekVoorkeuren();
});
</script>
@endsection
