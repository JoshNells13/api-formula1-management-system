<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceParticipant;
use Illuminate\Http\Request;

class RaceParticipantController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'race_id' => 'required|exists:races,id',
            'driver_id' => 'required|exists:drivers,id',
            'car_id' => 'required|exists:cars,id',
            'grid_position' => 'required|integer|min:1',
        ]);

        // Prevent duplicate entry for same driver in same race
        $exists = RaceParticipant::where('race_id', $validated['race_id'])
            ->where('driver_id', $validated['driver_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This driver is already assigned to this race.',
            ], 422);
        }

        $participant = RaceParticipant::create($validated);
        $participant->load('driver', 'car', 'race');

        return response()->json([
            'message' => 'Participant added to race.',
            'participant' => $participant,
        ], 201);
    }


    public function getByRace($racesId)
    {
        $race = Race::findOrFail($racesId);

        $participants = RaceParticipant::with(['driver', 'car', 'strategy', 'result'])
            ->where('race_id', $racesId)
            ->get();

        return response()->json([
            'race' => $race,
            'participants' => $participants,
        ]);
    }
}
