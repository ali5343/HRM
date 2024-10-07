<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
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
}
