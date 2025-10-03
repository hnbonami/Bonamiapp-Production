<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\TestzadelsController;

class SendTestzadelReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'testzadels:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send automatic reminder emails for overdue testzadel rentals (only for enabled automatic mailing)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new TestzadelsController();
        $sentCount = $controller->sendAutomaticReminders();
        
        $this->info("Sent {$sentCount} automatic testzadel reminder emails.");
        
        return Command::SUCCESS;
    }
}