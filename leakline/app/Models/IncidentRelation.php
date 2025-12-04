<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentRelation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'source_incident_id',
        'target_incident_id',
        'relation',
        'confidence',
        'merged_by',
        'merged_at',
        'note'
    ];
}
