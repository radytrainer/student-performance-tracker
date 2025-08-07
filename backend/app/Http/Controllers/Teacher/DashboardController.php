<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function students(Request $request)
    {
        $students = Student::with(['subjects', 'grades', 'attendances'])
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'course' => $student->course,
                    'term' => $student->term,
                    'subjects' => $student->subjects->map(fn($s) => [
                        'name' => $s->name,
                        'grade' => $student->grades->where('subject_id', $s->id)->avg('score') ?? 0,
                    ]),
                    'averageGrade' => $student->grades->avg('score') ?? 0,
                    'attendanceRate' => $student->attendances->where('status', 'present')->count() / max($student->attendances->count(), 1) * 100,
                    'monthlyGrades' => $student->grades->groupBy(fn($g) => Carbon::parse($g->created_at)->format('M'))->map(fn($g, $month) => [
                        'month' => $month,
                        'grade' => $g->avg('score')
                    ])->values(),
                ];
            });

        return response()->json(['data' => $students]);
    }
}