<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bot extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'username',
        'token',
        'webhook_url',
        'is_active',
    ];

    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Клиент, которому принадлежит бот
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Каналы бота
     */
    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }
}
