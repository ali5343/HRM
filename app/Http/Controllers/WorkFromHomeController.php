<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkFromHomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Check if the user has already clocked in and hasn't clocked out yet
        $existingAttendance = DB::table('wfh')
            ->where('user_id', $user->id)
            ->whereNull('clock_out') // Look for a record without a clock_out
            ->first();

        // Pass the attendance status to the view
        return view('wfh', [
            'isClockedIn' => $existingAttendance ? true : false,
        ]);
    }

    public function clockIn(Request $request)
    {
        $employee = $request->user();
        $employee->attendance()->create([
            'clock_in' => now(),
        ]);

        return redirect()->back()->with('success', 'Clocked in successfully.');
    }

    public function clockOut(Request $request)
    {
        $employee = $request->user();
        $attendance = $employee->attendance()->latest()->first();
        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->back()->with('success', 'Clocked out successfully.');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if the user has already clocked in and hasn't clocked out yet
        $existingAttendance = DB::table('wfh')
            ->where('user_id', $user->id)
            ->whereNull('clock_out')  // Look for a record without a clock_out
            ->first();

        if ($existingAttendance) {
            // Clock Out
            $clockOutTime = Carbon::now();
            $clockInTime = Carbon::parse($existingAttendance->clock_in);

            // Calculate total hours worked
            $totalHours = $clockInTime->diffInMinutes($clockOutTime);

            // Update the existing attendance record
            DB::table('wfh')
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
            DB::table('wfh')->insert([
                'user_id' => $user->id,       // Storing user ID
                'clock_in' => $clockInTime,   // Storing the current time for clock-in
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Clocked in successfully.');
        }
    }
}
