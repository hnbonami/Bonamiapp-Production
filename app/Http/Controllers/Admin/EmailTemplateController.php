    /**
     * Reset/update standaard Performance Pulse templates
     * Alleen voor superadmin
     */
    public function resetDefaultTemplates()
    {
        try {
            // Check of user superadmin is
            if (!auth()->user()->is_superadmin) {
                return redirect()->route('admin.email.templates')
                    ->with('error', 'Alleen superadmin kan standaard templates resetten.');
            }
            
            \Log::info('🔄 Superadmin reset standaard templates', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email
            ]);
            
            // Run de seeder
            \Artisan::call('db:seed', [
                '--class' => 'DefaultEmailTemplatesSeeder',
                '--force' => true
            ]);
            
            $output = \Artisan::output();
            \Log::info('✅ Standaard templates gereset', ['output' => $output]);
            
            return redirect()->route('admin.email.templates')
                ->with('success', '✅ Standaard Performance Pulse templates zijn bijgewerkt!');
                
        } catch (\Exception $e) {
            \Log::error('❌ Failed to reset templates: ' . $e->getMessage());
            
            return redirect()->route('admin.email.templates')
                ->with('error', 'Er ging iets mis bij het resetten van templates: ' . $e->getMessage());
        }
    }
    
    /**
     * Toon preview van een template
     */
    public function preview($id)
    {
        $template = EmailTemplate::findOrFail($id);
        
        // Vervang placeholders met demo data voor preview
        $html = $template->body_html;
        $html = str_replace('@{{voornaam}}', 'Jan', $html);
        $html = str_replace('@{{naam}}', 'Janssen', $html);
        $html = str_replace('@{{email}}', 'jan@voorbeeld.nl', $html);
        $html = str_replace('@{{bedrijf_naam}}', 'Bonami Sportcoaching', $html);
        $html = str_replace('@{{merk}}', 'Selle Italia', $html);
        $html = str_replace('@{{model}}', 'SLR Boost', $html);
        $html = str_replace('@{{uitgeleend_op}}', now()->format('d-m-Y'), $html);
        $html = str_replace('@{{leeftijd}}', '35', $html);
        $html = str_replace('@{{jaar}}', now()->year, $html);
        $html = str_replace('@{{website_url}}', config('app.url'), $html);
        $html = str_replace('@{{unsubscribe_url}}', config('app.url') . '/unsubscribe', $html);
        $html = str_replace('@{{temporary_password}}', 'DemoWachtwoord123', $html);
        
        return response($html)->header('Content-Type', 'text/html');
    }