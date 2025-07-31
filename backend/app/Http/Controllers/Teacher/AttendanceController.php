<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class AttendanceController extends Controller
{
    // List all attendance records
    public function index()
    {
        $attendances = Attendance::with('student')->get();

        return response()->json(['attendances' => $attendances]);
    }

    // Show a single attendance record
    public function show($id)
    {
        $attendance = Attendance::with('student')->findOrFail($id);

        return response()->json(['attendance' => $attendance]);
    }

    // Store new attendance record and notify
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer',
            'class_id' => 'required|integer',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'notes' => 'nullable|string',
        ]);

        $attendance = Attendance::create($validated);

        Notification::create([
            'contact_id' => $attendance->id, // If you’re using a different foreign key, update this
        ]);

        return response()->json([
            'message' => 'Attendance and notification saved successfully!',
            'attendance' => $attendance
        ], 201);
    }

    // Update attendance record
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer',
            'class_id' => 'required|integer',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'notes' => 'nullable|string',
        ]);

        $attendance = Attendance::findOrFail($id);
        $attendance->update($validated);

        return response()->json([
            'message' => 'Attendance updated successfully',
            'attendance' => $attendance
        ], 200);
    }

    // Delete an attendance record
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json([
            'message' => 'Attendance record deleted successfully',
        ], 200);
    }
    
}
