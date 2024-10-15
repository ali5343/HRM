<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
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

            // Fetch total attendance hours for each user
            $userAttendance = DB::table('attendance')
                ->select('user_id', DB::raw('SUM(total_hours) as total_hours'))
                ->groupBy('user_id')
                ->get();

            // Map attendance data to user data
            $usersWithAttendance = $allUsers->map(function($user) use ($userAttendance) {
                $attendance = $userAttendance->firstWhere('user_id', $user->id);
                $user->total_hours = $attendance ? $attendance->total_hours : 0;
                return $user;
            });

            // Pass the relevant data to the admin view
            return view('admin.index', compact('usersWithAttendance'));
        }

        // If not admin, redirect or show a different view (as needed)
        return redirect()->route('dashboard');
    }
}
