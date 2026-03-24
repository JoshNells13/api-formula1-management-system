<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'name','chassis_code',
        'top_speed','acceleration','downforce','reliability'
    ];

    public function participants()
    {
        return $this->hasMany(RaceParticipant::class);
    }
}
