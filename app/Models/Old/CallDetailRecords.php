<?php

namespace App\Models\Old;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallDetailRecords extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $connection = "old";

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'event_id',
        'phone',
        'extension',
        'path',
        'call_at',
        'type',
    ];
}
