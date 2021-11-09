<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallsSectorQueue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'sip_server',
        'last_sector_id',
        'next_to_count',
        'date',
        'data_counters',
        'counter'
    ];

    /**
     * The attributes that should be cast
     *
     * @var array
     */
    protected $casts = [
        'data_counters' => 'array',
    ];
}
