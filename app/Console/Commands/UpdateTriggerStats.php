<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailIntegrationService;

class UpdateTriggerStats extends Command
{
    protected $signature = 'email:update-trigger-stats';
    protected $description = 'Update trigger statistics for existing emails';

    public function handle()
    {
        $emailService = new EmailIntegrationService();
        
        // Get counts from email_logs and update triggers
        $emailLogs = \App\Models\EmailLog::selectRaw('trigger_name, COUNT(*) as count')
                                       ->groupBy('trigger_name')
                                       ->get();
        
        foreach ($emailLogs as $log) {
            if ($log->trigger_name) {
                $this->info("Updating {$log->trigger_name}: {$log->count} emails");
                
                // Create or update trigger
                $trigger = \App\Models\EmailTrigger::firstOrCreate(
                    ['type' => $log->trigger_name],
                    [
                        'name' => ucfirst(str_replace('_', ' ', $log->trigger_name)),
                        'description' => 'Auto-generated trigger for ' . $log->trigger_name,
                        'is_active' => true,
                    ]
                );
                
                $trigger->update([
                    'emails_sent' => $log->count,
                    'last_run_at' => now()
                ]);
            }
        }
        
        $this->info('Trigger statistics updated successfully!');
    }
}