<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class WeekendController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if today is a weekend (Saturday or Sunday)
        $today = Carbon::now();
        $isWeekend = $today->isWeekend();

        if (!$isWeekend) {
            return redirect()->back()->with('error', 'Weekend attendance can only be recorded on weekends.');
        }

        // Check if the user has already clocked in and hasn't clocked out for weekend work
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->where('is_weekend', true)  // Check if it's a weekend record
            ->first();

        if ($existingAttendance) {
            // Clock Out for weekend work
            $clockOutTime = Carbon::now();
            $clockInTime = Carbon::parse($existingAttendance->clock_in);

            // Calculate total weekend hours worked
            $totalMinutes = $clockInTime->diffInMinutes($clockOutTime);

            // Update the existing weekend attendance record
            $existingAttendance->update([
                'clock_out' => $clockOutTime,
                'total_hours' => $totalMinutes,
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Weekend Clocked out successfully.');
        } else {
            // Clock In for weekend work
            $clockInTime = Carbon::now();

            // Insert a new record for weekend clock-in
            Attendance::create([
                'user_id' => $user->id,
                'clock_in' => $clockInTime,
                'is_weekend' => true,  // Mark as weekend attendance
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Weekend Clocked in successfully.');
        }
    }
}
