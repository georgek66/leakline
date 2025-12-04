<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeverityLevel extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    public function slaRule()
    {
        return $this->hasOne(SlaRule::class, 'severity_id');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'severity_id');
    }
}
