<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Team;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $query = Car::with('team');

        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id'      => 'required|exists:teams,id',
            'name'         => 'required|string|max:255',
            'chassis_code' => 'required|string|max:50|unique:cars,chassis_code',
            'top_speed'    => 'required|integer|min:1|max:100',
            'acceleration' => 'required|integer|min:1|max:100',
            'downforce'    => 'required|integer|min:1|max:100',
            'reliability'  => 'required|integer|min:1|max:100',
        ]);

        $car = Car::create($validated);
        $car->load('team');

        return response()->json([
            'message' => 'Car created successfully.',
            'car'     => $car,
        ], 201);
    }

    public function show(Car $car)
    {
        $car->load('team', 'participants.driver', 'participants.race');

        return response()->json($car);
    }

    public function update(Request $request, Car $car)
    {
        $validated = $request->validate([
            'team_id'      => 'sometimes|exists:teams,id',
            'name'         => 'sometimes|string|max:255',
            'chassis_code' => 'sometimes|string|max:50|unique:cars,chassis_code,' . $car->id,
            'top_speed'    => 'sometimes|integer|min:1|max:100',
            'acceleration' => 'sometimes|integer|min:1|max:100',
            'downforce'    => 'sometimes|integer|min:1|max:100',
            'reliability'  => 'sometimes|integer|min:1|max:100',
        ]);

        $car->update($validated);
        $car->load('team');

        return response()->json([
            'message' => 'Car updated successfully.',
            'car'     => $car,
        ]);
    }

    public function destroy(Car $car)
    {
        $car->delete();

        return response()->json(['message' => 'Car deleted successfully.']);
    }

    public function byTeam(int $teamId)
    {
        $team = Team::findOrFail($teamId);
        $cars = $team->cars()->get();

        return response()->json([
            'team' => $team->name,
            'cars' => $cars,
        ]);
    }
}

