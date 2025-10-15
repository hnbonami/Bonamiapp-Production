<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Voeg doorverwijzing trigger toe aan email_triggers tabel
     */
    public function up(): void
    {
        // Controleer of de trigger al bestaat
        $existingTrigger = DB::table('email_triggers')
            ->where('trigger_type', 'referral_thank_you')
            ->first();
            
        if (!$existingTrigger) {
            // Zoek een geschikte email template voor doorverwijzing (of gebruik eerste beschikbare)
            $emailTemplateId = null;
            try {
                // Probeer eerst een referral template te vinden
                $referralTemplate = DB::table('email_templates')
                    ->where('type', 'referral_thank_you')
                    ->where('is_active', true)
                    ->first();
                    
                if ($referralTemplate) {
                    $emailTemplateId = $referralTemplate->id;
                } else {
                    // Gebruik een algemene template als fallback
                    $fallbackTemplate = DB::table('email_templates')
                        ->where('is_active', true)
                        ->first();
                    $emailTemplateId = $fallbackTemplate ? $fallbackTemplate->id : 1; // Default naar ID 1
                }
            } catch (\Exception $e) {
                $emailTemplateId = 1; // Hard fallback
            }
            
            // Voeg nieuwe doorverwijzing trigger toe
            DB::table('email_triggers')->insert([
                'trigger_key' => 'referral_thank_you_trigger',
                'name' => 'Doorverwijzing Dankje Email',
                'type' => 'referral_thank_you',
                'description' => 'Automatische dankje email voor klanten die andere klanten doorverwijzen',
                'trigger_type' => 'referral_thank_you',
                'trigger_data' => json_encode([
                    'event' => 'customer_referred',
                    'delay_minutes' => 0,
                    'automatic' => true
                ]),
                'email_template_id' => $emailTemplateId, // Voeg template ID toe
                'is_active' => true,
                'emails_sent' => 0,
                'conditions' => json_encode([]),
                'settings' => json_encode([
                    'send_immediately' => true,
                    'track_opens' => true,
                    'track_clicks' => true
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "✅ Doorverwijzing trigger toegevoegd\n";
        } else {
            echo "⚠️ Doorverwijzing trigger bestaat al\n";
        }
        
        // Update bestaande triggers die mogelijk doorverwijzing gerelateerd zijn
        $possibleReferralTriggers = DB::table('email_triggers')
            ->where('name', 'like', '%doorverwijzing%')
            ->orWhere('name', 'like', '%referral%')
            ->orWhere('name', 'like', '%verwijzing%')
            ->where('trigger_type', '!=', 'referral_thank_you')
            ->get();
            
        foreach ($possibleReferralTriggers as $trigger) {
            // Zorg ervoor dat deze triggers een email template hebben
            $emailTemplateId = $trigger->email_template_id;
            if (!$emailTemplateId) {
                try {
                    $fallbackTemplate = DB::table('email_templates')
                        ->where('is_active', true)
                        ->first();
                    $emailTemplateId = $fallbackTemplate ? $fallbackTemplate->id : 1;
                } catch (\Exception $e) {
                    $emailTemplateId = 1;
                }
            }
            
            DB::table('email_triggers')
                ->where('id', $trigger->id)
                ->update([
                    'type' => 'referral_thank_you',
                    'trigger_type' => 'referral_thank_you',
                    'trigger_data' => json_encode([
                        'event' => 'customer_referred',
                        'delay_minutes' => 0,
                        'automatic' => true
                    ]),
                    'email_template_id' => $emailTemplateId, // Zorg voor template ID
                    'description' => 'Automatische dankje email voor klant doorverwijzingen',
                    'updated_at' => now()
                ]);
                
            echo "✅ Trigger '{$trigger->name}' geconverteerd naar doorverwijzing type\n";
        }
    }

    /**
     * Verwijder doorverwijzing trigger
     */
    public function down(): void
    {
        DB::table('email_triggers')
            ->where('trigger_type', 'referral_thank_you')
            ->delete();
    }
};