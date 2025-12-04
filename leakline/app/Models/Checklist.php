<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = [
        'workorder_id',
        'task_description',
        'is_completed'
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'workorder_id');
    }
}
