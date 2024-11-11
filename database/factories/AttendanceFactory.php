<?php
namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        // Define a random clock_in time
        $clockIn = $this->faker->dateTimeBetween('-1 month', 'now');

        // Add random hours (1 to 8) to clock_in to get a valid clock_out
        $clockOut = (clone $clockIn)->modify('+' . $this->faker->numberBetween(1, 8) . ' hours');

        // Calculate total hours based on clock_in and clock_out
        $totalHours = Carbon::parse($clockIn)->diff($clockOut)->format('%H:%I:%S');

        return [
            'user_id' => 3, // Creates a new user or you can provide existing user IDs
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_hours' => $totalHours,
            'is_leave' => $this->faker->boolean(20), // 20% chance of being a leave day
        ];
    }
}

