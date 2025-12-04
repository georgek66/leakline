<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GdprRequest extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'incident_id',
        'email',
        'type',
        'status',
        'submitted_at',
        'processed_at',
        'note'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
