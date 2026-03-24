<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
        'race_participant_id','total_time','fastest_lap','position','status'
    ];

    public function participant()
    {
        return $this->belongsTo(RaceParticipant::class);
    }

    public function lapTimes()
    {
        return $this->hasMany(LapTime::class);
    }
}
