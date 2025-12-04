<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscalationRule extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'severity_id',
        'when_status',
        'after_minutes',
        'notify_role_id',
        'notify_team_id',
        'notify_user_id',
        'action'
    ];

    public function severity()
    {
        return $this->belongsTo(SeverityLevel::class);
    }
}
