<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationLogController extends Controller
{
    /**
     * Display a listing of notification logs.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = NotificationLog::where('user_id', $user->id)
            ->with(['reminder']);

        // Apply filters
        if ($request->has('channel') && $request->channel) {
            $query->where('channel', $request->channel);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('reminder', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Notifications/Index', [
            'logs' => $logs,
            'filters' => $request->only(['channel', 'status', 'search', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Resend a failed notification.
     */
    public function resend(Request $request, NotificationLog $notificationLog): RedirectResponse
    {
        $this->authorize('resend', $notificationLog);

        // Reset the log entry
        $notificationLog->update([
            'status' => 'pending',
            'retry_count' => 0,
            'error_message' => null,
        ]);

        // Dispatch the notification job
        // This will be handled by the ProcessReminders job

        return redirect()->back()
            ->with('success', __('notification_resent'));
    }
}
