<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::withCount(['drivers', 'cars'])->get();

        return response()->json($teams);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
            'country' => 'required|string|max:100',
            'principal' => 'required|string|max:255',
            'base' => 'required|string|max:255',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $team = Team::create($validated);

        return response()->json([
            'message' => 'Team created successfully.',
            'team' => $team,
        ], 201);
    }

    public function show(Team $team)
    {
        $team->load('drivers', 'cars');

        return response()->json($team);
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:teams,name,' . $team->id,
            'country' => 'sometimes|string|max:100',
            'principal' => 'sometimes|string|max:255',
            'base' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $team->update($validated);

        return response()->json([
            'message' => 'Team updated successfully.',
            'team' => $team,
        ]);
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return response()->json(['message' => 'Team deleted successfully.']);
    }
}
