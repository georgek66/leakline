<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = [
        'name',
        'channel',
        'locale',
        'subject',
        'body',
        'placeholders',
        'is_active'
    ];

    protected $casts = [
        'placeholders' => 'array'
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'template_id');
    }
}
