<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'reminder_id',
        'user_id',
        'channel',
        'recipient',
        'status',
        'error_message',
        'retry_count',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'retry_count' => 'integer',
        ];
    }

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(Reminder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
