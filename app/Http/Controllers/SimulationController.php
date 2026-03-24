<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Services\RaceSimulationService;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    public function __construct(
        private readonly RaceSimulationService $simulationService
    ) {
    }

    public function simulate(Request $request, $raceId)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'engineer'])) {
            return response()->json(['message' => 'Unauthorized. Only admin or engineer can simulate.'], 403);
        }

        $race = Race::with(['participants.driver', 'participants.car', 'participants.strategy'])
            ->findOrFail($raceId);

        $results = $this->simulationService->simulate($race);

        if (isset($results['error'])) {
            return response()->json(['message' => $results['error']], 422);
        }

        return response()->json([
            'message' => "Race '{$race->name}' simulated successfully.",
            'results' => $results,
        ]);
    }
}
