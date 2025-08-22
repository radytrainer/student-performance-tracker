<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin,teacher']);
    }

    /**
     * Correlation between attendance (%) and grade average (%) per student for a class & term.
     * GET /api/analytics/correlation?class_id=...&term_id=...
     */
    public function correlation(Request $request): JsonResponse
    {
        try {
            $classId = (int) $request->query('class_id');
            $termId = $request->query('term_id');
            $term = $termId ? Term::find($termId) : Term::where('is_current', true)->first();

            if (!$classId || !$term) {
                return response()->json(['success' => true, 'data' => ['points' => [], 'correlation' => null]]);
            }

            $studentIds = Student::where('current_class_id', $classId)->pluck('user_id');
            if ($studentIds->isEmpty()) {
                return response()->json(['success' => true, 'data' => ['points' => [], 'correlation' => null]]);
            }

            $start = $term->start_date; $end = $term->end_date;

            // Grades aggregated per student
            $grades = Grade::selectRaw('student_id, SUM(score_obtained) as so, SUM(max_score) as ms')
                ->whereIn('student_id', $studentIds)
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');

            // Attendance aggregated per student
            $attendance = Attendance::selectRaw("student_id, COUNT(*) as total, SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) as present")
                ->whereIn('student_id', $studentIds)
                ->whereBetween('date', [$start, $end])
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');

            $points = [];
            foreach ($studentIds as $sid) {
                $g = $grades->get($sid);
                $a = $attendance->get($sid);
                if ($g && $g->ms > 0 && $a && $a->total > 0) {
                    $gradePct = ($g->so / $g->ms) * 100.0;
                    $attPct = ($a->present / $a->total) * 100.0;
                    $points[] = [ 'student_id' => $sid, 'attendance' => round($attPct, 1), 'grade' => round($gradePct, 1) ];
                }
            }

            // Compute Pearson correlation r for x=attendance, y=grade
            $correlation = null;
            $n = count($points);
            if ($n > 1) {
                $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0; $sumY2 = 0;
                foreach ($points as $p) {
                    $x = $p['attendance']; $y = $p['grade'];
                    $sumX += $x; $sumY += $y; $sumXY += ($x*$y); $sumX2 += ($x*$x); $sumY2 += ($y*$y);
                }
                $num = ($n*$sumXY - $sumX*$sumY);
                $den = sqrt(max(($n*$sumX2 - $sumX*$sumX), 0.0000001) * max(($n*$sumY2 - $sumY*$sumY), 0.0000001));
                $correlation = $den > 0 ? round($num / $den, 3) : null;
            }

            return response()->json(['success' => true, 'data' => [ 'points' => $points, 'correlation' => $correlation ]]);
        } catch (\Throwable $e) {
            Log::error('Analytics correlation error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to compute correlation'], 500);
        }
    }

    /**
     * Heatmap buckets (10x10) of attendance vs grade across class & term.
     * GET /api/analytics/heatmap?class_id=...&term_id=...
     */
    public function heatmap(Request $request): JsonResponse
    {
        try {
            $classId = (int) $request->query('class_id');
            $termId = $request->query('term_id');
            $term = $termId ? Term::find($termId) : Term::where('is_current', true)->first();

            if (!$classId || !$term) {
                return response()->json(['success' => true, 'data' => [ 'buckets' => array_fill(0,10, array_fill(0,10,0)) ]]);
            }

            // Reuse correlation computation to build points
            $request2 = new Request(['class_id' => $classId, 'term_id' => $term->id]);
            $resp = $this->correlation($request2);
            $payload = $resp->getData(true);
            $points = $payload['data']['points'] ?? [];

            $buckets = array_fill(0, 10, array_fill(0, 10, 0));
            foreach ($points as $p) {
                $ai = min(9, max(0, (int) floor($p['attendance'] / 10)));
                $gi = min(9, max(0, (int) floor($p['grade'] / 10)));
                $buckets[$ai][$gi]++;
            }

            return response()->json(['success' => true, 'data' => [ 'buckets' => $buckets ]]);
        } catch (\Throwable $e) {
            Log::error('Analytics heatmap error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to compute heatmap'], 500);
        }
    }
}
