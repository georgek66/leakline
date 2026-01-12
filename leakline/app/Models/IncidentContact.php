<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentContact extends Model
{
    protected $fillable = [
        'incident_id',
        'name',
        'email',
        'phone',
        'preferred_locale',
        'consent_version',
        'consented_at',
        'gdpr_token',
        'latitude',
        'longitude',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
