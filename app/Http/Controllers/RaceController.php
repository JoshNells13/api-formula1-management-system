<?php

namespace App\Http\Controllers;

use App\Models\Race;
use Illuminate\Http\Request;

class RaceController extends Controller
{
    public function index()
    {
        $races = Race::withCount('participants')->latest()->get();

        return response()->json($races);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'location'   => 'required|string|max:255',
            'total_laps' => 'required|integer|min:1',
            'weather'    => 'required|in:sunny,cloudy,rain',
            'race_date'  => 'required|date',
        ]);

        $race = Race::create($validated);

        return response()->json([
            'message' => 'Race created successfully.',
            'race'    => $race,
        ], 201);
    }

    public function show($id)
    {
        $race = Race::with([
            'participants.driver',
            'participants.car',
            'participants.strategy',
            'participants.result.lapTimes',
        ])->findOrFail($id);

        return response()->json($race);
    }
}
