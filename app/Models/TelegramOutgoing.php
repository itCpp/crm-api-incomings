<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramOutgoing extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'method',
        'bot_token',
        'chat_id',
        'request_data',
        'response_code',
        'response_data',
        'created_at',
    ];

    /**
     * The attributes that should be cast
     *
     * @var array
     */
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];
}
