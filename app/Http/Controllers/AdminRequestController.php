<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Request as UserRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Carbon;
use App\Notifications\RequestAccepted; // Import the notification class
use App\Models\User;

class AdminRequestController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403); // Access denied
        }

        // Fetch pending requests
        $requests = UserRequest::where('is_approved', false)->get();
        
        // Pass the requests to the view
        return view('admin.dashboard', compact('requests'));
    }
    public function view()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403); // Access denied
        }

        // Fetch pending requests
        $requests = UserRequest::where('is_approved', false)->get();
        
        // Pass the requests to the view
        return view('admin.index', compact('requests'));
    }

    public function approve($id)
    {
        $requestEntry = \App\Models\Request::find($id);
        if ($requestEntry) {
            // Mark the request as approved
            $requestEntry->update(['is_approved' => true]);

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
                    // Log meeting hours or take other actions
                    break;

                case 'leave':
                    // Handle leave (e.g., deduct leave days)
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
}
