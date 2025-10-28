<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'bot_id',
        'telegram_user_id',
        'telegram_chat_id',
        'state',
        'post_content',
        'media_type',
        'media_files',
        'selected_channels',
        'session_data',
    ];

    protected $casts = [
        'media_files' => 'array',
        'selected_channels' => 'array',
        'session_data' => 'array',
    ];

    /**
     * Клиент сессии
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Бот сессии
     */
    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }
}
