<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\Race;
use Illuminate\Http\Request;

class ResultController extends Controller
{

    public function index()
    {
        $results = Result::with(['participant.driver', 'participant.car', 'participant.race'])
            ->orderBy('position')
            ->get();

        return response()->json($results);
    }


    public function getByRace($raceId)
    {
        $race = Race::findOrFail($raceId);

        $results = Result::with(['participant.driver', 'participant.car'])
            ->whereHas('participant', fn($q) => $q->where('race_id', $raceId))
            ->orderBy('position')
            ->get()
            ->map(function ($result) {
                return [
                    'position' => $result->position,
                    'status' => $result->status,
                    'driver' => $result->participant->driver->name,
                    'car' => $result->participant->car->name,
                    'total_time' => $result->total_time . 's',
                    'fastest_lap' => $result->fastest_lap . 's',
                    'gap' => null,
                ];
            })
            ->values();

        // Calculate gap to leader
        if ($results->isNotEmpty()) {
            $leaderTime = (float) str_replace('s', '', $results[0]['total_time']);
            $results = $results->map(function ($r) use ($leaderTime) {
                $time = (float) str_replace('s', '', $r['total_time']);
                $r['gap'] = $r['position'] === 1
                    ? 'Leader'
                    : '+' . round($time - $leaderTime, 3) . 's';
                return $r;
            });
        }

        return response()->json([
            'race' => $race->name,
            'results' => $results,
        ]);
    }
}
