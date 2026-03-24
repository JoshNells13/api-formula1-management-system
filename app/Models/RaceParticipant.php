<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaceParticipant extends Model
{
    protected $fillable = [
        'race_id','driver_id','car_id','grid_position'
    ];

    public function race()
    {
        return $this->belongsTo(Race::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function strategy()
    {
        return $this->hasOne(Strategy::class);
    }

    public function result()
    {
        return $this->hasOne(Result::class);
    }
}
