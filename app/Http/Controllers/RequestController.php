<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;


class RequestController extends Controller
{
    public function create(HttpRequest $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:meeting,weekend,wfh,overtime,leave',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'reason' => 'nullable|string',
        ]);

        // Calculate total hours if start_time and end_time are provided
        $totalHours = null;
        if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
            $totalHours = Carbon::parse($validated['start_time'])->diffInHours($validated['end_time']);
        }

        Request::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'reason' => $validated['reason'] ?? null,
            'total_hours' => $totalHours,
        ]);
    
        return redirect()->back()->with('success', 'Request created successfully.');
    }

}
