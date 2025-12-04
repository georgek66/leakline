<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'incident_id',
        'message',
        'type',
        'template_id',
        'status',
        'sent_at'
    ];

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }
}
