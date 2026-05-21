<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class LeaveRequestController extends Controller
{
    public function create()
    {
        return Inertia::render('LeaveRequests/Create', [
            'leaveTypes' => LeaveType::select('id', 'name')->get(),
            'countries' => Country::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
            'destination_country_id' => 'nullable|exists:countries,id',
            'alternative_contact_no' => 'nullable|string|max:20'
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        $linemanagerEmployeeId = auth()->user()->employee?->line_manager_id;
        $linemanagerUserId = null;

        if ($linemanagerEmployeeId) {
            $managerUser = User::where('employee_id', $linemanagerEmployeeId)->first();
            $linemanagerUserId = $managerUser ? $managerUser->id : null;
        }


        LeaveRequest::create([
            'user_id' => auth()->id(),
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'destination_country_id' => $validated['destination_country_id'] ?? null,
            'alternative_contact_no' => $validated['alternative_contact_no'] ?? null,
            'manager_id' => $linemanagerUserId
        ]);

        // 5. Redirect back to the dashboard with a success session flash
        return redirect()->route('dashboard')->with('success', 'Leave request submitted successfully.');

    }

}
