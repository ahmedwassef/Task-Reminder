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

class SendSmsNotification implements ShouldQueue
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

        if (!$user->phone) {
            Log::warning("Cannot send SMS notification: User {$user->id} has no phone number");
            return;
        }

        $log = NotificationLog::create([
            'reminder_id' => $this->reminder->id,
            'user_id' => $user->id,
            'channel' => 'sms',
            'recipient' => $user->phone,
            'status' => 'pending',
        ]);

        try {
            $twilioSid = config('services.twilio.sid');
            $twilioToken = config('services.twilio.token');
            $twilioNumber = config('services.twilio.phone_number');

            if (!$twilioSid || !$twilioToken || !$twilioNumber) {
                throw new \Exception('Twilio configuration is missing');
            }

            $client = new Client($twilioSid, $twilioToken);

            $message = $client->messages->create(
                $user->phone,
                [
                    'from' => $twilioNumber,
                    'body' => $this->getSmsContent(),
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
     * Get SMS content.
     */
    private function getSmsContent(): string
    {
        $reminder = $this->reminder;
        $dueDate = $reminder->due_date->format('Y-m-d H:i');

        return __("reminder_sms_body", [
            'title' => $reminder->title,
            'due_date' => $dueDate,
            'priority' => __("priority_{$reminder->priority}"),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $log = NotificationLog::where('reminder_id', $this->reminder->id)
            ->where('channel', 'sms')
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
