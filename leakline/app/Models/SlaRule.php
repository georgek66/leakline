<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaRule extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'severity_id',
        'response_time',
        'resolution_time'
    ];

    public function severity()
    {
        return $this->belongsTo(SeverityLevel::class);
    }
}
