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
use Illuminate\Support\Facades\Mail;

class SendEmailNotification implements ShouldQueue
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

        if (!$user->email) {
            Log::warning("Cannot send email notification: User {$user->id} has no email");
            return;
        }

        $log = NotificationLog::create([
            'reminder_id' => $this->reminder->id,
            'user_id' => $user->id,
            'channel' => 'email',
            'recipient' => $user->email,
            'status' => 'pending',
        ]);

        try {
            Mail::raw($this->getEmailContent(), function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject(__('reminder_notification_subject', ['title' => $this->reminder->title]));
            });

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
     * Get email content.
     */
    private function getEmailContent(): string
    {
        $reminder = $this->reminder;
        $dueDate = $reminder->due_date->format('Y-m-d H:i');

        return __("reminder_email_body", [
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
            ->where('channel', 'email')
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
