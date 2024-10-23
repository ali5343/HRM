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
        // Get the authenticated user
        $user = Auth::user();
       
        if ($user->hasRole('admin')) {
            // Fetch additional data for the admin dashboard
            $allAttendances = DB::table('attendance')->orderBy('created_at', 'desc')->get();
            $allUsers = User::count(); // Count total number of users
        
            // Define total expected hours per week and per month
            $totalHoursPerWeek = 36;
            $totalHoursPerMonth = 144;
        
            // Define the start and end of today, week, and month
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $startOfToday = Carbon::now()->startOfDay();
            $endOfToday = Carbon::now()->endOfDay();
        
            // Query user attendance for the current week, month, and day
            $userAttendance = Attendance::query()
                ->select('user_id',
                    DB::raw('SUM(CASE WHEN created_at BETWEEN "' . $startOfToday . '" AND "' . $endOfToday . '" THEN total_hours ELSE 0 END) as today_hours'),
                    DB::raw('SUM(CASE WHEN created_at BETWEEN "' . $startOfWeek . '" AND "' . $endOfWeek . '" THEN total_hours ELSE 0 END) as weekly_hours'),
                    DB::raw('SUM(CASE WHEN created_at BETWEEN "' . $startOfMonth . '" AND "' . $endOfMonth . '" THEN total_hours ELSE 0 END) as monthly_hours')
                )
                ->groupBy('user_id')
                ->get();
        
            // Fetch leave days for users this week and this month
            $leaveDaysThisWeek = Attendance::query()
                ->select('user_id', DB::raw('COUNT(*) as leave_days_this_week'))
                ->where('is_leave', true)
                ->whereBetween('clock_in', [$startOfWeek, $endOfWeek])
                ->groupBy('user_id')
                ->get();
        
            $leaveDaysThisMonth = Attendance::query()
                ->select('user_id', DB::raw('COUNT(*) as leave_days_this_month'))
                ->where('is_leave', true)
                ->whereBetween('clock_in', [$startOfMonth, $endOfMonth])
                ->groupBy('user_id')
                ->get();
        
            // Map user attendance and calculate remaining hours, considering leave days
            $usersWithAttendance = $allUsers->map(function($user) use ($userAttendance, $leaveDaysThisWeek, $leaveDaysThisMonth, $totalHoursPerWeek, $totalHoursPerMonth) {
                $attendance = $userAttendance->firstWhere('user_id', $user->id);
                $user->today_hours = $attendance ? $attendance->today_hours : 0;
                $user->weekly_hours = $attendance ? $attendance->weekly_hours : 0;
                $user->monthly_hours = $attendance ? $attendance->monthly_hours : 0;
        
                // Find the leave days for the user
                $weeklyLeaveDays = $leaveDaysThisWeek->firstWhere('user_id', $user->id)->leave_days_this_week ?? 0;
                $monthlyLeaveDays = $leaveDaysThisMonth->firstWhere('user_id', $user->id)->leave_days_this_month ?? 0;
        
                // Subtract leave hours (8 hours per leave day) from total expected hours
                $user->remaining_weekly_hours = $totalHoursPerWeek - $user->weekly_hours - ($weeklyLeaveDays * 8);
                $user->remaining_monthly_hours = $totalHoursPerMonth - $user->monthly_hours - ($monthlyLeaveDays * 8);
                
                return $user;
            });
        
            // Fetch admin attendance information
            $adminAttendance = $usersWithAttendance->firstWhere('id', $user->id);
        
            // Pass the relevant data to the admin view
            return view('admin.index', compact('usersWithAttendance', 'adminAttendance', 'totalHoursPerWeek', 'totalHoursPerMonth'));
        }
        

        // Fetch attendance records for the user
        $attendances = DB::table('attendance')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc') // Optional: order by the most recent first
            ->get();

        // Fetch weekend attendance records for the user
        $weekendAttendances = DB::table('weekend')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc') // Optional: order by the most recent first
            ->get();

        // Calculate total hours for today
        // Calculate total hours for today excluding leave
        $today = Carbon::today();
        $todayTotalHours = $attendances->filter(function ($attendance) use ($today) {
        // Exclude attendance records where `is_leave` is true
        return Carbon::parse($attendance->created_at)->isToday() && !$attendance->is_leave;
        })->sum('total_hours');


        // Calculate actual worked hours for the current week (without subtracting leave hours)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $weeklyTotalHours = $attendances->filter(function ($attendance) use ($startOfWeek, $endOfWeek) {
            return Carbon::parse($attendance->created_at)->between($startOfWeek, $endOfWeek) && !$attendance->is_leave;
        })->sum('total_hours');

        // Get number of leave days this week, but don't subtract from worked hours
        $leaveDaysThisWeek = DB::table('attendance')
            ->where('user_id', $user->id)
            ->where('is_leave', true)
            ->whereBetween('clock_in', [$startOfWeek, $endOfWeek])
            ->count();

        // Calculate actual worked hours for the current month (without subtracting leave hours)
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $monthlyTotalHours = $attendances->filter(function ($attendance) use ($startOfMonth, $endOfMonth) {
            return Carbon::parse($attendance->created_at)->between($startOfMonth, $endOfMonth) && !$attendance->is_leave;
        })->sum('total_hours');

        // Get number of leave days this month
        $leaveDaysThisMonth = DB::table('attendance')
            ->where('user_id', $user->id)
            ->where('is_leave', true)
            ->whereBetween('clock_in', [$startOfMonth, $endOfMonth])
            ->count();

        // Define the total hours expected per week
        $totalHoursPerWeek = 36;

        // Calculate remaining hours left in the week, subtracting leave hours
        $leftHoursPerWeek = $totalHoursPerWeek - $weeklyTotalHours - ($leaveDaysThisWeek * 8);

        // Define the total hours expected per month
        $totalMonthlyHours = 144;

        // Calculate remaining hours left in the month, subtracting leave hours
        $leftHoursPerMonth = $totalMonthlyHours - $monthlyTotalHours - ($leaveDaysThisMonth * 8);

        // Pass the attendance records, today's total hours, weekly total hours,
        // monthly total hours, left hours per week, and left hours per month to the view
        return view('dashboard', [
            'attendances' => $attendances,
            'weekendAttendances' => $weekendAttendances,
            'todayTotalHours' => $todayTotalHours,
            'weeklyTotalHours' => $weeklyTotalHours, // Worked hours without leaves
            'monthlyTotalHours' => $monthlyTotalHours, // Worked hours without leaves
            'leftHoursPerWeek' => $leftHoursPerWeek,
            'leftHoursPerMonth' => $leftHoursPerMonth,
            'isClockedIn' => $attendances->whereNull('clock_out')->isNotEmpty(),
        ]);
    }

    public function admin_index()
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
            return view('admin.index', compact('usersWithAttendance', 'adminAttendance', 'totalHoursPerWeek', 'totalHoursPerMonth'));
        }
        // If not admin, redirect or show a different view (as needed)
        return redirect()->route('dashboard');
    }
}