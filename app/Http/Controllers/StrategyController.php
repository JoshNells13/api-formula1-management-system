<?php

namespace App\Http\Controllers;

use App\Models\Strategy;
use App\Models\RaceParticipant;
use Illuminate\Http\Request;

class StrategyController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'race_participant_id' => 'required|exists:race_participants,id',
            'pit_stop_lap' => 'required|integer|min:1',
            'tyre_type' => 'required|in:soft,medium,hard',
            'fuel_load' => 'required|integer|min:1|max:100',
        ]);

        // One strategy per participant
        if (Strategy::where('race_participant_id', $validated['race_participant_id'])->exists()) {
            return response()->json([
                'message' => 'Strategy already exists for this participant. Use PUT to update.',
            ], 422);
        }

        $strategy = Strategy::create($validated);

        return response()->json([
            'message' => 'Strategy created successfully.',
            'strategy' => $strategy,
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $strategy = Strategy::findOrFail($id);

        $validated = $request->validate([
            'pit_stop_lap' => 'sometimes|integer|min:1',
            'tyre_type' => 'sometimes|in:soft,medium,hard',
            'fuel_load' => 'sometimes|integer|min:1|max:100',
        ]);

        $strategy->update($validated);

        return response()->json([
            'message' => 'Strategy updated successfully.',
            'strategy' => $strategy,
        ]);
    }
}
