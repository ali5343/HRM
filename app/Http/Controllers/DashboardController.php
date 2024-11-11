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

        if ($user->hasRole('admin')) {
            $allUsers = User::paginate(5); // Adjust pagination as needed
            $userData = $this->fetchUserData($allUsers, $startOfToday, $endOfToday, $startOfWeek, $endOfWeek, $startOfMonth, $endOfMonth, $totalHoursPerWeek, $totalHoursPerMonth);
            $adminAttendance = $userData->firstWhere('id', $user->id);

            /*dd($userData);*/
            return view('admin.index', compact('userData', 'adminAttendance', 'totalHoursPerWeek', 'totalHoursPerMonth'));
        }

        // Fetch and calculate data for the regular user
        $userData = $this->fetchUserData(collect([$user]), $startOfToday, $endOfToday, $startOfWeek, $endOfWeek, $startOfMonth, $endOfMonth, $totalHoursPerWeek, $totalHoursPerMonth);

        return view('dashboard', [
            'userData' => $userData->first(),
            'isClockedIn' => $this->checkClockedInStatus($user->id),
            'totalHoursPerWeek' => $totalHoursPerWeek,
            'totalHoursPerMonth' => $totalHoursPerMonth,
        ]);
    }

    private function fetchUserData($users, $startOfToday, $endOfToday, $startOfWeek, $endOfWeek, $startOfMonth, $endOfMonth, $totalHoursPerWeek, $totalHoursPerMonth)
    {
        $userIds = $users->pluck('id');
        $attendanceData = $this->fetchUserAttendance($userIds, $startOfToday, $endOfToday, $startOfWeek, $endOfWeek, $startOfMonth, $endOfMonth);
        $leaveHoursWeekly = $this->fetchLeaveHours($userIds, $startOfWeek, $endOfWeek);
        $leaveHoursMonthly = $this->fetchLeaveHours($userIds, $startOfMonth, $endOfMonth);

        return $users->map(function ($user) use ($attendanceData, $leaveHoursWeekly, $leaveHoursMonthly, $totalHoursPerWeek, $totalHoursPerMonth) {
            return $this->mapUserAttendance($user, $attendanceData, $leaveHoursWeekly, $leaveHoursMonthly, $totalHoursPerWeek, $totalHoursPerMonth);
        });
    }

    private function fetchUserAttendance($userIds, $startOfToday, $endOfToday, $startOfWeek, $endOfWeek, $startOfMonth, $endOfMonth)
    {
        return Attendance::whereIn('user_id', $userIds)
            ->select('user_id')
            ->selectRaw('SUM(CASE WHEN created_at BETWEEN ? AND ? THEN total_hours ELSE 0 END) as today_hours', [$startOfToday, $endOfToday])
            ->selectRaw('SUM(CASE WHEN created_at BETWEEN ? AND ? THEN total_hours ELSE 0 END) as weekly_hours', [$startOfWeek, $endOfWeek])
            ->selectRaw('SUM(CASE WHEN created_at BETWEEN ? AND ? THEN total_hours ELSE 0 END) as monthly_hours', [$startOfMonth, $endOfMonth])
            ->groupBy('user_id')
            ->get();
    }

    private function fetchLeaveHours($userIds, $start, $end)
    {
        return Attendance::whereIn('user_id', $userIds)
            ->select('user_id', DB::raw('SUM(total_hours) as leave_hours'))
            ->where('is_leave', true)
            ->whereBetween('clock_in', [$start, $end])
            ->groupBy('user_id')
            ->get();
    }

    private function mapUserAttendance($user, $attendanceData, $leaveHoursWeekly, $leaveHoursMonthly, $totalHoursPerWeek, $totalHoursPerMonth)
    {
        $attendance = $attendanceData->firstWhere('user_id', $user->id);
        $user->today_hours = $attendance ? $attendance->today_hours : 0;
        $user->weekly_hours = $attendance ? $attendance->weekly_hours : 0;
        $user->monthly_hours = $attendance ? $attendance->monthly_hours : 0;

        $weeklyLeaveHours = $leaveHoursWeekly->firstWhere('user_id', $user->id)->leave_hours ?? 0;
        $monthlyLeaveHours = $leaveHoursMonthly->firstWhere('user_id', $user->id)->leave_hours ?? 0;

        $user->remaining_weekly_hours = $totalHoursPerWeek - $user->weekly_hours - $weeklyLeaveHours;
        $user->remaining_monthly_hours = $totalHoursPerMonth - $user->monthly_hours - $monthlyLeaveHours;

        return $user;
    }

    private function checkClockedInStatus($userId)
    {
        return Attendance::where('user_id', $userId)->whereNull('clock_out')->exists();
    }
}
