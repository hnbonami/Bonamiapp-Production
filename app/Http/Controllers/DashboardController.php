<?php

namespace App\Http\Controllers;

use App\Models\DashboardWidget;
use App\Models\DashboardUserLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardController extends Controller
{
    use AuthorizesRequests;
    /**
     * Toon het dashboard met widgets
     */
    public function index()
    {
        $user = auth()->user();
        
        // Haal widgets op op basis van rol en organisatie
        $widgets = DashboardWidget::visibleFor($user)
            ->active()
            ->with('creator')
            ->get();

        // Haal user-specific layouts op, of gebruik CREATOR/WIDGET defaults
        $layouts = $widgets->map(function ($widget) use ($user) {
            // ALTIJD de creator layout gebruiken als basis
            $creatorLayout = DashboardUserLayout::where('widget_id', $widget->id)
                ->where('user_id', $widget->created_by)
                ->first();
            
            // Haal user layout op (als die bestaat)
            $userLayout = DashboardUserLayout::where('user_id', $user->id)
                ->where('widget_id', $widget->id)
                ->first();
            
            // Bepaal welke layout te gebruiken
            if ($userLayout && $user->id === $widget->created_by) {
                // Creator gebruikt zijn eigen layout
                $layout = $userLayout;
            } elseif ($creatorLayout) {
                // Niet-creators gebruiken ALTIJD de creator layout (live sync!)
                $layout = new DashboardUserLayout([
                    'user_id' => $user->id,
                    'widget_id' => $widget->id,
                    'grid_x' => $creatorLayout->grid_x,
                    'grid_y' => $creatorLayout->grid_y,
                    'grid_width' => $creatorLayout->grid_width,
                    'grid_height' => $creatorLayout->grid_height,
                    'is_visible' => true,
                ]);
                
                \Log::info('� Live sync: gebruiker ziet creator layout', [
                    'widget_id' => $widget->id,
                    'user_id' => $user->id,
                    'creator_id' => $widget->created_by,
                    'size' => $creatorLayout->grid_width . 'x' . $creatorLayout->grid_height,
                    'creator_layout_updated_at' => $creatorLayout->updated_at
                ]);
            } else {
                // Fallback: gebruik widget defaults
                $layout = new DashboardUserLayout([
                    'user_id' => $user->id,
                    'widget_id' => $widget->id,
                    'grid_x' => $widget->grid_x ?? 0,
                    'grid_y' => $widget->grid_y ?? 0,
                    'grid_width' => $widget->grid_width ?? 4,
                    'grid_height' => $widget->grid_height ?? 3,
                    'is_visible' => true,
                ]);
            }

            return [
                'widget' => $widget,
                'layout' => $layout,
            ];
        });

        Log::info('Dashboard geladen voor user', [
            'user_id' => $user->id,
            'role' => $user->role,
            'organisatie_id' => $user->organisatie_id,
            'widgets_count' => $widgets->count(),
            'layouts_sample' => $layouts->take(2)->map(fn($l) => [
                'widget_id' => $l['widget']->id,
                'title' => $l['widget']->title,
                'size' => $l['layout']->grid_width . 'x' . $l['layout']->grid_height,
                'position' => $l['layout']->grid_x . ',' . $l['layout']->grid_y,
            ])
        ]);

        return view('dashboard.index', compact('layouts'));
    }

    /**
     * Toon formulier voor nieuwe widget
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        
        // Check autorisatie via policy
        $this->authorize('create', DashboardWidget::class);

        $type = $request->get('type', 'text');

        return view('dashboard.create', compact('type'));
    }

    /**
     * Sla nieuwe widget op
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check autorisatie via policy
        $this->authorize('create', DashboardWidget::class);

        $validated = $request->validate([
            'type' => 'required|in:text,metric,chart,image,button',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'chart_type' => 'nullable|string',
            'chart_data' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
            'button_color' => 'nullable|string', // Knop kleur validatie
            'background_color' => 'nullable|string',
            'text_color' => 'nullable|string',
            'grid_width' => 'nullable|integer|min:1|max:12',
            'grid_height' => 'nullable|integer|min:1|max:10',
            'visibility' => 'required|in:everyone,medewerkers,only_me',
            'metric_type' => 'nullable|string', // NIEUW: Valideer metric type
        ]);

        // Medewerkers mogen geen 'everyone' visibility instellen (alleen admin)
        if ($user->role === 'medewerker' && $validated['visibility'] === 'everyone') {
            $validated['visibility'] = 'medewerkers';
            Log::warning('Medewerker probeerde everyone visibility in te stellen', [
                'user_id' => $user->id,
                'forced_to' => 'medewerkers'
            ]);
        }

        // Voor chart widgets: zorg dat chart_data altijd valid JSON is
        if ($validated['type'] === 'chart' && !empty($validated['chart_type'])) {
            $validated['chart_data'] = json_encode([
                'chart_type' => $validated['chart_type'],
                'scope' => 'auto',
                'periode' => 'laatste-30-dagen'
            ]);
        }

        // Upload afbeelding indien aanwezig
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'widget_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            if (app()->environment('production')) {
                // PRODUCTIE: Upload naar httpd.www/uploads/widgets
                $destinationPath = base_path('../httpd.www/uploads/widgets');
                
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                $image->move($destinationPath, $imageName);
                $validated['image_path'] = 'widgets/' . $imageName;
                
                \Log::info('✅ Widget image uploaded to PRODUCTION', [
                    'path' => $destinationPath . '/' . $imageName
                ]);
            } else {
                // LOKAAL: Upload naar storage/app/public/widgets
                $validated['image_path'] = $image->store('widgets', 'public');
                
                \Log::info('✅ Widget image uploaded to LOCAL', [
                    'path' => $validated['image_path']
                ]);
            }
        }

        // Zet creator en organisatie
        $validated['created_by'] = $user->id;
        $validated['organisatie_id'] = $user->organisatie_id;
        $validated['is_active'] = true;

        // Standaard posities en groottes
        $validated['grid_x'] = $validated['grid_x'] ?? 0;
        $validated['grid_y'] = $validated['grid_y'] ?? 0;
        $validated['grid_width'] = $validated['grid_width'] ?? 4;
        $validated['grid_height'] = $validated['grid_height'] ?? 3;

    // Als metric_type is ingesteld en niet 'custom', haal live data op
    if ($validated['type'] === 'metric' && isset($validated['metric_type']) && $validated['metric_type'] !== 'custom') {
        // Security check: Medewerkers mogen alleen hun eigen metrics gebruiken
        $adminOnlyMetrics = [
            'totaal_klanten',
            'totaal_bikefits',
            'nieuwe_klanten_maand',
            'omzet_organisatie_maand',
            'omzet_organisatie_kwartaal',
            'actieve_medewerkers'
        ];
        
        if (in_array($validated['metric_type'], $adminOnlyMetrics) && !auth()->user()->isBeheerder()) {
            return redirect()->back()
                ->withErrors(['metric_type' => 'Je hebt geen toegang tot deze metric type.'])
                ->withInput();
        }
        
        // Bereken de metric waarde
        $metricValue = $this->calculateMetricValue($validated['metric_type']);
        $validated['content'] = $metricValue['formatted'];
    }        $widget = DashboardWidget::create($validated);

        // ⚡ BELANGRIJK: Maak direct een layout aan voor de creator met de widget defaults
        // Zodat andere users (medewerkers/klanten) deze layout kunnen overnemen
        DashboardUserLayout::create([
            'user_id' => $user->id,
            'widget_id' => $widget->id,
            'grid_x' => $validated['grid_x'],
            'grid_y' => $validated['grid_y'],
            'grid_width' => $validated['grid_width'],
            'grid_height' => $validated['grid_height'],
            'is_visible' => true,
        ]);

        Log::info('Widget aangemaakt met creator layout', [
            'widget_id' => $widget->id,
            'type' => $widget->type,
            'user_id' => $user->id,
            'organisatie_id' => $widget->organisatie_id,
            'size' => $validated['grid_width'] . 'x' . $validated['grid_height']
        ]);

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Widget succesvol toegevoegd!');
    }

    /**
     * Bereken metric waarde (helper method)
     */
    private function calculateMetricValue($metricType)
    {
        $controller = new DashboardStatsController();
        $request = new Request(['metric_type' => $metricType]);
        $response = $controller->calculateMetric($request);
        
        return json_decode($response->getContent(), true);
    }

    /**
     * Maak welkomst/handleiding widget aan voor nieuwe organisatie
     * 
     * @param int $organisatieId
     * @param int $userId (creator/admin)
     * @return DashboardWidget
     */
    public static function createWelcomeWidget($organisatieId, $userId)
    {
        // Haal organisatie op voor personalisatie
        $organisatie = \App\Models\Organisatie::find($organisatieId);
        $organisatieNaam = $organisatie ? $organisatie->naam : 'Performance Pulse';
        
        // Maak de welkomst widget aan
        $widget = DashboardWidget::create([
            'type' => 'text',
            'title' => '👋 Welkom bij ' . $organisatieNaam,
            'content' => self::getWelcomeWidgetContent($organisatieNaam),
            'background_color' => '#ffffff', // Wit
            'text_color' => '#374151', // Zachte dark gray
            'grid_x' => 0,
            'grid_y' => 0,
            'grid_width' => 12, // Volledige breedte
            'grid_height' => 12, // ⚡ HOGER: voor volledige zichtbaarheid
            'visibility' => 'everyone', // Zichtbaar voor iedereen in organisatie
            'created_by' => $userId,
            'organisatie_id' => $organisatieId,
            'is_active' => true,
        ]);

        // Maak direct een layout aan voor de creator
        DashboardUserLayout::create([
            'user_id' => $userId,
            'widget_id' => $widget->id,
            'grid_x' => 0,
            'grid_y' => 0,
            'grid_width' => 12,
            'grid_height' => 8,
            'is_visible' => true,
        ]);

        Log::info('✅ Welkomst widget aangemaakt voor nieuwe organisatie', [
            'widget_id' => $widget->id,
            'organisatie_id' => $organisatieId,
            'organisatie_naam' => $organisatieNaam,
            'user_id' => $userId
        ]);

        return $widget;
    }

    /**
     * Genereer HTML content voor welkomst widget
     */
    private static function getWelcomeWidgetContent($organisatieNaam = 'Performance Pulse')
    {
        return <<<HTML
<div class="welcome-widget" style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.6;">
    <!-- Header met subtiele accent kleuren -->
    <div style="background: linear-gradient(135deg, #f8fafc 0%, #c8e1eb15 100%); color: #475569; padding: 1.5rem; border-radius: 12px 12px 0 0; margin: -1rem -1rem 0 -1rem; border-bottom: 2px solid #c8e1eb;">
        <h2 style="margin: 0 0 0.5rem 0; font-size: 1.75rem; font-weight: 700; color: #475569;">
            🎉 Welkom bij <span style="color: #cb5739;">{$organisatieNaam}</span>
        </h2>
        <p style="margin: 0; opacity: 0.8; font-size: 1rem; color: #64748b;">
            Jouw complete platform voor bikefits, inspanningstesten en klantenbeheer
        </p>
    </div>

    <!-- Tabbladen met accent kleuren -->
    <div style="margin-top: 1rem;">
        <div class="tab-buttons" style="display: flex; gap: 0.5rem; border-bottom: 2px solid #c8e1eb; margin-bottom: 1rem;">
            <button onclick="showTab('start')" class="tab-btn active" data-tab="start" style="padding: 0.75rem 1.25rem; background: #c8e1eb; color: #475569; border: none; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; transition: all 0.2s;">
                🚀 Snel Starten
            </button>
            <button onclick="showTab('features')" class="tab-btn" data-tab="features" style="padding: 0.75rem 1.25rem; background: #f8fafc; color: #94a3b8; border: none; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; transition: all 0.2s;">
                ✨ Mogelijkheden
            </button>
            <button onclick="showTab('tips')" class="tab-btn" data-tab="tips" style="padding: 0.75rem 1.25rem; background: #f8fafc; color: #94a3b8; border: none; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; transition: all 0.2s;">
                💡 Tips & Tricks
            </button>
        </div>

        <!-- Tab: Snel Starten -->
        <div id="tab-start" class="tab-content" style="display: block;">
            <h3 style="color: #475569; margin-top: 0; font-size: 1.25rem;">📋 Eerste stappen</h3>
            
            <div style="background: #c8e1eb20; border-left: 3px solid #c8e1eb; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                <h4 style="margin: 0 0 0.5rem 0; color: #475569; font-size: 1rem;">1️⃣ Klanten toevoegen</h4>
                <p style="margin: 0; color: #64748b; font-size: 0.95rem;">
                    Ga naar <strong style="color: #cb5739;">Klanten</strong> → <strong>Nieuwe klant</strong> om je eerste klant toe te voegen.
                    Vul naam, contactgegevens en relevante informatie in.
                </p>
            </div>

            <div style="background: #f8faf9; border-left: 3px solid #cbd5e1; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                <h4 style="margin: 0 0 0.5rem 0; color: #475569; font-size: 1rem;">2️⃣ Inspanningstesten aanmaken</h4>
                <p style="margin: 0; color: #64748b; font-size: 0.95rem;">
                    Selecteer een klant → <strong style="color: #cb5739;">Nieuwe inspanningstest</strong>. Kies testtype (fietstest/looptest),
                    vul testresultaten in en genereer automatisch drempelwaarden + trainingsadvies!
                </p>
            </div>

            <div style="background: #fafaf8; border-left: 3px solid #cbd5e1; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                <h4 style="margin: 0 0 0.5rem 0; color: #475569; font-size: 1rem;">3️⃣ Bikefits uitvoeren</h4>
                <p style="margin: 0; color: #64748b; font-size: 0.95rem;">
                    Ga naar <strong style="color: #cb5739;">Klanten</strong> → selecteer klant → <strong>Nieuwe bikefit</strong>.
                    Vul metingen in en gebruik de automatische calculator voor optimale fietshouding.
                </p>
            </div>

            <div style="background: #cb573910; border-left: 3px solid #cb5739; padding: 1rem; border-radius: 6px;">
                <h4 style="margin: 0 0 0.5rem 0; color: #475569; font-size: 1rem;">4️⃣ Dashboard widgets aanpassen</h4>
                <p style="margin: 0; color: #64748b; font-size: 0.95rem;">
                    Klik rechtsboven op <strong style="color: #cb5739;">+ Widget toevoegen</strong> om je dashboard aan te passen.
                    Sleep widgets om ze te verplaatsen en pas grootte aan naar wens!
                </p>
            </div>
        </div>

        <!-- Tab: Mogelijkheden -->
        <div id="tab-features" class="tab-content" style="display: none;">
            <h3 style="color: #475569; margin-top: 0; font-size: 1.25rem;">✨ Wat kan je allemaal?</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div style="background: #c8e1eb15; border: 1px solid #c8e1eb; border-radius: 8px; padding: 1rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">👥</div>
                    <h4 style="margin: 0 0 0.5rem 0; color: #475569;">Klantenbeheer</h4>
                    <ul style="margin: 0; padding-left: 1.25rem; color: #64748b; font-size: 0.9rem;">
                        <li>Volledige klantprofielen</li>
                        <li>Documenten & foto's uploaden</li>
                        <li>Historie van testen & bikefits</li>
                        <li>GDPR-compliant</li>
                    </ul>
                </div>

                <div style="background: #fafafa; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📊</div>
                    <h4 style="margin: 0 0 0.5rem 0; color: #475569;">Inspanningstesten</h4>
                    <ul style="margin: 0; padding-left: 1.25rem; color: #64748b; font-size: 0.9rem;">
                        <li>Automatische drempelberekening</li>
                        <li>AI-powered trainingsadvies</li>
                        <li>Test vergelijking (progressie)</li>
                        <li>PDF rapporten genereren</li>
                    </ul>
                </div>

                <div style="background: #cb573908; border: 1px solid #cb573940; border-radius: 8px; padding: 1rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🚴</div>
                    <h4 style="margin: 0 0 0.5rem 0; color: #475569;">Bikefits</h4>
                    <ul style="margin: 0; padding-left: 1.25rem; color: #64748b; font-size: 0.9rem;">
                        <li>Geautomatiseerde calculator</li>
                        <li>Voor & na metingen</li>
                        <li>Mobiliteitstabellen</li>
                        <li>Professionele rapporten</li>
                    </ul>
                </div>

                <div style="background: #fafafa; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📈</div>
                    <h4 style="margin: 0 0 0.5rem 0; color: #475569;">Analytics & Widgets</h4>
                    <ul style="margin: 0; padding-left: 1.25rem; color: #64748b; font-size: 0.9rem;">
                        <li>Real-time statistieken</li>
                        <li>Aanpasbaar dashboard</li>
                        <li>Grafieken & metrics</li>
                        <li>Export functionaliteit</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tab: Tips & Tricks -->
        <div id="tab-tips" class="tab-content" style="display: none;">
            <h3 style="color: #475569; margin-top: 0; font-size: 1.25rem;">💡 Handige tips</h3>
            
            <div style="background: #c8e1eb15; border: 1px solid #c8e1eb; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                <h4 style="margin: 0 0 0.5rem 0; color: #475569;">⌨️ Sneltoetsen</h4>
                <ul style="margin: 0; padding-left: 1.25rem; color: #64748b;">
                    <li><kbd>Ctrl/Cmd + K</kbd> - Snelle zoekfunctie (overal in de app)</li>
                    <li><kbd>Ctrl/Cmd + N</kbd> - Nieuwe klant aanmaken</li>
                    <li>Klik op een klant om direct alle info te zien</li>
                </ul>
            </div>

            <div style="background: #fafaf8; border: 1px solid #e5e5e0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                <h4 style="margin: 0 0 0.5rem 0; color: #475569;">🎯 Best practices</h4>
                <ul style="margin: 0; padding-left: 1.25rem; color: #64748b;">
                    <li>Vul altijd <strong style="color: #cb5739;">doelstellingen</strong> in bij klanten → betere AI-adviezen!</li>
                    <li>Upload <strong>testresultaten</strong> direct na de test → geen werk verloren</li>
                    <li>Gebruik <strong style="color: #cb5739;">test vergelijking</strong> om progressie te tonen aan klanten</li>
                    <li>Maak gebruik van <strong>PDF export</strong> voor professionele rapportage</li>
                </ul>
            </div>

            <div style="background: #f8faf9; border: 1px solid #e0e5e3; border-radius: 8px; padding: 1rem;">
                <h4 style="margin: 0 0 0.5rem 0; color: #475569;">🔒 Privacy & Beveiliging</h4>
                <ul style="margin: 0; padding-left: 1.25rem; color: #64748b;">
                    <li>Alle klantdata is <strong>GDPR-compliant</strong> opgeslagen</li>
                    <li>Alleen jij en je medewerkers hebben toegang tot klantdata</li>
                    <li>Regelmatige backups voor dataveiligheid</li>
                    <li>Secure SSL-verbinding voor alle communicatie</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Footer met accent kleur -->
    <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid #c8e1eb; text-align: center; color: #94a3b8; font-size: 0.875rem;">
        <p style="margin: 0;">
            ❓ Hulp nodig? Neem contact op via <strong style="color: #cb5739;">support@performancepulse.be</strong>
        </p>
        <p style="margin: 0.5rem 0 0 0; font-size: 0.75rem; color: #cbd5e1;">
            💡 Tip: Deze widget kan je verwijderen of aanpassen via het tandwiel-icoon rechtsboven
        </p>
    </div>
</div>

<script>
function showTab(tabName) {
    // Verberg alle tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Verwijder active class van alle buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.style.background = '#f8fafc';
        btn.style.color = '#94a3b8';
    });
    
    // Toon geselecteerde tab
    document.getElementById('tab-' + tabName).style.display = 'block';
    
    // Activeer button met accent kleur
    const activeBtn = document.querySelector('.tab-btn[data-tab="' + tabName + '"]');
    activeBtn.style.background = '#c8e1eb';
    activeBtn.style.color = '#475569';
}

// Hover effect voor tab buttons met accent kleuren
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            if (this.style.background !== 'rgb(200, 225, 235)') {
                this.style.background = '#c8e1eb50';
            }
        });
        
        btn.addEventListener('mouseleave', function() {
            if (this.style.background !== 'rgb(200, 225, 235)') {
                this.style.background = '#f8fafc';
            }
        });
    });
});
</script>

<style>
kbd {
    background: #f8fafc;
    border: 1px solid #c8e1eb;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.875rem;
    font-family: monospace;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
</style>
HTML;
    }

    /**
     * Toon formulier voor widget bewerken
     */
    public function edit(DashboardWidget $widget)
    {
        $this->authorize('update', $widget);

        return view('dashboard.edit', compact('widget'));
    }

    /**
     * Update een widget
     */
    public function update(Request $request, DashboardWidget $widget)
    {
        $this->authorize('update', $widget);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'chart_type' => 'nullable|string',
            'chart_data' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
            'button_color' => 'nullable|string', // Knop kleur validatie
            'background_color' => 'nullable|string',
            'text_color' => 'nullable|string',
            'visibility' => 'required|in:everyone,medewerkers,only_me',
            'grid_width' => 'nullable|integer|min:1|max:12',
            'grid_height' => 'nullable|integer|min:1|max:10',
        ]);

        // Medewerkers mogen geen 'everyone' visibility instellen (alleen admin)
        if (auth()->user()->role === 'medewerker' && $validated['visibility'] === 'everyone') {
            $validated['visibility'] = 'medewerkers';
            Log::warning('Medewerker probeerde everyone visibility in te stellen', [
                'user_id' => auth()->id(),
                'widget_id' => $widget->id,
                'forced_to' => 'medewerkers'
            ]);
        }

        // Voor chart widgets: update chart_data indien chart_type is gewijzigd
        if ($widget->type === 'chart' && !empty($validated['chart_type'])) {
            $validated['chart_data'] = json_encode([
                'chart_type' => $validated['chart_type'],
                'scope' => 'auto',
                'periode' => 'laatste-30-dagen'
            ]);
        }

        // Upload nieuwe afbeelding indien aanwezig
        if ($request->hasFile('image')) {
            // Verwijder oude afbeelding
            if ($widget->image_path) {
                if (app()->environment('production')) {
                    $oldPath = base_path('../httpd.www/uploads/' . $widget->image_path);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                } else {
                    Storage::disk('public')->delete($widget->image_path);
                }
            }
            
            $image = $request->file('image');
            $imageName = 'widget_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            if (app()->environment('production')) {
                // PRODUCTIE: Upload naar httpd.www/uploads/widgets
                $destinationPath = base_path('../httpd.www/uploads/widgets');
                
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                $image->move($destinationPath, $imageName);
                $validated['image_path'] = 'widgets/' . $imageName;
                
                \Log::info('✅ Widget image updated in PRODUCTION', [
                    'path' => $destinationPath . '/' . $imageName
                ]);
            } else {
                // LOKAAL: Upload naar storage/app/public/widgets
                $validated['image_path'] = $image->store('widgets', 'public');
                
                \Log::info('✅ Widget image updated in LOCAL', [
                    'path' => $validated['image_path']
                ]);
            }
        }

        $widget->update($validated);

        // ⚡ BELANGRIJK: Update ook de creator layout als grid_width/height zijn gewijzigd
        if (isset($validated['grid_width']) || isset($validated['grid_height'])) {
            $layout = DashboardUserLayout::where('user_id', auth()->id())
                ->where('widget_id', $widget->id)
                ->first();
            
            if ($layout) {
                // Update bestaande layout
                $layout->update([
                    'grid_width' => $validated['grid_width'] ?? $layout->grid_width,
                    'grid_height' => $validated['grid_height'] ?? $layout->grid_height,
                ]);
                
                Log::info('Widget layout bijgewerkt', [
                    'widget_id' => $widget->id,
                    'user_id' => auth()->id(),
                    'new_size' => $validated['grid_width'] . 'x' . $validated['grid_height']
                ]);
            }
        }

        Log::info('Widget bijgewerkt', [
            'widget_id' => $widget->id,
            'user_id' => auth()->id()
        ]);

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Widget succesvol bijgewerkt!');
    }

    /**
     * Update widget layout (positie en grootte)
     */
    public function updateLayout(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'widget_id' => 'required|exists:dashboard_widgets,id',
            'grid_x' => 'required|integer|min:0',
            'grid_y' => 'required|integer|min:0',
            'grid_width' => 'required|integer|min:1|max:12',
            'grid_height' => 'required|integer|min:1|max:10',
        ]);

        $widget = DashboardWidget::findOrFail($validated['widget_id']);

        // Check of user deze widget mag verplaatsen/resizen
        if (!$widget->canBeDraggedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Je hebt geen toestemming om deze widget te verplaatsen.'
            ], 403);
        }

        // Update of create user layout
        $layout = DashboardUserLayout::updateOrCreate(
            [
                'user_id' => $user->id,
                'widget_id' => $widget->id,
            ],
            [
                'grid_x' => $validated['grid_x'],
                'grid_y' => $validated['grid_y'],
                'grid_width' => $validated['grid_width'],
                'grid_height' => $validated['grid_height'],
            ]
        );

        // ⚡ BELANGRIJK: Als je de CREATOR bent, update ook de widget defaults
        // Zodat andere users (medewerkers/klanten) de nieuwe grootte zien
        if ($widget->created_by === $user->id) {
            $widget->update([
                'grid_x' => $validated['grid_x'],
                'grid_y' => $validated['grid_y'],
                'grid_width' => $validated['grid_width'],
                'grid_height' => $validated['grid_height'],
            ]);
            
            Log::info('Widget defaults bijgewerkt door creator', [
                'widget_id' => $widget->id,
                'user_id' => $user->id,
                'position' => "{$validated['grid_x']},{$validated['grid_y']}",
                'size' => "{$validated['grid_width']}x{$validated['grid_height']}"
            ]);
        }

        Log::info('Widget layout bijgewerkt', [
            'widget_id' => $widget->id,
            'user_id' => $user->id,
            'is_creator' => $widget->created_by === $user->id,
            'position' => "{$validated['grid_x']},{$validated['grid_y']}",
            'size' => "{$validated['grid_width']}x{$validated['grid_height']}"
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layout opgeslagen!',
        ]);
    }

    /**
     * Toggle widget visibility
     */
    public function toggleVisibility(DashboardWidget $widget)
    {
        $this->authorize('view', $widget);

        $user = auth()->user();

        $layout = DashboardUserLayout::where('user_id', $user->id)
            ->where('widget_id', $widget->id)
            ->firstOrFail();

        $layout->update([
            'is_visible' => !$layout->is_visible,
        ]);

        return response()->json([
            'success' => true,
            'is_visible' => $layout->is_visible,
        ]);
    }

    /**
     * Verwijder een widget
     */
    public function destroy(DashboardWidget $widget)
    {
        $this->authorize('delete', $widget);

        // Verwijder afbeelding indien aanwezig
        if ($widget->image_path) {
            if (app()->environment('production')) {
                $imagePath = base_path('../httpd.www/uploads/' . $widget->image_path);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            } else {
                Storage::disk('public')->delete($widget->image_path);
            }
        }

        // Verwijder alle user layouts
        $widget->userLayouts()->delete();

        Log::info('Widget verwijderd', [
            'widget_id' => $widget->id,
            'user_id' => auth()->id()
        ]);

        $widget->delete();

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Widget succesvol verwijderd!');
    }
}