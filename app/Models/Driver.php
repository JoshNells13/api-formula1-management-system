<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'team_id', 'name', 'number', 'nationality',
        'speed', 'consistency', 'tyre_management', 'status',
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
