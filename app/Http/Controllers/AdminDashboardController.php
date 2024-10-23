<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
class AdminDashboardController extends Controller
{
    public function view()
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            // Fetch all users
            $allUsers = User::all();
            // Get the start and end of the current week, month, and today
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $startOfToday = Carbon::now()->startOfDay();
            $endOfToday = Carbon::now()->endOfDay();
            // Fetch total weekly, monthly, and today attendance hours for each user
            $userAttendance = Attendance::query()
                ->select('user_id',
                    DB::raw('SUM(CASE WHEN created_at BETWEEN "' . $startOfToday . '" AND "' . $endOfToday . '" THEN total_hours ELSE 0 END) as today_hours'),
                    DB::raw('SUM(CASE WHEN created_at BETWEEN "' . $startOfWeek . '" AND "' . $endOfWeek . '" THEN total_hours ELSE 0 END) as weekly_hours'),
                    DB::raw('SUM(CASE WHEN created_at BETWEEN "' . $startOfMonth . '" AND "' . $endOfMonth . '" THEN total_hours ELSE 0 END) as monthly_hours')
                )
                ->groupBy('user_id')
                ->get();
            // Define total expected hours per week and month
            $totalHoursPerWeek = 36;
            $totalHoursPerMonth = 144;
            // Map attendance data to user data
            $usersWithAttendance = $allUsers->map(function($user) use ($userAttendance, $totalHoursPerWeek, $totalHoursPerMonth) {
                $attendance = $userAttendance->firstWhere('user_id', $user->id);
                $user->today_hours = $attendance ? $attendance->today_hours : 0;
                $user->weekly_hours = $attendance ? $attendance->weekly_hours : 0;
                $user->monthly_hours = $attendance ? $attendance->monthly_hours : 0;
                $user->remaining_weekly_hours = $totalHoursPerWeek - $user->weekly_hours;
                $user->remaining_monthly_hours = $totalHoursPerMonth - $user->monthly_hours;
                return $user;
            });
            // Fetch admin-specific attendance data
            $adminAttendance = $usersWithAttendance->firstWhere('id', $user->id);
            // Pass the relevant data to the admin view
            return view('admin.index', compact('usersWithAttendance', 'adminAttendance'));
        }
        // If not admin, redirect or show a different view (as needed)
        return redirect()->route('dashboard');
    }
}