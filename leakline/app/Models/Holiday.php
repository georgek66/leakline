<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'date',
        'name',
        'region'
    ];
}
