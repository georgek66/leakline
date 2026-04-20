<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaborLog extends Model
{
    public $timestamps = false;

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
        'ended_at' => 'datetime',
        'hours' => 'decimal:2',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'workorder_id');
    }
}
