<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\MailHelper;
use App\Models\Klant;
use App\Models\Medewerker;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendBirthdayEmails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'birthday:send-emails';

    /**
     * The console command description.
     */
    protected $description = 'Send birthday emails to users who have their birthday today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $birthdayCount = 0;

        $this->info('🎂 Checking for birthdays on ' . $today->format('d/m/Y'));

        // Check Klanten
        $klanten = Klant::whereRaw('DATE_FORMAT(geboortedatum, "%m-%d") = ?', [$today->format('m-d')])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($klanten as $klant) {
            $this->sendBirthdayEmail($klant, 'klant');
            $birthdayCount++;
            $this->info("✅ Birthday email sent to klant: {$klant->voornaam} {$klant->naam}");
        }

        // Check Medewerkers
        $medewerkers = Medewerker::whereRaw('DATE_FORMAT(geboortedatum, "%m-%d") = ?', [$today->format('m-d')])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($medewerkers as $medewerker) {
            $this->sendBirthdayEmail($medewerker, 'medewerker');
            $birthdayCount++;
            $this->info("✅ Birthday email sent to medewerker: {$medewerker->voornaam} {$medewerker->naam}");
        }

        // Check Users (if they have birthdate)
        $users = User::whereRaw('DATE_FORMAT(created_at, "%m-%d") = ?', [$today->format('m-d')])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        // Note: Users don't have birthdate field, so we skip this for now
        // You can add birthdate field to users table if needed

        $this->info("🎉 Total birthday emails sent: {$birthdayCount}");

        return Command::SUCCESS;
    }

    private function sendBirthdayEmail($person, $type)
    {
        try {
            MailHelper::smartSend('emails.birthday', [
                'person' => $person,
                'type' => $type
            ], function($message) use ($person) {
                $message->to($person->email, $person->voornaam . ' ' . $person->naam);
                // FORCE the correct sender - use config values to ensure consistency
                $message->from(config('mail.from.address'), config('mail.from.name'));
                $message->replyTo(config('mail.from.address'), config('mail.from.name'));
                $message->subject('Hiep hiep hoera! 🎂 Tijd voor een sportieve felicitatie!');
            });

            \Log::info("Birthday email sent to: {$person->email}");
        } catch (\Exception $e) {
            \Log::error("Failed to send birthday email to {$person->email}: " . $e->getMessage());
        }
    }
}