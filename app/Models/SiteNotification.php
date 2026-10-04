<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteNotification extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'type', 'title', 'body', 'link_url', 'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Notifications for a given user. */
    public static function forUser(int $userId)
    {
        return static::where('user_id', $userId);
    }

    /** Create a notification for a user. */
    public static function notify(int $userId, string $type, string $title, ?string $body = null, ?string $linkUrl = null): self
    {
        return static::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link_url' => $linkUrl,
        ]);
    }

    public function markRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
