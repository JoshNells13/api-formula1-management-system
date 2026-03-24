<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name','number','nationality',
        'speed','consistency','tyre_management','status'
    ];

    public function participants()
    {
        return $this->hasMany(RaceParticipant::class);
    }
}
