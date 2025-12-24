<?php

namespace App\Jobs;

use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = now();

        // Get all active reminders where next_reminder_date <= now
        $reminders = Reminder::where('status', '!=', 'completed')
            ->whereNotNull('next_reminder_date')
            ->where('next_reminder_date', '<=', $now)
            ->with(['user', 'category'])
            ->get();

        foreach ($reminders as $reminder) {
            try {
                // Send notifications via enabled channels
                if ($reminder->notify_email && $reminder->user->email) {
                    SendEmailNotification::dispatch($reminder);
                }

                if ($reminder->notify_sms && $reminder->user->phone) {
                    SendSmsNotification::dispatch($reminder);
                }

                if ($reminder->notify_whatsapp && $reminder->user->whatsapp) {
                    SendWhatsAppNotification::dispatch($reminder);
                }

                // Update last_sent_at
                $reminder->update(['last_sent_at' => $now]);

                // Calculate and set next_reminder_date based on frequency
                $nextReminderDate = $this->calculateNextReminderDate($reminder);
                $reminder->update(['next_reminder_date' => $nextReminderDate]);

                // If one-time reminder and due date passed, mark as completed
                if ($reminder->frequency_type === 'once' && $reminder->due_date->isPast()) {
                    $reminder->update([
                        'status' => 'completed',
                        'completed_at' => $now,
                    ]);
                }

                // Update status to overdue if past due date
                if ($reminder->status === 'pending' && $reminder->due_date->isPast()) {
                    $reminder->update(['status' => 'overdue']);
                }
            } catch (\Exception $e) {
                Log::error("Error processing reminder {$reminder->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Calculate next reminder date based on frequency and advance notice.
     */
    private function calculateNextReminderDate(Reminder $reminder): ?Carbon
    {
        $dueDate = Carbon::parse($reminder->due_date);
        $now = now();

        // If due date is in the past and it's a one-time reminder, return null
        if ($reminder->frequency_type === 'once' && $dueDate->isPast()) {
            return null;
        }

        // Calculate next due date based on frequency
        $nextDueDate = match ($reminder->frequency_type) {
            'daily' => $dueDate->copy()->addDay(),
            'weekly' => $this->calculateWeeklyNextDate($reminder, $dueDate),
            'monthly' => $dueDate->copy()->addMonth(),
            'yearly' => $dueDate->copy()->addYear(),
            'custom' => $this->calculateCustomNextDate($reminder, $dueDate),
            default => null,
        };

        if (!$nextDueDate) {
            return null;
        }

        // Calculate next reminder date based on advance notice
        if (!empty($reminder->advance_notice_days)) {
            $daysBefore = max($reminder->advance_notice_days);
            $nextReminderDate = $nextDueDate->copy()->subDays($daysBefore);

            // If the next reminder date is in the past, use the next advance notice
            if ($nextReminderDate->isPast()) {
                // For recurring reminders, calculate the next occurrence
                if ($reminder->frequency_type !== 'once') {
                    // Keep calculating until we find a future date
                    while ($nextReminderDate->isPast() && $nextDueDate->isFuture()) {
                        $nextDueDate = match ($reminder->frequency_type) {
                            'daily' => $nextDueDate->addDay(),
                            'weekly' => $this->calculateWeeklyNextDate($reminder, $nextDueDate),
                            'monthly' => $nextDueDate->addMonth(),
                            'yearly' => $nextDueDate->addYear(),
                            'custom' => $this->calculateCustomNextDate($reminder, $nextDueDate),
                            default => null,
                        };
                        $nextReminderDate = $nextDueDate->copy()->subDays($daysBefore);
                    }
                }
            }

            return $nextReminderDate->isFuture() ? $nextReminderDate : null;
        }

        return $nextDueDate->isFuture() ? $nextDueDate : null;
    }

    /**
     * Calculate next date for weekly frequency.
     */
    private function calculateWeeklyNextDate(Reminder $reminder, Carbon $currentDate): Carbon
    {
        $frequencyValue = $reminder->frequency_value ?? [];
        $days = $frequencyValue['days'] ?? [1]; // Default to Monday

        $nextDate = $currentDate->copy()->addWeek();
        $dayOfWeek = $nextDate->dayOfWeek;

        // Find the next matching day
        while (!in_array($dayOfWeek, $days)) {
            $nextDate->addDay();
            $dayOfWeek = $nextDate->dayOfWeek;
        }

        return $nextDate;
    }

    /**
     * Calculate next date for custom frequency.
     */
    private function calculateCustomNextDate(Reminder $reminder, Carbon $currentDate): ?Carbon
    {
        $frequencyValue = $reminder->frequency_value ?? [];
        $unit = $frequencyValue['unit'] ?? 'days';
        $value = $frequencyValue['value'] ?? 1;

        return match ($unit) {
            'days' => $currentDate->copy()->addDays($value),
            'weeks' => $currentDate->copy()->addWeeks($value),
            'months' => $currentDate->copy()->addMonths($value),
            default => null,
        };
    }
}
