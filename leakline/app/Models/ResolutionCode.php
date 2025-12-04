<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResolutionCode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'label',
        'description',
        'default_estimated_savings_liters'
    ];

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
