<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class, 'assigned_team_id');
    }
}
