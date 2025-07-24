<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\ClassSubject;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->student) {
            return response()->json(['message' => 'No student data found.'], 404);
        }

        $attendanceRecords = Attendance::with(['class.classSubjects.subject'])
            ->where('student_id', $user->student->user_id)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'date' => $record->date->format('Y-m-d'),
                    'status' => $record->status,
                    'recorded_at' => $record->recorded_at->format('Y-m-d H:i:s'),
                    'class_id' => $record->class_id,
                    'class_name' => $record->class->name ?? 'N/A',
                    'subject_name' => $this->getSubjectName($record->class_id),
                    'notes' => $record->notes,
                ];
            });

        return response()->json([
            'message' => 'Attendance records fetched successfully.',
            'data' => $attendanceRecords
        ]);
    }

    protected function getSubjectName($classId)
    {
        $subject = ClassSubject::where('class_id', $classId)
            ->with('subject')
            ->first();

        return $subject->subject->subject_name ?? 'N/A';
    }
}