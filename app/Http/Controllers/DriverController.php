<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::withCount('participants')->get();

        return response()->json($drivers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'number'          => 'required|integer|unique:drivers,number',
            'nationality'     => 'required|string|max:100',
            'speed'           => 'required|integer|min:1|max:100',
            'consistency'     => 'required|integer|min:1|max:100',
            'tyre_management' => 'required|integer|min:1|max:100',
            'status'          => 'sometimes|in:active,inactive',
        ]);

        $driver = Driver::create($validated);

        return response()->json([
            'message' => 'Driver created successfully.',
            'driver'  => $driver,
        ], 201);
    }

    public function show(Driver $driver)
    {
        $driver->load('participants.race', 'participants.car', 'participants.result');

        return response()->json($driver);
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'number'          => 'sometimes|integer|unique:drivers,number,' . $driver->id,
            'nationality'     => 'sometimes|string|max:100',
            'speed'           => 'sometimes|integer|min:1|max:100',
            'consistency'     => 'sometimes|integer|min:1|max:100',
            'tyre_management' => 'sometimes|integer|min:1|max:100',
            'status'          => 'sometimes|in:active,inactive',
        ]);

        $driver->update($validated);

        return response()->json([
            'message' => 'Driver updated successfully.',
            'driver'  => $driver,
        ]);
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return response()->json(['message' => 'Driver deleted successfully.']);
    }
}
