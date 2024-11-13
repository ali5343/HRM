<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function view()
    {
        $user = Auth::user();

        // Check if the user is an admin
        if ($user->hasRole('admin')) {
            $allUsers = User::paginate(5); // Adjust pagination as needed
            $userData = $allUsers->map(function ($user) {
                return $this->calculateUserData($user);
            });
            $adminAttendance = $userData->firstWhere('id', $user->id);

            return view('admin.index', compact('userData', 'adminAttendance'));
        }

        // Fetch and calculate data for the regular user
        $userData = $this->calculateUserData($user);
        $attendances = $user->attendances; // Fetch related attendances if needed

        return view('dashboard', compact('userData', 'attendances'));
    }

    private function calculateUserData($user)
    {
        $today = Carbon::now()->startOfDay();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalHoursPerWeek = config('attendance.hours_per_week', 36);  // Default to 36 hours
        $totalHoursPerMonth = config('attendance.hours_per_month', 144); // Default to 144 hours

        $userData = new \stdClass();
        $userData->id = $user->id;
        $userData->name = $user->name;

        // Calculate attendance hours for today, this week, and this month
        $userData->today_hours = $user->attendances()
            ->whereBetween('clock_in', [$today, Carbon::now()->endOfDay()])
            ->whereNotNull('clock_out')
            ->sum('total_hours');

        $userData->weekly_hours = $user->attendances()
            ->whereBetween('clock_in', [$startOfWeek, Carbon::now()->endOfWeek()])
            ->whereNotNull('clock_out')
            ->where('is_leave', 0)
            ->sum('total_hours');

        $userData->monthly_hours = $user->attendances()
            ->whereBetween('clock_in', [$startOfMonth, Carbon::now()->endOfMonth()])
            ->whereNotNull('clock_out')
            ->where('is_leave', 0)
            ->sum('total_hours');

        // Calculate leave hours for the current week and month
        $weeklyLeaveHours = $user->attendances()
            ->where('is_leave', true)
            ->whereBetween('clock_in', [$startOfWeek, Carbon::now()->endOfWeek()])
            ->sum('total_hours');

        $monthlyLeaveHours = $user->attendances()
            ->where('is_leave', true)
            ->whereBetween('clock_in', [$startOfMonth, Carbon::now()->endOfMonth()])
            ->sum('total_hours');

        // Calculate remaining hours after subtracting leave hours
        $userData->remaining_weekly_hours = $totalHoursPerWeek - $userData->weekly_hours - $weeklyLeaveHours;
        $userData->remaining_monthly_hours = $totalHoursPerMonth - $userData->monthly_hours - $monthlyLeaveHours;

        return $userData;
    }
}
