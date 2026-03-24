# 🏎️ F1 Multi-Team Management System

> A production-ready Laravel backend that simulates internal race management for **all F1 teams** — featuring team management, driver registration per team, car configuration, race creation, strategy setup, and a full lap-by-lap race simulation engine.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
- [Race Simulation Engine](#race-simulation-engine)
- [Default Seeded Data](#default-seeded-data)
- [Roles & Permissions](#roles--permissions)
- [Project Structure](#project-structure)

---

## Overview

The F1 Multi-Team Management System is an **API-only Laravel backend** that simulates the internal operations of a Formula 1 Championship. It goes beyond Ferrari-only by supporting **any team** (Ferrari, Mercedes, Red Bull, etc.), where each team owns its own drivers and cars.

The system includes a full **race simulation engine** that calculates realistic lap times based on:

- Driver skill ratings (speed, consistency, tyre management)
- Car performance stats (top speed, acceleration, downforce, reliability)
- Tyre compound selection and degradation over race distance
- Pit stop timing and fresh tyre reset
- Weather conditions (sunny, cloudy, rain)
- Fuel load weight effects per lap
- Random performance variance (controlled by driver consistency)
- DNF probability based on car reliability

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.2+ |
| Database | MySQL |
| Authentication | Laravel Sanctum (token-based) |
| Architecture | Service-based (thin controllers, fat service) |

---

## Architecture

```
HTTP Request → Controller (validate + delegate)
                    ↓
              Service Layer (business logic)
             [RaceSimulationService]
                    ↓
              Eloquent Models → MySQL
```

Controllers are intentionally minimal — all heavy logic lives inside `app/Services/RaceSimulationService.php`.

---

## Database Schema

### `teams`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| name | string | unique (e.g. Scuderia Ferrari) |
| country | string | |
| principal | string | Team Principal name |
| base | string | HQ location |
| status | enum | `active`, `inactive` |

### `users`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| name | string | |
| email | string | unique |
| password | string | bcrypt |
| role | enum | `admin`, `engineer`, `viewer` |

### `drivers`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| team_id | FK | → teams |
| name | string | |
| number | integer | car number (unique) |
| nationality | string | |
| speed | integer | 1–100 |
| consistency | integer | 1–100, also controls lap variance |
| tyre_management | integer | 1–100, controls tyre deg rate |
| status | enum | `active`, `inactive` |

### `cars`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| team_id | FK | → teams |
| name | string | e.g. Ferrari SF-24 |
| chassis_code | string | unique |
| top_speed | integer | 1–100 |
| acceleration | integer | 1–100 |
| downforce | integer | 1–100 |
| reliability | integer | 1–100, affects DNF chance |

### `races`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| name | string | e.g. Italian Grand Prix |
| location | string | e.g. Monza |
| total_laps | integer | |
| weather | enum | `sunny`, `cloudy`, `rain` |
| race_date | date | |

### `race_participants`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| race_id | FK | → races |
| driver_id | FK | → drivers |
| car_id | FK | → cars |
| grid_position | integer | starting position |

### `strategies`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| race_participant_id | FK | → race_participants (unique) |
| pit_stop_lap | integer | lap number to pit |
| tyre_type | enum | `soft`, `medium`, `hard` |
| fuel_load | integer | 1–100 |

### `results`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| race_participant_id | FK | → race_participants |
| total_time | float | seconds |
| fastest_lap | float | seconds |
| position | integer | finishing position |
| status | enum | `finished`, `dnf` |

### `lap_times`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| result_id | FK | → results |
| lap_number | integer | |
| lap_time | float | seconds |
| tyre_condition | float | 0–100, decreases per lap |
| fuel_remaining | float | kg remaining |

### `notifications`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| user_id | FK (nullable) | → users |
| title | string | |
| message | text | |
| is_read | boolean | default false |

### Entity Relationships

```
Team ──< Driver
Team ──< Car
Race ──< RaceParticipant >── Driver (from any Team)
               │            └── Car   (from any Team)
               ├── Strategy (1:1)
               └── Result (1:1)
                     └──< LapTime
```

---

## Installation

### 1. Clone & Install Dependencies

```bash
git clone <repo-url>
cd f1-multi-team-management-system
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=f1_management
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Migrate & Seed

```bash
php artisan migrate:fresh --seed
```

This creates all tables and seeds the database with Ferrari, Mercedes, and Red Bull teams, their drivers, cars, races, and users.

### 4. Start the Server

```bash
php artisan serve
```

API is available at `http://localhost:8000/api`

---

## Authentication

This API uses **Laravel Sanctum** for token-based authentication.

### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@f1.com",
  "password": "password"
}
```

**Response:**
```json
{
  "message": "Login successful.",
  "user": { "id": 1, "name": "Race Admin", "role": "admin" },
  "token": "1|abc123..."
}
```

### Using the Token

Include the token in all subsequent requests:

```http
Authorization: Bearer 1|abc123...
```

### Logout

```http
POST /api/logout
Authorization: Bearer {token}
```

---

## API Endpoints

### 🔓 Public

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/login` | Get access token |

### 🔒 Protected (requires Sanctum token)

#### Auth
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/me` | Get authenticated user |
| POST | `/api/logout` | Revoke current token |

#### Teams
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/teams` | List all teams (with driver & car counts) |
| POST | `/api/teams` | Create a team |
| GET | `/api/teams/{id}` | Get team with its drivers & cars |
| PUT | `/api/teams/{id}` | Update a team |
| DELETE | `/api/teams/{id}` | Delete a team |
| GET | `/api/teams/{id}/drivers` | List all drivers of a team |
| GET | `/api/teams/{id}/cars` | List all cars of a team |

**Create Team Payload:**
```json
{
  "name": "Scuderia Ferrari",
  "country": "Italy",
  "principal": "Frédéric Vasseur",
  "base": "Maranello, Italy",
  "championships_won": 16
}
```

#### Drivers
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/drivers` | List all drivers (with team info) |
| GET | `/api/drivers?team_id=1` | Filter drivers by team |
| POST | `/api/drivers` | Create a driver |
| GET | `/api/drivers/{id}` | Get driver with race history |
| PUT | `/api/drivers/{id}` | Update a driver |
| DELETE | `/api/drivers/{id}` | Delete a driver |

**Create Driver Payload:**
```json
{
  "team_id": 1,
  "name": "Charles Leclerc",
  "number": 16,
  "nationality": "Monegasque",
  "speed": 95,
  "consistency": 87,
  "tyre_management": 80
}
```

#### Cars
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/cars` | List all cars (with team info) |
| GET | `/api/cars?team_id=1` | Filter cars by team |
| POST | `/api/cars` | Create a car |
| GET | `/api/cars/{id}` | Get car with participants |
| PUT | `/api/cars/{id}` | Update a car |
| DELETE | `/api/cars/{id}` | Delete a car |

**Create Car Payload:**
```json
{
  "team_id": 1,
  "name": "Ferrari SF-24",
  "chassis_code": "SF-24-01",
  "top_speed": 92,
  "acceleration": 90,
  "downforce": 88,
  "reliability": 85
}
```

#### Races
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/races` | List all races |
| POST | `/api/races` | Create a race |
| GET | `/api/races/{id}` | Get race with all participant details |

**Create Race Payload:**
```json
{
  "name": "Italian Grand Prix",
  "location": "Monza",
  "total_laps": 53,
  "weather": "sunny",
  "race_date": "2024-09-01"
}
```

#### Race Participants
| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/participants` | Assign driver + car to a race |
| GET | `/api/participants/races/{raceId}` | Get all participants in a race |

**Assign Participant Payload:**
```json
{
  "race_id": 1,
  "driver_id": 1,
  "car_id": 1,
  "grid_position": 1
}
```

> 💡 Drivers and cars from **different teams** can be mixed in the same race — just like real F1!

#### Strategies
| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/strategies` | Create a strategy for a participant |
| PUT | `/api/strategies/{id}` | Update an existing strategy |

**Strategy Payload:**
```json
{
  "race_participant_id": 1,
  "pit_stop_lap": 28,
  "tyre_type": "soft",
  "fuel_load": 75
}
```

#### Race Simulation ⚡
| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/simulate/{raceId}` | Run simulation for a race |

> Requires `admin` or `engineer` role. Simulates every lap for every participant, stores results and lap telemetry.

**Response:**
```json
{
  "message": "Race 'Italian Grand Prix' simulated successfully.",
  "results": [
    {
      "participant_id": 1,
      "driver": "Max Verstappen",
      "result": { "total_time": 4791.2, "fastest_lap": 83.1, "position": 1, "status": "finished" }
    }
  ]
}
```

#### Results & Analytics
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/results` | All results across all races |
| GET | `/api/results/race/{raceId}` | Race leaderboard with gap-to-leader |
| GET | `/api/lap-times/result/{resultId}` | Full lap telemetry for a result |

**Race Results Response:**
```json
{
  "race": "Italian Grand Prix",
  "results": [
    { "position": 1, "driver": "Max Verstappen", "total_time": "4791.2s", "gap": "Leader" },
    { "position": 2, "driver": "Charles Leclerc", "total_time": "4797.9s", "gap": "+6.7s" }
  ]
}
```

#### Notifications
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/notifications` | List all notifications |
| PUT | `/api/notifications/{id}/read` | Mark as read |

---

## Race Simulation Engine

The `RaceSimulationService` is the core of this system. Here is how each lap time is computed:

### Lap Time Formula

```
Base Time = 90.0s (Monza baseline)

− Driver Bonus    (up to −3.0s based on speed + consistency)
− Car Bonus       (up to −3.0s based on top_speed + acceleration + downforce)
± Tyre Offset     (soft: −0.8s | medium: 0s | hard: +0.5s)
+ Tyre Degradation (up to +2.0s as condition drops from 100 → 0)
+ Weather Penalty  (sunny: 0s | cloudy: +0.3s | rain: +3.5s)
+ Fuel Weight      (up to +0.3s when tank is full, reduces as fuel burns)
± Random Variance  (controlled by driver consistency — higher = tighter)
+ Pit Stop Penalty (25s on pit lap, tyres reset to 100)
```

### Tyre Degradation Rate

| Compound | Base Deg/Lap | With Max Tyre Management |
|---|---|---|
| Soft | 4.5% | 2.7% |
| Medium | 2.5% | 1.5% |
| Hard | 1.2% | 0.72% |

### DNF Probability

Each lap runs a reliability check:
```
DNF chance/lap = max(0.02%, (200 − car.reliability − driver.consistency) / 200 × 0.5%)
```

### Positions

After all laps are simulated:
1. `finished` drivers ranked by `total_time` ascending
2. `dnf` drivers ranked after all finishers

---

## Default Seeded Data

After running `php artisan migrate:fresh --seed`:

### Users
| Name | Email | Password | Role |
|---|---|---|---|
| Race Admin | admin@f1.com | password | admin |
| Race Engineer | engineer@f1.com | password | engineer |
| Viewer | viewer@f1.com | password | viewer |

### Teams
| # | Team | Country | Principal | Championships |
|---|---|---|---|---|
| 1 | Scuderia Ferrari | Italy | Frédéric Vasseur | 16 |
| 2 | Mercedes-AMG Petronas | United Kingdom | Toto Wolff | 8 |
| 3 | Oracle Red Bull Racing | Austria | Christian Horner | 6 |

### Drivers
| # | Name | Team | Speed | Consistency | Tyre Mgmt |
|---|---|---|---|---|---|
| 16 | Charles Leclerc | Ferrari | 95 | 87 | 80 |
| 55 | Carlos Sainz | Ferrari | 89 | 92 | 88 |
| 44 | Lewis Hamilton | Mercedes | 99 | 98 | 95 |
| 63 | George Russell | Mercedes | 91 | 89 | 85 |
| 1 | Max Verstappen | Red Bull | 99 | 97 | 94 |
| 11 | Sergio Perez | Red Bull | 86 | 84 | 87 |

### Cars
| Name | Chassis | Team | Top Speed | Accel | Downforce | Reliability |
|---|---|---|---|---|---|---|
| Ferrari SF-24 | SF-24-01 | Ferrari | 92 | 90 | 88 | 85 |
| Mercedes W15 | W15-01 | Mercedes | 91 | 89 | 90 | 93 |
| Red Bull RB20 | RB20-01 | Red Bull | 96 | 95 | 93 | 90 |

### Races
| Name | Location | Laps | Weather |
|---|---|---|---|
| Italian Grand Prix | Monza | 53 | sunny |
| Monaco Grand Prix | Monte Carlo | 78 | cloudy |
| British Grand Prix | Silverstone | 52 | rain |

---

## Roles & Permissions

| Action | Admin | Engineer | Viewer |
|---|---|---|---|
| Login / Logout | ✅ | ✅ | ✅ |
| View teams, drivers, cars, races | ✅ | ✅ | ✅ |
| Create / Update / Delete teams | ✅ | ✅ | ❌ |
| Create / Update / Delete drivers & cars | ✅ | ✅ | ❌ |
| Create races & participants | ✅ | ✅ | ❌ |
| Set strategies | ✅ | ✅ | ❌ |
| **Run race simulation** | ✅ | ✅ | ❌ |
| View results & lap times | ✅ | ✅ | ✅ |

---

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── AuthController.php          # login, logout, me
│       ├── TeamController.php          # full CRUD + nested drivers/cars
│       ├── DriverController.php        # full CRUD + byTeam()
│       ├── CarController.php           # full CRUD + byTeam()
│       ├── RaceController.php          # index, store, show
│       ├── RaceParticipantController.php # store, getByRace
│       ├── StrategyController.php      # store, update
│       ├── SimulationController.php    # delegates to service
│       ├── ResultController.php        # index, getByRace
│       ├── LaptimeController.php       # getByResult
│       └── NotificationController.php  # index, read
├── Models/
│   ├── User.php
│   ├── Team.php                        # ← NEW
│   ├── Driver.php                      # team_id FK added
│   ├── Car.php                         # team_id FK added
│   ├── Race.php
│   ├── RaceParticipant.php
│   ├── Strategy.php
│   ├── Result.php
│   ├── LapTime.php
│   └── Notification.php
└── Services/
    └── RaceSimulationService.php      # 🏎️ Core simulation engine
routes/
└── api.php                            # All API routes incl. /teams
```

---

## Quick Start Flow

```
1. POST /api/login                      → get token
2. GET  /api/teams                      → see all 3 seeded teams
3. GET  /api/teams/1/drivers            → see Ferrari's drivers
4. POST /api/races                      → create a race
5. POST /api/participants               → assign Verstappen + RB20 to race
6. POST /api/strategies                 → set tyre=soft, pit_stop_lap=25
7. POST /api/simulate/{raceId}          → 🏁 run simulation
8. GET  /api/results/race/{raceId}      → see leaderboard with gap-to-leader
9. GET  /api/lap-times/result/{id}      → see full lap telemetry
```

---

*Built to simulate real F1 race operations for all constructor teams. Not affiliated with Formula 1, Ferrari, Mercedes, or Red Bull.*
