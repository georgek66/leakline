<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $fillable = [
        'incident_id',
        'assigned_team_id',
        'assigned_to',
        'priority',
        'due_date',
        'status',
        'resolution_code_id',
        'estimated_water_saved_liters',
        'closure_notes'
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'assigned_team_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolutionCode()
    {
        return $this->belongsTo(ResolutionCode::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class, 'workorder_id');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'workorder_id');
    }

    public function laborLogs()
    {
        return $this->hasMany(LaborLog::class, 'workorder_id');
    }

    public function signature()
    {
        return $this->hasOne(Signature::class, 'workorder_id');
    }
}
