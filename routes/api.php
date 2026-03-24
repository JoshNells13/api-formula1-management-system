<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\LaptimeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\RaceParticipantController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\StrategyController;
use App\Http\Controllers\TeamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // TEAMS (full CRUD + nested drivers/cars)
    Route::apiResource('/teams', TeamController::class);
    Route::get('/teams/{teamId}/drivers', [DriverController::class, 'byTeam']);
    Route::get('/teams/{teamId}/cars', [CarController::class, 'byTeam']);

    // DRIVERS & CARS (supports optional ?team_id= filter)
    Route::apiResource('/drivers', DriverController::class);
    Route::apiResource('/cars', CarController::class);

    // RACES
    Route::get('/races', [RaceController::class, 'index']);
    Route::post('/races', [RaceController::class, 'store']);
    Route::get('/races/{id}', [RaceController::class, 'show']);

    // Race Participants
    Route::post('/participants', [RaceParticipantController::class, 'store']);
    Route::get('/participants/races/{racesId}', [RaceParticipantController::class, 'getByRace']);

    // Strategies
    Route::post('/strategies', [StrategyController::class, 'store']);
    Route::put('/strategies/{id}', [StrategyController::class, 'update']);

    // Simulate
    Route::post('/simulate/{raceId}', [SimulationController::class, 'simulate']);

    // Results
    Route::get('/results', [ResultController::class, 'index']);
    Route::get('/results/race/{raceId}', [ResultController::class, 'getByRace']);

    // Lap Times
    Route::get('/lap-times/result/{resultId}', [LaptimeController::class, 'getByResult']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'read']);
});

