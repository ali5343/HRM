<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Leaves;
use App\Models\Request as UserRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Carbon;
use App\Notifications\RequestAccepted;

// Import the notification class
use App\Models\User;
use App\Notifications\RequestRejected;

class AdminRequestController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403); // Access denied
        }

        // Fetch pending requests
        $requests = UserRequest::where('status', 'pending')->get();

        // Pass the requests to the view
        return view('admin.dashboard', compact('requests'));
    }

    public function view()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403); // Access denied
        }

        // Fetch pending requests
        $requests = UserRequest::where('status', 'pending')->get();

        // Pass the requests to the view
        return view('admin.index', compact('requests'));
    }

    public function approve($id)
    {
        $requestEntry = \App\Models\Request::find($id);
        if ($requestEntry) {
            // Mark the request as approved
            $requestEntry->update(['status' => 'approved']);

            // After approval, perform additional actions based on the request type
            switch ($requestEntry->type) {
                case 'weekend':
                case 'wfh':
                case 'overtime':
                    // Add the hours worked to the attendance log
                    Attendance::create([
                        'user_id' => $requestEntry->user_id,
                        'clock_in' => $requestEntry->start_time,
                        'clock_out' => $requestEntry->end_time,
                        'total_hours' => $requestEntry->total_hours,
                        'is_weekend' => $requestEntry->type === 'weekend',
                    ]);
                    break;

                case 'meeting':
                    // Log meeting hours
                    Attendance::create([
                        'user_id' => $requestEntry->user_id,
                        'clock_in' => $requestEntry->start_time,
                        'clock_out' => $requestEntry->end_time,
                        'total_hours' => $requestEntry->total_hours,
                        'is_meeting' => true,
                    ]);

                    break;



                case 'leave':
                    // Parse `start_time` and `end_time` into proper date or datetime format
                    $startDate = Carbon::parse($requestEntry->start_time); // Converts to a Carbon instance
                    $endDate = Carbon::parse($requestEntry->end_time);     // Converts to a Carbon instance


                    $leave = new Leaves();
                    $leave->user_id = $requestEntry->user_id;
                    $leave->start_date = $startDate->toDateString();
                    $leave->end_date = $endDate->toDateString();

                    $leave->status = 'approved';
                    $leave->save();

                    // Create the leave record directly in the `leaves` table
                    /*$leave = Leaves::create([
                        'user_id' => $requestEntry->user_id,
                        'start_date' => $requestEntry->start_time, // Save as date string (e.g., '2024-11-12')
                        'end_date' => $requestEntry->end_time,     // Save as date string
                        'leave_type' => $requestEntry->leave_type,
                        'status' => 'approved',                    // Approve the leave directly
                    ]);*/

                    // Step 2: Calculate leave hours if needed
                    $leaveHours = $startDate->diffInHours($endDate);

                    // Step 3: Add the leave entry to the `attendance` table
                    Attendance::create([
                        'user_id' => $leave->user_id,
                        'clock_in' => $startDate->toDateTimeString(), // Save as full datetime
                        'clock_out' => $endDate->toDateTimeString(),   // Save as full datetime
                        'total_hours' => $leaveHours,
                        'is_leave' => true,                            // Custom field to mark it as leave
                    ]);

                    break;




            }

            // Notify the user of approval
            $user = User::find($requestEntry->user_id);
            if ($user) {
                $user->notify(new RequestAccepted($requestEntry)); // Send the notification
            }

            return redirect()->back()->with('success', 'Request approved, processed, and user notified.');
        }

        return redirect()->back()->with('error', 'Request not found.');
    }

    public function reject($id)
    {
        $requestEntry = \App\Models\Request::find($id);
        if ($requestEntry) {
            // Mark the request as rejected
            $requestEntry->update(['status' => 'rejected']);

            // After rejection, perform additional actions based on the request type
            switch ($requestEntry->type) {
                case 'weekend':
                case 'wfh':
                case 'overtime':
                    // Notify the user of rejection
                    break;

                case 'meeting':
                    // Notify the user of rejection
                    break;

                case 'leave':
                    // Notify the user of rejection
                    break;
            }

            // Notify the user of rejection
            $user = User::find($requestEntry->user_id);
            if ($user) {
                $user->notify(new RequestRejected($requestEntry)); // Send the notification
            }

            return redirect()->back()->with('success', 'Request rejected and user notified.');
        }

        return redirect()->back()->with('error', 'Request not found.');
    }
}
