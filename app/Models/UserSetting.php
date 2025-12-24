<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'default_view',
        'items_per_page',
        'date_format',
        'quiet_hours_start',
        'quiet_hours_end',
        'weekend_notifications',
        'default_advance_notice',
        'default_notify_email',
        'default_notify_sms',
        'default_notify_whatsapp',
    ];

    protected function casts(): array
    {
        return [
            'items_per_page' => 'integer',
            'quiet_hours_start' => 'datetime:H:i',
            'quiet_hours_end' => 'datetime:H:i',
            'weekend_notifications' => 'boolean',
            'default_advance_notice' => 'array',
            'default_notify_email' => 'boolean',
            'default_notify_sms' => 'boolean',
            'default_notify_whatsapp' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
