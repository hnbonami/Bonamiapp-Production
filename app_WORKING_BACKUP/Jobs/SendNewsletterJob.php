<?php

namespace App\Jobs;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $newsletterId;
    public string $email;
    public ?string $name;
    public bool $isTest;
    public ?int $recipientId;

    public function __construct(int $newsletterId, string $email, ?string $name = null, bool $isTest = false, ?int $recipientId = null)
    {
        $this->newsletterId = $newsletterId;
        $this->email = $email;
        $this->name = $name;
        $this->isTest = $isTest;
        $this->recipientId = $recipientId;
    }

    public function handle(): void
    {
        $newsletter = Newsletter::with('blocks')->findOrFail($this->newsletterId);

        // Render HTML similarly to controller
        $html = app(\App\Http\Controllers\NewsletterController::class)->preview($newsletter)->getContent();

        $subject = $newsletter->subject ?: 'Nieuwsbrief';
        $fromEmail = $newsletter->from_email ?: config('mail.from.address');
        $fromName = $newsletter->from_name ?: config('mail.from.name');

        try {
            Mail::send([], [], function ($message) use ($subject, $fromEmail, $fromName, $html) {
                $message->to($this->email, $this->name);
                $message->subject($subject);
                $message->from($fromEmail, $fromName);
                $message->html($html);
            });

            if ($this->recipientId) {
                NewsletterRecipient::where('id', $this->recipientId)->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'error' => null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Newsletter send failed: ' . $e->getMessage());
            if ($this->recipientId) {
                NewsletterRecipient::where('id', $this->recipientId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
            }
            throw $e;
        }
    }
}
