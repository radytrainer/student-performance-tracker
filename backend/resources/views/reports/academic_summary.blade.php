<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 15px;
        }
        .school-logo {
            font-size: 24px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        .student-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 22%;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #3B82F6;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .grades-table th,
        .grades-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .grades-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .grade-a { color: #10B981; font-weight: bold; }
        .grade-b { color: #3B82F6; font-weight: bold; }
        .grade-c { color: #F59E0B; font-weight: bold; }
        .grade-d { color: #EF4444; font-weight: bold; }
        .grade-f { color: #DC2626; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-logo">Student Performance Tracker</div>
        <div class="report-title">Academic Summary Report</div>
        <div>Generated on {{ $generated_at->format('F d, Y') }}</div>
    </div>

    <div class="student-info">
        <div class="info-row">
            <span class="info-label">Student Name:</span>
            <span>{{ $student->user->first_name }} {{ $student->user->last_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Student ID:</span>
            <span>{{ $student->student_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Class:</span>
            <span>{{ $student->currentClass->class_name ?? 'Not Assigned' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Report Period:</span>
            <span>{{ ucfirst(str_replace('_', ' ', $period)) }}</span>
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-value">{{ number_format($gpa, 1) }}</div>
            <div class="stat-label">Current GPA</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $attendance['percentage'] }}%</div>
            <div class="stat-label">Attendance Rate</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $class_rank['rank'] }}/{{ $class_rank['total'] }}</div>
            <div class="stat-label">Class Rank</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $attendance['present_days'] }}/{{ $attendance['total_days'] }}</div>
            <div class="stat-label">Days Present</div>
        </div>
    </div>

    @if($grades->count() > 0)
    <h3>Subject Performance</h3>
    <table class="grades-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Assessment Type</th>
                <th>Score</th>
                <th>Max Score</th>
                <th>Percentage</th>
                <th>Grade</th>
                <th>Term</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades as $grade)
            <tr>
                <td>{{ $grade->classSubject->subject->subject_name }}</td>
                <td>{{ ucfirst($grade->assessment_type) }}</td>
                <td>{{ $grade->score_obtained }}</td>
                <td>{{ $grade->max_score }}</td>
                <td>{{ number_format(($grade->score_obtained / $grade->max_score) * 100, 1) }}%</td>
                <td class="grade-{{ strtolower(substr($grade->grade_letter, 0, 1)) }}">
                    {{ $grade->grade_letter }}
                </td>
                <td>{{ $grade->term->term_name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 20px; color: #666;">
        No grades recorded for this period.
    </div>
    @endif

    <div class="footer">
        <p>This report is generated automatically by the Student Performance Tracker system.</p>
        <p>For any discrepancies, please contact the academic office within 48 hours.</p>
    </div>
</body>
</html>
