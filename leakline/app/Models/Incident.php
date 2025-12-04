<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $fillable = [
        'reporter_id',
        'category_id',
        'severity_id',
        'area_id',
        'location',
        'description',
        'status'
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function severity()
    {
        return $this->belongsTo(SeverityLevel::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function contact()
    {
        return $this->hasOne(IncidentContact::class);
    }

    public function media()
    {
        return $this->hasMany(IncidentMedia::class);
    }

    public function events()
    {
        return $this->hasMany(IncidentEvent::class);
    }

    public function relations()
    {
        return $this->hasMany(IncidentRelation::class, 'source_incident_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
