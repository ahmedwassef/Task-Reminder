<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get statistics
        $totalReminders = Reminder::where('user_id', $user->id)->count();
        $pendingReminders = Reminder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $completedThisMonth = Reminder::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();
        $overdueReminders = Reminder::where('user_id', $user->id)
            ->where('status', 'overdue')
            ->count();

        // Get upcoming reminders
        $todaysReminders = Reminder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereDate('due_date', today())
            ->orderBy('due_date')
            ->with('category')
            ->get();

        $thisWeeksReminders = Reminder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->orderBy('due_date')
            ->with('category')
            ->limit(10)
            ->get();

        $thisMonthsReminders = Reminder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->orderBy('due_date')
            ->with('category')
            ->limit(10)
            ->get();

        $criticalReminders = Reminder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('priority', 'critical')
            ->orderBy('due_date')
            ->with('category')
            ->limit(5)
            ->get();

        // Recent activity
        $recentNotifications = $user->notificationLogs()
            ->with('reminder')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentCompleted = Reminder::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->with('category')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $totalReminders,
                'pending' => $pendingReminders,
                'completedThisMonth' => $completedThisMonth,
                'overdue' => $overdueReminders,
            ],
            'todaysReminders' => $todaysReminders,
            'thisWeeksReminders' => $thisWeeksReminders,
            'thisMonthsReminders' => $thisMonthsReminders,
            'criticalReminders' => $criticalReminders,
            'recentNotifications' => $recentNotifications,
            'recentCompleted' => $recentCompleted,
        ]);
    }
}
