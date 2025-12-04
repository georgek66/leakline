<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaborLog extends Model
{
    protected $fillable = [
        'workorder_id',
        'user_id',
        'started_at',
        'ended_at',
        'hours',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'workorder_id');
    }
}
