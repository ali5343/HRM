<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('attendance');
    }

    public function clockIn(Request $request)
    {
        $employee = $request->user();
        $employee->attendance()->create([
            'clock_in' => now(),
        ]);

        return redirect()->back();
    }

    public function clockOut(Request $request)
    {
        $employee = $request->user();
        $attendance = $employee->attendance()->latest()->first();
        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->back();
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if the user has already clocked in and hasn't clocked out yet
        $existingAttendance = DB::table('attendance')
            ->where('id', $user->id)
            ->whereNull('clock_out')  // Look for a record without a clock_out
            ->first();

        if ($existingAttendance) {
            // Clock Out
            $clockOutTime = Carbon::now();
            $clockInTime = Carbon::parse($existingAttendance->clock_in);

            // Calculate total hours worked (can be in hours or minutes, depending on your needs)
            $totalHours = $clockInTime->diffInMinutes($clockOutTime); // or use diffInMinutes() for more precision

            // Update the existing attendance record with clock_out time and total hours
            DB::table('attendance')
                ->where('id', $existingAttendance->id)
                ->update([
                    'clock_out' => $clockOutTime,
                    'total_hours' => $totalHours,
                    'updated_at' => Carbon::now(),
                ]);

            return redirect()->back()->with('success', 'Clocked out successfully.');
        } else {
            // Clock In
            $clockInTime = Carbon::now();

            // Insert a new record for clocking in
            DB::table('attendance')->insert([
                'id' => $user->id,       // Storing user ID
                'clock_in' => $clockInTime,   // Storing the current time for clock-in
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Clocked in successfully.');
        }
    }
}
