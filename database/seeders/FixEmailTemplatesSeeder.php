<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class FixEmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Zet alle "Performance Pulse" templates op organisatie_id = null (standaard templates)
        EmailTemplate::where('name', 'LIKE', '%Performance Pulse%')
            ->update(['organisatie_id' => null]);
        
        $this->command->info('✅ Performance Pulse templates zijn nu standaard templates (organisatie_id = null)');
        
        // Log hoeveel templates er zijn
        $standaardTemplates = EmailTemplate::whereNull('organisatie_id')->count();
        $orgTemplates = EmailTemplate::whereNotNull('organisatie_id')->count();
        
        $this->command->info("📊 Standaard templates: {$standaardTemplates}");
        $this->command->info("📊 Organisatie templates: {$orgTemplates}");
    }
}