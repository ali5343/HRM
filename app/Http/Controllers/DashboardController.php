<?php

namespace App\Http\Controllers;

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
       
        if (Auth::user()->hasRole('admin')) {
            // Fetch additional data for the admin dashboard
            $allAttendances = DB::table('attendance')->orderBy('created_at', 'desc')->get();
            $allWeekendAttendances = DB::table('weekend')->orderBy('created_at', 'desc')->get();

            // Pass the relevant data to the admin view
            return redirect('/admin-dashboard');
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
        $today = Carbon::today();
        $todayTotalHours = $attendances->filter(function ($attendance) use ($today) {
            return Carbon::parse($attendance->created_at)->isToday();
        })->sum('total_hours');

        // Calculate total hours for the current week
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $weeklyTotalHours = $attendances->filter(function ($attendance) use ($startOfWeek, $endOfWeek) {
            return Carbon::parse($attendance->created_at)->between($startOfWeek, $endOfWeek);
        })->sum('total_hours');

        // Calculate total hours for the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $monthlyTotalHours = $attendances->filter(function ($attendance) use ($startOfMonth, $endOfMonth) {
            return Carbon::parse($attendance->created_at)->between($startOfMonth, $endOfMonth);
        })->sum('total_hours');

        // Define the total hours expected per week
        $totalHoursPerWeek = 36;

        // Calculate remaining hours left in the week
        $leftHoursPerWeek = $totalHoursPerWeek - $weeklyTotalHours;

        // Define the total hours expected per month
        $totalMonthlyHours = 144;

        // Calculate remaining hours left in the month
        $leftHoursPerMonth = $totalMonthlyHours - $monthlyTotalHours;

        // Pass the attendance records, today's total hours, weekly total hours,
        // monthly total hours, left hours per week, and left hours per month to the view
        return view('dashboard', [
            'attendances' => $attendances,
            'weekendAttendances' => $weekendAttendances,
            'todayTotalHours' => $todayTotalHours,
            'weeklyTotalHours' => $weeklyTotalHours,
            'monthlyTotalHours' => $monthlyTotalHours,
            'leftHoursPerWeek' => $leftHoursPerWeek,
            'leftHoursPerMonth' => $leftHoursPerMonth, // Added left hours for the month
            'isClockedIn' => $attendances->whereNull('clock_out')->isNotEmpty(),
        ]);
    }
}
