<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReminderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Reminder::where('user_id', $user->id)
            ->with(['category', 'attachments']);

        // Apply filters
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $reminders = $query->paginate($request->get('per_page', 15));

        $categories = Category::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('is_system', true);
        })->get();

        return Inertia::render('Reminders/Index', [
            'reminders' => $reminders,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'priority', 'status', 'search', 'date_from', 'date_to', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $user = $request->user();
        $categories = Category::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('is_system', true);
        })->get();

        $settings = $user->settings;

        return Inertia::render('Reminders/Create', [
            'categories' => $categories,
            'defaultSettings' => $settings ? [
                'advance_notice' => $settings->default_advance_notice,
                'notify_email' => $settings->default_notify_email,
                'notify_sms' => $settings->default_notify_sms,
                'notify_whatsapp' => $settings->default_notify_whatsapp,
            ] : [
                'advance_notice' => [7, 1],
                'notify_email' => true,
                'notify_sms' => false,
                'notify_whatsapp' => false,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,critical',
            'frequency_type' => 'required|in:once,daily,weekly,monthly,yearly,custom',
            'frequency_value' => 'nullable|array',
            'advance_notice_days' => 'nullable|array',
            'notify_email' => 'boolean',
            'notify_sms' => 'boolean',
            'notify_whatsapp' => 'boolean',
        ]);

        $user = $request->user();
        $dueDate = Carbon::parse($validated['due_date']);

        // Calculate next reminder date based on advance notice
        $nextReminderDate = null;
        if (!empty($validated['advance_notice_days'])) {
            $daysBefore = max($validated['advance_notice_days']);
            $nextReminderDate = $dueDate->copy()->subDays($daysBefore);
        } else {
            $nextReminderDate = $dueDate->copy();
        }

        $reminder = Reminder::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $dueDate,
            'priority' => $validated['priority'],
            'frequency_type' => $validated['frequency_type'],
            'frequency_value' => $validated['frequency_value'] ?? null,
            'advance_notice_days' => $validated['advance_notice_days'] ?? [],
            'notify_email' => $validated['notify_email'] ?? true,
            'notify_sms' => $validated['notify_sms'] ?? false,
            'notify_whatsapp' => $validated['notify_whatsapp'] ?? false,
            'next_reminder_date' => $nextReminderDate->isPast() ? null : $nextReminderDate,
            'status' => $dueDate->isPast() ? 'overdue' : 'pending',
        ]);

        return redirect()->route('reminders.show', $reminder->id)
            ->with('success', __('reminder_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Reminder $reminder): Response
    {
        $this->authorize('view', $reminder);

        $reminder->load(['category', 'attachments', 'notificationLogs', 'parent', 'children']);

        return Inertia::render('Reminders/Show', [
            'reminder' => $reminder,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Reminder $reminder): Response
    {
        $this->authorize('update', $reminder);

        $user = $request->user();
        $categories = Category::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('is_system', true);
        })->get();

        return Inertia::render('Reminders/Edit', [
            'reminder' => $reminder,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reminder $reminder): RedirectResponse
    {
        $this->authorize('update', $reminder);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,critical',
            'frequency_type' => 'required|in:once,daily,weekly,monthly,yearly,custom',
            'frequency_value' => 'nullable|array',
            'advance_notice_days' => 'nullable|array',
            'notify_email' => 'boolean',
            'notify_sms' => 'boolean',
            'notify_whatsapp' => 'boolean',
        ]);

        $dueDate = Carbon::parse($validated['due_date']);

        // Recalculate next reminder date
        $nextReminderDate = null;
        if (!empty($validated['advance_notice_days'])) {
            $daysBefore = max($validated['advance_notice_days']);
            $nextReminderDate = $dueDate->copy()->subDays($daysBefore);
        } else {
            $nextReminderDate = $dueDate->copy();
        }

        $reminder->update([
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $dueDate,
            'priority' => $validated['priority'],
            'frequency_type' => $validated['frequency_type'],
            'frequency_value' => $validated['frequency_value'] ?? null,
            'advance_notice_days' => $validated['advance_notice_days'] ?? [],
            'notify_email' => $validated['notify_email'] ?? true,
            'notify_sms' => $validated['notify_sms'] ?? false,
            'notify_whatsapp' => $validated['notify_whatsapp'] ?? false,
            'next_reminder_date' => $nextReminderDate->isPast() ? null : $nextReminderDate,
            'status' => $reminder->status === 'completed' ? 'completed' : ($dueDate->isPast() ? 'overdue' : 'pending'),
        ]);

        return redirect()->route('reminders.show', $reminder->id)
            ->with('success', __('reminder_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Reminder $reminder): RedirectResponse
    {
        $this->authorize('delete', $reminder);

        $reminder->delete();

        return redirect()->route('reminders.index')
            ->with('success', __('reminder_deleted'));
    }

    /**
     * Mark reminder as complete.
     */
    public function complete(Request $request, Reminder $reminder): RedirectResponse
    {
        $this->authorize('update', $reminder);

        $reminder->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // If recurring, create next occurrence
        if ($reminder->frequency_type !== 'once' && $reminder->frequency_type !== null) {
            $this->createNextOccurrence($reminder);
        }

        return redirect()->back()->with('success', __('reminder_completed'));
    }

    /**
     * Snooze reminder.
     */
    public function snooze(Request $request, Reminder $reminder): RedirectResponse
    {
        $this->authorize('update', $reminder);

        $validated = $request->validate([
            'snooze_until' => 'required|date|after:now',
        ]);

        $reminder->update([
            'next_reminder_date' => Carbon::parse($validated['snooze_until']),
        ]);

        return redirect()->back()->with('success', __('reminder_snoozed'));
    }

    /**
     * Duplicate reminder.
     */
    public function duplicate(Request $request, Reminder $reminder): RedirectResponse
    {
        $this->authorize('view', $reminder);

        $newReminder = $reminder->replicate();
        $newReminder->status = 'pending';
        $newReminder->completed_at = null;
        $newReminder->last_sent_at = null;
        $newReminder->parent_id = null;
        $newReminder->save();

        // Duplicate attachments if any
        foreach ($reminder->attachments as $attachment) {
            $newAttachment = $attachment->replicate();
            $newAttachment->reminder_id = $newReminder->id;
            $newAttachment->save();
        }

        return redirect()->route('reminders.edit', $newReminder->id)
            ->with('success', __('reminder_duplicated'));
    }

    /**
     * Calendar view.
     */
    public function calendar(Request $request): Response
    {
        $user = $request->user();
        $reminders = Reminder::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->with('category')
            ->get()
            ->map(function ($reminder) {
                return [
                    'id' => $reminder->id,
                    'title' => $reminder->title,
                    'start' => $reminder->due_date->format('Y-m-d'),
                    'priority' => $reminder->priority,
                    'category' => $reminder->category ? $reminder->category->name_ar : null,
                    'color' => $reminder->category ? $reminder->category->color : '#3B82F6',
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('Reminders/Calendar', [
            'reminders' => $reminders,
        ]);
    }

    /**
     * Create next occurrence for recurring reminder.
     */
    private function createNextOccurrence(Reminder $reminder): void
    {
        $nextDueDate = $this->calculateNextDueDate($reminder);

        if (!$nextDueDate) {
            return;
        }

        $nextReminderDate = null;
        if (!empty($reminder->advance_notice_days)) {
            $daysBefore = max($reminder->advance_notice_days);
            $nextReminderDate = $nextDueDate->copy()->subDays($daysBefore);
        } else {
            $nextReminderDate = $nextDueDate->copy();
        }

        Reminder::create([
            'user_id' => $reminder->user_id,
            'category_id' => $reminder->category_id,
            'title' => $reminder->title,
            'description' => $reminder->description,
            'due_date' => $nextDueDate,
            'priority' => $reminder->priority,
            'frequency_type' => $reminder->frequency_type,
            'frequency_value' => $reminder->frequency_value,
            'advance_notice_days' => $reminder->advance_notice_days,
            'notify_email' => $reminder->notify_email,
            'notify_sms' => $reminder->notify_sms,
            'notify_whatsapp' => $reminder->notify_whatsapp,
            'next_reminder_date' => $nextReminderDate->isPast() ? null : $nextReminderDate,
            'status' => $nextDueDate->isPast() ? 'overdue' : 'pending',
            'parent_id' => $reminder->parent_id ?? $reminder->id,
        ]);
    }

    /**
     * Calculate next due date based on frequency.
     */
    private function calculateNextDueDate(Reminder $reminder): ?Carbon
    {
        $currentDueDate = Carbon::parse($reminder->due_date);

        return match ($reminder->frequency_type) {
            'daily' => $currentDueDate->addDay(),
            'weekly' => $currentDueDate->addWeek(),
            'monthly' => $currentDueDate->addMonth(),
            'yearly' => $currentDueDate->addYear(),
            'custom' => $this->calculateCustomNextDate($reminder, $currentDueDate),
            default => null,
        };
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
            'days' => $currentDate->addDays($value),
            'weeks' => $currentDate->addWeeks($value),
            'months' => $currentDate->addMonths($value),
            default => null,
        };
    }
}
