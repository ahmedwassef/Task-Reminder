<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [300, 900, 3600]; // 5min, 15min, 1hr

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Reminder $reminder
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->reminder->user;

        if (!$user->whatsapp) {
            Log::warning("Cannot send WhatsApp notification: User {$user->id} has no WhatsApp number");
            return;
        }

        $log = NotificationLog::create([
            'reminder_id' => $this->reminder->id,
            'user_id' => $user->id,
            'channel' => 'whatsapp',
            'recipient' => $user->whatsapp,
            'status' => 'pending',
        ]);

        try {
            $twilioSid = config('services.twilio.sid');
            $twilioToken = config('services.twilio.token');
            $whatsappNumber = config('services.twilio.whatsapp_number');

            if (!$twilioSid || !$twilioToken || !$whatsappNumber) {
                throw new \Exception('Twilio WhatsApp configuration is missing');
            }

            $client = new Client($twilioSid, $twilioToken);

            // Format WhatsApp number (ensure it starts with whatsapp:)
            $to = $user->whatsapp;
            if (!str_starts_with($to, 'whatsapp:')) {
                $to = 'whatsapp:' . $to;
            }

            $message = $client->messages->create(
                $to,
                [
                    'from' => $whatsappNumber,
                    'body' => $this->getWhatsAppContent(),
                ]
            );

            $log->update([
                'status' => 'success',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => $this->attempts(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Get WhatsApp content.
     */
    private function getWhatsAppContent(): string
    {
        $reminder = $this->reminder;
        $dueDate = $reminder->due_date->format('Y-m-d H:i');

        return __("reminder_whatsapp_body", [
            'name' => $reminder->user->name,
            'title' => $reminder->title,
            'description' => $reminder->description ?? __('no_description'),
            'due_date' => $dueDate,
            'priority' => __("priority_{$reminder->priority}"),
            'category' => $reminder->category ? $reminder->category->name_ar : __('no_category'),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $log = NotificationLog::where('reminder_id', $this->reminder->id)
            ->where('channel', 'whatsapp')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($log) {
            $log->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'retry_count' => $this->tries,
            ]);
        }
    }
}
