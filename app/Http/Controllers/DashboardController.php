<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function view()
    {
        $user = Auth::user();

        // Fetch total expected hours dynamically (can be stored in config or DB)
        $totalHoursPerWeek = config('attendance.hours_per_week', 36);  // Default to 36 hours
        $totalHoursPerMonth = config('attendance.hours_per_month', 144); // Default to 144 hours

        $startOfToday = Carbon::now()->startOfDay();
        $endOfToday = Carbon::now()->endOfDay();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Admin-specific dashboard
        if ($user->hasRole('admin')) {
            // Fetch paginated users for better performance with large datasets
            $allUsers = User::paginate(5); // Adjust the pagination limit as needed

            // Get attendance and leave information for each user
            $userAttendance = $this->fetchUserAttendance($startOfToday, $endOfToday, $startOfWeek, $endOfWeek, $startOfMonth, $endOfMonth);
            $leaveDaysThisWeek = $this->fetchLeaveHours($startOfWeek, $endOfWeek);
            $leaveDaysThisMonth = $this->fetchLeaveHours($startOfMonth, $endOfMonth);

            // Map and calculate remaining hours for each user
            $usersWithAttendance = $allUsers->map(function ($user) use ($userAttendance, $leaveDaysThisWeek, $leaveDaysThisMonth, $totalHoursPerWeek, $totalHoursPerMonth) {
                return $this->mapUserAttendance($user, $userAttendance, $leaveDaysThisWeek, $leaveDaysThisMonth, $totalHoursPerWeek, $totalHoursPerMonth);
            });

            // Fetch the admin's attendance info
            $adminAttendance = $usersWithAttendance->firstWhere('id', $user->id);

            return view('admin.index', compact('usersWithAttendance', 'adminAttendance', 'totalHoursPerWeek', 'totalHoursPerMonth'));
        }

        // Non-admin user logic (regular user dashboard)
        $attendances = $this->fetchUserAttendances($user->id);
        $weekendAttendances = $this->fetchWeekendAttendances($user->id);

        // Calculate hours for the user
        $todayTotalHours = $this->calculateTotalHours($attendances, $startOfToday, $endOfToday);
        $weeklyTotalHours = $this->calculateTotalHours($attendances, $startOfWeek, $endOfWeek);
        $monthlyTotalHours = $this->calculateTotalHours($attendances, $startOfMonth, $endOfMonth);

        // Calculate leave days and remaining hours
        $leaveHoursThisWeek = $this->fetchLeaveHoursForUser($user->id, $startOfWeek, $endOfWeek);
        $leaveHoursThisMonth = $this->fetchLeaveHoursForUser($user->id, $startOfMonth, $endOfMonth);

        $leftHoursPerWeek = $totalHoursPerWeek - $weeklyTotalHours - $leaveHoursThisWeek;
        $leftHoursPerMonth = $totalHoursPerMonth - $monthlyTotalHours - $leaveHoursThisMonth;
/*dd($leaveHoursThisMonth,$leaveHoursThisWeek );*/

        return view('dashboard', [
            'attendances' => $attendances,
            'weekendAttendances' => $weekendAttendances,
            'todayTotalHours' => $todayTotalHours,
            'weeklyTotalHours' => $weeklyTotalHours,
            'monthlyTotalHours' => $monthlyTotalHours,
            'leftHoursPerWeek' => $leftHoursPerWeek,
            'leftHoursPerMonth' => $leftHoursPerMonth,
            'isClockedIn' => $attendances->whereNull('clock_out')->isNotEmpty(),
        ]);
    }

    private function fetchUserAttendance($startOfToday, $endOfToday, $startOfWeek, $endOfWeek, $startOfMonth, $endOfMonth)
    {
        return Attendance::select('user_id')
            ->selectRaw('SUM(CASE WHEN created_at BETWEEN ? AND ? THEN total_hours ELSE 0 END) as today_hours', [$startOfToday, $endOfToday])
            ->selectRaw('SUM(CASE WHEN created_at BETWEEN ? AND ? THEN total_hours ELSE 0 END) as weekly_hours', [$startOfWeek, $endOfWeek])
            ->selectRaw('SUM(CASE WHEN created_at BETWEEN ? AND ? THEN total_hours ELSE 0 END) as monthly_hours', [$startOfMonth, $endOfMonth])
            ->groupBy('user_id')
            ->get();
    }

    private function fetchLeaveHours($start, $end)
    {
        return Attendance::select('user_id', DB::raw('SUM(total_hours) as leave_hours'))
            ->where('is_leave', true)
            ->whereBetween('clock_in', [$start, $end])
            ->groupBy('user_id')
            ->get();
    }

    private function mapUserAttendance($user, $userAttendance, $leaveDaysThisWeek, $leaveDaysThisMonth, $totalHoursPerWeek, $totalHoursPerMonth)
    {
        $attendance = $userAttendance->firstWhere('user_id', $user->id);
        $user->today_hours = $attendance ? $attendance->today_hours : 0;
        $user->weekly_hours = $attendance ? $attendance->weekly_hours : 0;
        $user->monthly_hours = $attendance ? $attendance->monthly_hours : 0;

        // Fetch leave hours (instead of leave days)
        $weeklyLeaveHours = $leaveDaysThisWeek->firstWhere('user_id', $user->id)->leave_hours ?? 0;
        $monthlyLeaveHours = $leaveDaysThisMonth->firstWhere('user_id', $user->id)->leave_hours ?? 0;

        // Subtract leave hours
        $user->remaining_weekly_hours = $totalHoursPerWeek - $user->weekly_hours - $weeklyLeaveHours;
        $user->remaining_monthly_hours = $totalHoursPerMonth - $user->monthly_hours - $monthlyLeaveHours;

        return $user;
    }

    private function fetchUserAttendances($userId)
    {
        return Attendance::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function fetchWeekendAttendances($userId)
    {
        return DB::table('weekend')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function calculateTotalHours($attendances, $start, $end)
    {
        return $attendances->filter(function ($attendance) use ($start, $end) {
            return Carbon::parse($attendance->created_at)->between($start, $end) && !$attendance->is_leave;
        })->sum('total_hours');
    }

    private function fetchLeaveHoursForUser($userId, $start, $end)
    {
        return Attendance::where('user_id', $userId)
            ->where('is_leave', true)
            ->whereBetween('clock_in', [$start, $end])
            ->sum('total_hours'); // Fetch actual leave hours
    }

    public function admin_index()
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            // Fetch paginated users for better performance with large datasets
            $allUsers = User::paginate(10); // Adjust the pagination limit as needed

            $startOfToday = Carbon::now()->startOfDay();
            $endOfToday = Carbon::now()->endOfDay();
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            // Fetch attendance and leave hours for each user
            $userAttendance = $this->fetchUserAttendance($startOfToday, $endOfToday, $startOfWeek, $endOfWeek, $startOfMonth, $endOfMonth);
            $userLeaveHours = $this->fetchLeaveHours($startOfWeek, $endOfWeek);

            $totalHoursPerWeek = config('attendance.hours_per_week', 36); // Dynamic or default value
            $totalHoursPerMonth = config('attendance.hours_per_month', 144);

            // Map attendance and leave data to user data
            $usersWithAttendance = $allUsers->map(function ($user) use ($userAttendance, $userLeaveHours, $totalHoursPerWeek, $totalHoursPerMonth) {
                $attendance = $userAttendance->firstWhere('user_id', $user->id);
                $leaveHours = $userLeaveHours->firstWhere('user_id', $user->id);

                $user->today_hours = $attendance ? $attendance->today_hours : 0;
                $user->weekly_hours = ($attendance ? $attendance->weekly_hours : 0) + ($leaveHours ? $leaveHours->weekly_leave_hours : 0);
                $user->monthly_hours = ($attendance ? $attendance->monthly_hours : 0) + ($leaveHours ? $leaveHours->monthly_leave_hours : 0);
                $user->remaining_weekly_hours = $totalHoursPerWeek - $user->weekly_hours;
                $user->remaining_monthly_hours = $totalHoursPerMonth - $user->monthly_hours;

                return $user;
            });

            return view('admin.index', compact('usersWithAttendance', 'totalHoursPerWeek', 'totalHoursPerMonth'));
        }
    }
}
