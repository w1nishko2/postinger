<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'client_id',
        'content',
        'media_type',
        'media_files',
        'telegram_message_id',
        'status',
        'error_message',
        'published_at',
    ];

    protected $casts = [
        'media_files' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * Канал, в который опубликован пост
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Клиент, создавший пост
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
