<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Attendance;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'totalUsers'    => User::count(),
            'totalStudents' => User::where('role', 'student')->count(),
            'totalTeachers' => User::where('role', 'teacher')->count(),
            'totalAdmins'   => User::where('role', 'admin')->count(),
            'activeUsers'   => User::where('is_active', true)->count(),


            // Extra stats
            'totalClasses'   => Classes::count(),
            'totalSubjects'  => Subject::count(),
            'totalTerms'     => Term::count(),
            'totalAttendance'=> Attendance::count(),
        ]);
    }
}
