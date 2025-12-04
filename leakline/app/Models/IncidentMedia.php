<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentMedia extends Model
{
    protected $fillable = [
        'incident_id',
        'file_url',
        'media_type'
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
