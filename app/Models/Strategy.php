<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Strategy extends Model
{
    protected $fillable = [
        'race_participant_id','pit_stop_lap','tyre_type','fuel_load'
    ];

    public function participant()
    {
        return $this->belongsTo(RaceParticipant::class);
    }
}
