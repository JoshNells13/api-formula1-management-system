<?php

namespace App\Http\Controllers;

use App\Models\LapTime;
use App\Models\Result;
use Illuminate\Http\Request;

class LaptimeController extends Controller
{
    /**
     * Return all lap times for a given result ID.
     * Includes tyre condition and fuel data per lap.
     */
    public function getByResult($resultId)
    {
        $result = Result::with(['participant.driver', 'participant.race'])->findOrFail($resultId);

        $lapTimes = LapTime::where('result_id', $resultId)
            ->orderBy('lap_number')
            ->get(['lap_number', 'lap_time', 'tyre_condition', 'fuel_remaining']);

        $fastestLap = $lapTimes->min('lap_time');

        return response()->json([
            'result_id'   => $result->id,
            'driver'      => $result->participant->driver->name,
            'race'        => $result->participant->race->name,
            'status'      => $result->status,
            'total_time'  => $result->total_time,
            'fastest_lap' => $fastestLap,
            'lap_count'   => $lapTimes->count(),
            'lap_times'   => $lapTimes,
        ]);
    }
}
