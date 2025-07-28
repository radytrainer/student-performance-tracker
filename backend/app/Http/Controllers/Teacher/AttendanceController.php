<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // List attendance records for a class on a specific date
    public function index(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $user = Auth::user();

        $attendances = Attendance::with('student')
            ->accessibleToUser($user)
            ->forClass($request->class_id)
            ->forDate($request->date)
            ->paginate(50);


        return response()->json($attendances);
    }

    // Show a single attendance record by ID
    public function show($id)
    {
        $user = Auth::user();

        $attendance = Attendance::with('student')
            ->accessibleToUser($user)
            ->findOrFail($id);

        return response()->json($attendance);
    }

    // Create or update multiple attendance records
    public function store(Request $request)
    {
        $request->validate([
            'records' => 'required|array',
            'records.*.student_id' => 'required|integer',
            'records.*.class_id' => 'required|integer',
            'records.*.date' => 'required|date',
            'records.*.status' => 'required|in:present,absent,late',
            'records.*.notes' => 'nullable|string',
            'records.*.excused' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        foreach ($request->records as $record) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'class_id' => $record['class_id'],
                    'date' => $record['date'],
                ],
                [
                    'status' => $record['status'],
                    'notes' => $record['notes'] ?? null,
                    'recorded_by' => $user->id,
                    'recorded_at' => now(),
                ]
            );
        }

        return response()->json(['message' => 'Attendance records saved successfully.']);
    }

    // Update a single attendance record by ID
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late',
            'notes' => 'nullable|string',
            'excused' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        $attendance = Attendance::accessibleToUser($user)->findOrFail($id);

        $attendance->update([
            'status' => $request->status,
            'notes' => $request->notes ?? null,
            'excused' => $request->excused ?? false,
            'recorded_by' => $user->id,
            'recorded_at' => now(),
        ]);


        return response()->json(['message' => 'Attendance record updated successfully.', 'attendance' => $attendance]);
    }

    // Delete a single attendance record by ID
    public function destroy($id)
    {
        $user = Auth::user();

        $attendance = Attendance::accessibleToUser($user)->findOrFail($id);

        $attendance->delete();

        return response()->json(['message' => 'Attendance record deleted successfully.']);
    }

    // Export attendance CSV for a class and date
    public function export(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $user = Auth::user();

        $attendances = Attendance::with('student')
            ->accessibleToUser($user)
            ->forClass($request->class_id)
            ->forDate($request->date)
            ->get();

        $filename = 'attendance_export_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student Name', 'Student ID', 'Date', 'Status', 'Notes']);

            foreach ($attendances as $record) {
                fputcsv($handle, [
                    $record->student->name ?? 'N/A',
                    $record->student_id,
                    $record->date->toDateString(),
                    $record->status,
                    $record->notes,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
