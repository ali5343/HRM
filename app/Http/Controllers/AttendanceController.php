<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Fetch attendance records for the user
        $attendances = DB::table('attendance')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc') // Optional: order by the most recent first
            ->get();
        
        // Pass the attendance records to the view
        return view('daily', [
            'attendances' => $attendances,
            'isClockedIn' => $attendances->whereNull('clock_out')->isNotEmpty(),
        ]);
    }
    public function view()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Fetch attendance records for the user
        $attendances = DB::table('attendance')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc') // Optional: order by the most recent first
            ->get();

        // Pass the attendance records to the view
        return view('dashboard', [
            'attendances' => $attendances,
            'isClockedIn' => $attendances->whereNull('clock_out')->isNotEmpty(),
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
        $existingAttendance = DB::table('attendance')
            ->where('user_id', $user->id)
            ->whereNull('clock_out')  // Look for a record without a clock_out
            ->first();

        if ($existingAttendance) {
            // Clock Out
            $clockOutTime = Carbon::now();
            $clockInTime = Carbon::parse($existingAttendance->clock_in);

            // Calculate total hours worked
            $totalHours = $clockInTime->diffInHours($clockOutTime); 

            // Update the existing attendance record
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
                'user_id' => $user->id,       // Storing user ID
                'clock_in' => $clockInTime,   // Storing the current time for clock-in
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Clocked in successfully.');
        }
    }
}
