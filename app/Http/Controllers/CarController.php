<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index()
    {
        return response()->json(Car::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'chassis_code' => 'required|string|max:50|unique:cars,chassis_code',
            'top_speed'    => 'required|integer|min:1|max:100',
            'acceleration' => 'required|integer|min:1|max:100',
            'downforce'    => 'required|integer|min:1|max:100',
            'reliability'  => 'required|integer|min:1|max:100',
        ]);

        $car = Car::create($validated);

        return response()->json([
            'message' => 'Car created successfully.',
            'car'     => $car,
        ], 201);
    }

    public function show(Car $car)
    {
        $car->load('participants.driver', 'participants.race');

        return response()->json($car);
    }

    public function update(Request $request, Car $car)
    {
        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'chassis_code' => 'sometimes|string|max:50|unique:cars,chassis_code,' . $car->id,
            'top_speed'    => 'sometimes|integer|min:1|max:100',
            'acceleration' => 'sometimes|integer|min:1|max:100',
            'downforce'    => 'sometimes|integer|min:1|max:100',
            'reliability'  => 'sometimes|integer|min:1|max:100',
        ]);

        $car->update($validated);

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
}
