<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'incident_id',
        'actor_id',
        'event_type',
        'message',
        'meta',
        'created_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime'
    ];
}
