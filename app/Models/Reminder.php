<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'frequency_type',
        'frequency_value',
        'advance_notice_days',
        'notify_email',
        'notify_sms',
        'notify_whatsapp',
        'next_reminder_date',
        'last_sent_at',
        'completed_at',
        'parent_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'next_reminder_date' => 'datetime',
            'last_sent_at' => 'datetime',
            'completed_at' => 'datetime',
            'frequency_value' => 'array',
            'advance_notice_days' => 'array',
            'notify_email' => 'boolean',
            'notify_sms' => 'boolean',
            'notify_whatsapp' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Reminder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Reminder::class, 'parent_id');
    }
}
