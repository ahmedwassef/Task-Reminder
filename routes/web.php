<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationLogController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reminders
    Route::resource('reminders', ReminderController::class);
    Route::post('reminders/{reminder}/complete', [ReminderController::class, 'complete'])
        ->name('reminders.complete');
    Route::post('reminders/{reminder}/snooze', [ReminderController::class, 'snooze'])
        ->name('reminders.snooze');
    Route::post('reminders/{reminder}/duplicate', [ReminderController::class, 'duplicate'])
        ->name('reminders.duplicate');
    Route::get('reminders/calendar/view', [ReminderController::class, 'calendar'])
        ->name('reminders.calendar');

    // Categories
    Route::resource('categories', CategoryController::class);

    // Notifications
    Route::get('notifications', [NotificationLogController::class, 'index'])
        ->name('notifications.index');
    Route::post('notifications/{notificationLog}/resend', [NotificationLogController::class, 'resend'])
        ->name('notifications.resend');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])
        ->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])
        ->name('settings.update');
    Route::post('settings/test-notification', [SettingsController::class, 'testNotification'])
        ->name('settings.test-notification');
});

require __DIR__.'/settings.php';
