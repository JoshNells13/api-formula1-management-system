<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'team_id', 'name', 'chassis_code',
        'top_speed', 'acceleration', 'downforce', 'reliability',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function participants()
    {
        return $this->hasMany(RaceParticipant::class);
    }
}
