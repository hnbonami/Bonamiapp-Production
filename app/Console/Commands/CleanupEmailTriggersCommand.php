<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailTrigger;
use App\Models\EmailTemplate;

class CleanupEmailTriggersCommand extends Command
{
    /**
     * De naam en signature van het artisan command.
     *
     * @var string
     */
    protected $signature = 'email:cleanup-triggers 
                            {--dry-run : Voer een droge run uit zonder wijzigingen}
                            {--fix : Probeer automatisch defecte triggers te repareren}';

    /**
     * De beschrijving van het console command.
     *
     * @var string
     */
    protected $description = 'Ruim defecte email triggers op en repareer ongeldige data';

    /**
     * Voer het console command uit.
     */
    public function handle()
    {
        $this->info('🔍 Start Email Triggers Cleanup voor Bonami Sportcoaching');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $shouldFix = $this->option('fix');

        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - Geen wijzigingen worden doorgevoerd');
            $this->newLine();
        }

        // Haal alle triggers op
        $triggers = EmailTrigger::all();
        $this->info("📊 Gevonden {$triggers->count()} email triggers");

        $issues = [];
        $fixed = 0;

        foreach ($triggers as $trigger) {
            $triggerIssues = $this->analyzeTrigger($trigger);
            
            if (!empty($triggerIssues)) {
                $issues[] = [
                    'trigger' => $trigger,
                    'issues' => $triggerIssues
                ];

                $this->warn("⚠️  Trigger ID {$trigger->id}: " . implode(', ', $triggerIssues));

                if ($shouldFix && !$isDryRun) {
                    if ($this->fixTrigger($trigger, $triggerIssues)) {
                        $fixed++;
                        $this->info("✅ Trigger ID {$trigger->id} gerepareerd");
                    }
                }
            }
        }

        $this->newLine();
        $this->info("📋 Samenvatting:");
        $this->line("  - Totaal triggers: {$triggers->count()}");
        $this->line("  - Triggers met problemen: " . count($issues));
        $this->line("  - Gerepareerde triggers: {$fixed}");

        if (!empty($issues) && !$shouldFix) {
            $this->newLine();
            $this->info("💡 Gebruik --fix om automatisch te repareren:");
            $this->line("   php artisan email:cleanup-triggers --fix");
        }

        return Command::SUCCESS;
    }

    /**
     * Analyseer een trigger op problemen
     */
    private function analyzeTrigger(EmailTrigger $trigger): array
    {
        $issues = [];

        if (empty($trigger->name)) {
            $issues[] = 'Naam ontbreekt';
        }

        if (empty($trigger->trigger_type)) {
            $issues[] = 'Trigger type ontbreekt';
        } elseif (!in_array($trigger->trigger_type, array_keys(EmailTrigger::getTypes()))) {
            $issues[] = 'Ongeldig trigger type: ' . $trigger->trigger_type;
        }

        if (empty($trigger->email_template_id)) {
            $issues[] = 'Email template ontbreekt';
        } elseif (!$trigger->emailTemplate) {
            $issues[] = 'Email template bestaat niet meer';
        }

        if (!is_null($trigger->trigger_data) && !is_array($trigger->trigger_data)) {
            $issues[] = 'Ongeldige trigger_data format';
        }

        return $issues;
    }

    /**
     * Probeer een trigger te repareren
     */
    private function fixTrigger(EmailTrigger $trigger, array $issues): bool
    {
        $wasFixed = false;

        try {
            // Fix ontbrekende naam
            if (empty($trigger->name) && !empty($trigger->trigger_type)) {
                $types = EmailTrigger::getTypes();
                $trigger->name = $types[$trigger->trigger_type] ?? 'Automatische Trigger';
                $wasFixed = true;
            }

            // Fix ongeldige of ontbrekende trigger types op basis van naam
            if (empty($trigger->trigger_type) || !in_array($trigger->trigger_type, array_keys(EmailTrigger::getTypes()))) {
                $name = strtolower($trigger->name ?? '');
                
                if (str_contains($name, 'testzadel') || str_contains($name, 'herinnering')) {
                    $trigger->trigger_type = 'testzadel_reminder';
                    $trigger->trigger_data = ['schedule' => 'daily', 'time' => '10:00', 'days_before_due' => 7];
                } elseif (str_contains($name, 'verjaardag') || str_contains($name, 'birthday')) {
                    $trigger->trigger_type = 'birthday';
                    $trigger->trigger_data = ['schedule' => 'daily', 'time' => '09:00'];
                } elseif (str_contains($name, 'welkom') && str_contains($name, 'klant')) {
                    $trigger->trigger_type = 'welcome_customer';
                    $trigger->trigger_data = ['event' => 'customer_created', 'delay_minutes' => 0];
                } elseif (str_contains($name, 'welkom') && str_contains($name, 'medewerker')) {
                    $trigger->trigger_type = 'welcome_employee';
                    $trigger->trigger_data = ['event' => 'employee_created', 'delay_minutes' => 0];
                } elseif (str_contains($name, 'uitnodiging') && str_contains($name, 'klant')) {
                    $trigger->trigger_type = 'klant_invitation';
                    $trigger->trigger_data = ['frequency' => 'manual'];
                } elseif (str_contains($name, 'uitnodiging') && str_contains($name, 'medewerker')) {
                    $trigger->trigger_type = 'medewerker_invitation';
                    $trigger->trigger_data = ['frequency' => 'manual'];
                } elseif (str_contains($name, 'doorverwijzing') || str_contains($name, 'referral') || str_contains($name, 'verwijzing')) {
                    $trigger->trigger_type = 'referral_thank_you';
                    $trigger->trigger_data = ['event' => 'customer_referred', 'delay_minutes' => 0];
                } else {
                    $trigger->trigger_type = 'birthday'; // Default fallback
                    $trigger->trigger_data = ['schedule' => 'daily', 'time' => '09:00'];
                }
                $wasFixed = true;
            }

            // Fix ongeldige trigger_data
            if (!is_null($trigger->trigger_data) && !is_array($trigger->trigger_data)) {
                $trigger->trigger_data = [];
                $wasFixed = true;
            }

            // Fix ontbrekende template
            if (empty($trigger->email_template_id)) {
                $defaultTemplate = \App\Models\EmailTemplate::where('type', $trigger->trigger_type)->first()
                    ?? \App\Models\EmailTemplate::first();
                
                if ($defaultTemplate) {
                    $trigger->email_template_id = $defaultTemplate->id;
                    $wasFixed = true;
                }
            }

            // Set description if missing
            if (empty($trigger->description)) {
                $descriptions = [
                    'testzadel_reminder' => 'Automatische herinnering voor testzadel terugbreng datum',
                    'birthday' => 'Automatische verjaardag felicitaties voor klanten en medewerkers',
                    'welcome_customer' => 'Welkomstmail voor nieuwe klanten',
                    'welcome_employee' => 'Welkomstmail voor nieuwe medewerkers',
                    'klant_invitation' => 'Handmatige uitnodigingen voor klanten',
                    'medewerker_invitation' => 'Handmatige uitnodigingen voor medewerkers',
                    'referral_thank_you' => 'Dankje email voor klant doorverwijzingen',
                ];
                
                $trigger->description = $descriptions[$trigger->trigger_type] ?? 'Automatische email trigger';
                $wasFixed = true;
            }

            if ($wasFixed) {
                $trigger->save();
            }

            return $wasFixed;

        } catch (\Exception $e) {
            $this->error("❌ Kon trigger ID {$trigger->id} niet repareren: " . $e->getMessage());
            return false;
        }
    }
}