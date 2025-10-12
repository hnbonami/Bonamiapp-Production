<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListAllEmailTriggersCommand extends Command
{
    protected $signature = 'email:list-all-triggers';
    protected $description = 'Toon alle email triggers in de database';

    public function handle()
    {
        $this->info('📋 ALLE EMAIL TRIGGERS IN DATABASE:');
        $this->info('');
        
        $triggers = DB::table('email_triggers')
            ->orderBy('id')
            ->get();
            
        if ($triggers->isEmpty()) {
            $this->error('❌ Geen triggers gevonden in database');
            return;
        }
        
        foreach ($triggers as $trigger) {
            $this->info("🎯 ID: {$trigger->id} | {$trigger->name}");
            $this->line("   Type: {$trigger->trigger_type} | Status: " . ($trigger->is_active ? '✅ Actief' : '❌ Inactief'));
            $this->line("   Template ID: {$trigger->email_template_id} | Emails sent: " . ($trigger->emails_sent ?? 0));
            if (!empty($trigger->description)) {
                $this->line("   Beschrijving: {$trigger->description}");
            }
            $this->line('');
        }
        
        // Toon count per type
        $this->info('📊 TRIGGERS PER TYPE:');
        $typeCounts = DB::table('email_triggers')
            ->select('trigger_type', DB::raw('count(*) as count'))
            ->groupBy('trigger_type')
            ->get();
            
        foreach ($typeCounts as $typeCount) {
            $this->line("   {$typeCount->trigger_type}: {$typeCount->count}");
        }
        
        // Zoek specifiek naar doorverwijzing triggers
        $this->info('');
        $this->info('🔍 DOORVERWIJZING TRIGGERS:');
        $referralTriggers = DB::table('email_triggers')
            ->where('trigger_type', 'referral_thank_you')
            ->orWhere('name', 'like', '%doorverwijzing%')
            ->orWhere('name', 'like', '%referral%')
            ->get();
            
        if ($referralTriggers->isEmpty()) {
            $this->warn('⚠️ Geen doorverwijzing triggers gevonden');
        } else {
            foreach ($referralTriggers as $trigger) {
                $this->info("   ✅ {$trigger->name} (ID: {$trigger->id}) - {$trigger->trigger_type}");
            }
        }
    }
}