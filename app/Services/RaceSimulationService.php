<?php

namespace App\Services;

use App\Models\Race;
use App\Models\RaceParticipant;
use App\Models\Result;
use App\Models\LapTime;
use App\Models\Notification;

class RaceSimulationService
{
    /**
     * Base lap time in seconds (e.g. 90.0s ~ Monza pace)
     */
    private const BASE_LAP_TIME = 90.0;

    /**
     * Pit stop time penalty in seconds
     */
    private const PIT_STOP_PENALTY = 25.0;

    /**
     * Initial tyre condition (100 = fresh)
     */
    private const INITIAL_TYRE_CONDITION = 100.0;

    /**
     * Simulate a full race for all participants.
     */
    public function simulate(Race $race): array
    {
        // Delete any previous simulation results for this race
        $participantIds = $race->participants()->pluck('id');
        Result::whereIn('race_participant_id', $participantIds)->delete();

        $participants = $race->participants()->with(['driver', 'car', 'strategy'])->get();

        if ($participants->isEmpty()) {
            return ['error' => 'No participants found for this race.'];
        }

        $simulatedResults = [];

        foreach ($participants as $participant) {
            $result = $this->simulateParticipant($race, $participant);
            $simulatedResults[] = [
                'participant_id' => $participant->id,
                'driver'         => $participant->driver->name,
                'result'         => $result,
            ];
        }

        // Rank finished drivers by total_time, push DNF to bottom
        $this->assignPositions($race);

        // Notify via notifications table
        $this->createRaceNotification($race);

        return $simulatedResults;
    }

    /**
     * Simulate a single participant's race.
     */
    private function simulateParticipant(Race $race, RaceParticipant $participant): Result
    {
        $driver   = $participant->driver;
        $car      = $participant->car;
        $strategy = $participant->strategy;

        $tyreType     = $strategy?->tyre_type ?? 'medium';
        $pitStopLap   = $strategy?->pit_stop_lap ?? 0;
        $fuelLoad     = $strategy?->fuel_load ?? 50;
        $totalLaps    = $race->total_laps;
        $weather      = $race->weather;

        $lapTimesData         = [];
        $totalTime            = 0.0;
        $fastestLap           = PHP_FLOAT_MAX;
        $tyreCondition        = self::INITIAL_TYRE_CONDITION;
        $fuelRemaining        = (float) $fuelLoad;
        $fuelConsumptionPerLap = $fuelLoad / $totalLaps;
        $status               = 'finished';
        $dnfOccurred          = false;

        for ($lap = 1; $lap <= $totalLaps; $lap++) {
            // DNF reliability check each lap
            if ($this->checkDnf($car, $driver)) {
                $status      = 'dnf';
                $dnfOccurred = true;
                break;
            }

            // Apply pit stop: reset tyre, add time penalty
            $pitPenalty = 0.0;
            if ($pitStopLap > 0 && $lap === $pitStopLap) {
                $pitPenalty    = self::PIT_STOP_PENALTY;
                $tyreCondition = self::INITIAL_TYRE_CONDITION; // Fresh tyres after stop
            }

            $lapTime = $this->calculateLapTime(
                driver: $driver,
                car: $car,
                weather: $weather,
                tyreType: $tyreType,
                tyreCondition: $tyreCondition,
                fuelLoad: $fuelLoad,
                fuelRemaining: $fuelRemaining,
                pitPenalty: $pitPenalty,
            );

            // Tyre degradation per lap
            $tyreCondition -= $this->tyreDegradationRate($tyreType, $driver->tyre_management);
            $tyreCondition  = max(0, $tyreCondition);

            // Fuel consumption
            $fuelRemaining -= $fuelConsumptionPerLap;
            $fuelRemaining  = max(0, $fuelRemaining);

            $totalTime += $lapTime;

            if ($lapTime < $fastestLap) {
                $fastestLap = $lapTime;
            }

            $lapTimesData[] = [
                'lap_number'     => $lap,
                'lap_time'       => round($lapTime, 3),
                'tyre_condition' => round($tyreCondition, 2),
                'fuel_remaining' => round($fuelRemaining, 2),
            ];
        }

        // Create Result record (position assigned later)
        $result = Result::create([
            'race_participant_id' => $participant->id,
            'total_time'          => round($totalTime, 3),
            'fastest_lap'         => $dnfOccurred ? 0.0 : round($fastestLap, 3),
            'position'            => 0, // placeholder, assigned after all
            'status'              => $status,
        ]);

        // Bulk insert lap times
        $lapTimeRecords = collect($lapTimesData)->map(fn($l) => array_merge($l, [
            'result_id'  => $result->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]))->all();

        LapTime::insert($lapTimeRecords);

        return $result;
    }

    /**
     * Calculate a single lap time in seconds.
     */
    private function calculateLapTime(
        $driver,
        $car,
        string $weather,
        string $tyreType,
        float $tyreCondition,
        float $fuelLoad,
        float $fuelRemaining,
        float $pitPenalty
    ): float {
        $base = self::BASE_LAP_TIME;

        // --- Driver modifier (range: -3s to 0s) ---
        // Higher speed/consistency = faster
        $driverBonus = (($driver->speed + $driver->consistency) / 200) * 3.0;
        $base -= $driverBonus;

        // --- Car performance modifier (range: -3s to 0s) ---
        $carBonus = (($car->top_speed + $car->acceleration + $car->downforce) / 300) * 3.0;
        $base -= $carBonus;

        // --- Tyre type initial pace modifier ---
        $tyrePaceOffset = match ($tyreType) {
            'soft'   => -0.8,
            'medium' =>  0.0,
            'hard'   =>  0.5,
        };
        $base += $tyrePaceOffset;

        // --- Tyre degradation effect (0-100 condition: fully degraded adds ~2s) ---
        $degradationPenalty = ((100 - $tyreCondition) / 100) * 2.0;
        $base += $degradationPenalty;

        // --- Weather modifier ---
        $weatherPenalty = match ($weather) {
            'sunny'  =>  0.0,
            'cloudy' =>  0.3,
            'rain'   =>  3.5,
        };
        $base += $weatherPenalty;

        // --- Fuel weight modifier (heavy fuel = slower) ---
        // At full load (e.g. 100kg) it adds ~0.3s/lap, reduces as fuel burns
        $fuelPenalty = ($fuelRemaining / $fuelLoad) * 0.3;
        $base += $fuelPenalty;

        // --- Random variance controlled by driver consistency ---
        // High consistency = small variance
        $variance = (100 - $driver->consistency) / 100 * 0.8;
        $random   = (lcg_value() * 2 - 1) * $variance; // between -$variance and +$variance
        $base += $random;

        // --- Pit stop penalty ---
        $base += $pitPenalty;

        return max(60.0, $base); // never below 60s
    }

    /**
     * Determine tyre degradation per lap.
     */
    private function tyreDegradationRate(string $tyreType, int $tyreManagement): float
    {
        $baseRate = match ($tyreType) {
            'soft'   => 4.5,
            'medium' => 2.5,
            'hard'   => 1.2,
        };

        // Better tyre management reduces degradation by up to 40%
        $managementFactor = 1.0 - ($tyreManagement / 100) * 0.4;

        return $baseRate * $managementFactor;
    }

    /**
     * Random DNF check based on car reliability and driver consistency.
     * Returns true if DNF happens on this lap.
     */
    private function checkDnf($car, $driver): bool
    {
        // Combined reliability 0-200 → chance per lap
        $combined        = $car->reliability + $driver->consistency;
        $dnfProbability  = max(0.0002, (200 - $combined) / 200 * 0.005);

        return lcg_value() < $dnfProbability;
    }

    /**
     * Sort finished cars by total_time and assign positions.
     * DNF cars are ranked last.
     */
    private function assignPositions(Race $race): void
    {
        $participantIds = $race->participants()->pluck('id');

        $finishedIds = Result::whereIn('race_participant_id', $participantIds)
            ->where('status', 'finished')
            ->orderBy('total_time')
            ->pluck('id');

        $dnfIds = Result::whereIn('race_participant_id', $participantIds)
            ->where('status', 'dnf')
            ->pluck('id');

        $position = 1;
        foreach ($finishedIds as $id) {
            Result::where('id', $id)->update(['position' => $position++]);
        }
        foreach ($dnfIds as $id) {
            Result::where('id', $id)->update(['position' => $position++]);
        }
    }

    /**
     * Create a notification after a race simulation.
     */
    private function createRaceNotification(Race $race): void
    {
        Notification::create([
            'title'   => "Race Simulation Completed: {$race->name}",
            'message' => "The race simulation for '{$race->name}' at {$race->location} has been completed successfully.",
            'is_read' => false,
        ]);
    }
}
