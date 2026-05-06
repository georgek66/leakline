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
        'status',
        'longitude',
        'latitude',
        'ticket_id',
        'client_id',
    ];

    protected $appends = ['duplicates_count'];

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
    public function slaRule()
    {
        return $this->belongsTo(SlaRule::class, 'severity_id', 'severity_id');
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
    //SLA Rules functions for timers
    public function responseDueAt(){
        $rule = $this->slaRule;
        if (!$rule || !$this->created_at){
            return null;
        }
        return $this->created_at->copy()->addHours($rule->response_time);
    }

    public function resolutionDueAt(){
        $rule =$this->slaRule;
        //If there is no rule or created at, return null
        if(!$rule || !$this->created_at){
            return null;
        }
        return $this->created_at->copy()->addHours($rule->resolution_time);
    }

    public function slaMinutesLeft(){
        $due = $this->responseDueAt();
        if (!$due){
            return null;
        }
        return (int)now()->diffInMinutes($due,false);
    }
    public function slaResolutionMinutesLeft(){
        $due = $this->resolutionDueAt();
        if (!$due){
            return null;
        }
        return (int)now()->diffInMinutes($due, false);
    }

    public function nearbyDuplicatesCount(): int
    {
        $latitude = $this->latitude;
        $longitude = $this->longitude;

        if ($latitude === null|| $longitude === null) {
            return 0;
        }

        $distanceMeters = 50;
        $hours = 24;

        return Incident::query()
            ->select('incidents.*')
            ->whereKeyNot($this->id)
            ->whereNotNull('location_geom')
            ->where('created_at', '>=', now()->subHours($hours))
            ->whereNotIn('status', ['closed', 'resolved', 'cancelled'])
            ->whereRaw(
                "ST_DWithin(incidents.location_geom::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)",
                [$longitude, $latitude, $distanceMeters]
            )
            ->count();
    }
    public function getDuplicatesCountAttribute()
    {
        return $this->nearbyDuplicatesCount();
    }
}
