<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Display user settings.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $settings = $user->settings;

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'user' => $user,
        ]);
    }

    /**
     * Update user settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'default_view' => 'nullable|in:list,calendar,kanban',
            'items_per_page' => 'nullable|integer|min:5|max:100',
            'date_format' => 'nullable|in:gregorian,hijri',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
            'weekend_notifications' => 'boolean',
            'default_advance_notice' => 'nullable|array',
            'default_notify_email' => 'boolean',
            'default_notify_sms' => 'boolean',
            'default_notify_whatsapp' => 'boolean',
            // User profile fields
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|in:ar,en',
        ]);

        // Update user profile
        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'timezone' => $validated['timezone'] ?? 'Asia/Riyadh',
            'locale' => $validated['locale'] ?? 'ar',
        ]);

        // Update or create settings
        $settings = $user->settings ?? new UserSetting(['user_id' => $user->id]);
        $settings->fill([
            'default_view' => $validated['default_view'] ?? 'list',
            'items_per_page' => $validated['items_per_page'] ?? 15,
            'date_format' => $validated['date_format'] ?? 'gregorian',
            'quiet_hours_start' => $validated['quiet_hours_start'] ?? null,
            'quiet_hours_end' => $validated['quiet_hours_end'] ?? null,
            'weekend_notifications' => $validated['weekend_notifications'] ?? true,
            'default_advance_notice' => $validated['default_advance_notice'] ?? [7, 1],
            'default_notify_email' => $validated['default_notify_email'] ?? true,
            'default_notify_sms' => $validated['default_notify_sms'] ?? false,
            'default_notify_whatsapp' => $validated['default_notify_whatsapp'] ?? false,
        ]);
        $settings->save();

        return redirect()->route('settings.index')
            ->with('success', __('settings_updated'));
    }

    /**
     * Send test notification.
     */
    public function testNotification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'channel' => 'required|in:email,sms,whatsapp',
        ]);

        $user = $request->user();

        // Dispatch test notification job
        // This will be implemented in the notification jobs

        return redirect()->back()
            ->with('success', __('test_notification_sent'));
    }
}
