<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    protected $fillable = [
        'name','location','total_laps','weather','race_date'
    ];

    public function participants()
    {
        return $this->hasMany(RaceParticipant::class);
    }
}
