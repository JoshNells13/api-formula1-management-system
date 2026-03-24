<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'country',
        'principal',
        'base',
        'status',
    ];

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }
}
