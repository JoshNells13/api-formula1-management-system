<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LapTime extends Model
{
    protected $fillable = [
        'result_id','lap_number','lap_time','tyre_condition','fuel_remaining'
    ];

    public function result()
    {
        return $this->belongsTo(Result::class);
    }
}
