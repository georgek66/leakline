<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'workorder_id',
        'signed_by_name',
        'file_url',
        'signed_at'
    ];

    protected $casts = [
        'signed_at' => 'datetime'
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
