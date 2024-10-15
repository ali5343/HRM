<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;

class AdminDashboardController extends Controller
{  public function view()
    {
    $user = Auth::user();
       
    if (Auth::user()->hasRole('admin')) {
        // Fetch additional data for the admin dashboard
        $allAttendances = DB::table('attendance')->orderBy('created_at', 'desc')->get();
        $allWeekendAttendances = DB::table('weekend')->orderBy('created_at', 'desc')->get();
        $allUsers = User::count();
        $username = User::get('name'); // Assuming you are counting the total number of users
            // Pass the relevant data to the admin view
            return view('admin.index', compact('allUsers','username'));
        // Pass the relevant data to the admin view
        
    }
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
        
        'weeklyTotalHours' => $weeklyTotalHours,
        'monthlyTotalHours' => $monthlyTotalHours,
        'leftHoursPerWeek' => $leftHoursPerWeek,
        'leftHoursPerMonth' => $leftHoursPerMonth, // Added left hours for the month
        
    ]);
}
}
