<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #10B981;
            padding-bottom: 15px;
        }
        .school-logo {
            font-size: 24px;
            font-weight: bold;
            color: #10B981;
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
        .subject-section {
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .subject-header {
            background-color: #10B981;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
        }
        .grades-table {
            width: 100%;
            border-collapse: collapse;
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
        .summary-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .overall-gpa {
            font-size: 36px;
            font-weight: bold;
            color: #10B981;
        }
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
        <div class="report-title">Detailed Grade Report</div>
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

    <div class="summary-box">
        <div style="font-size: 14px; color: #666; margin-bottom: 10px;">Overall GPA</div>
        <div class="overall-gpa">{{ number_format($overall_gpa, 2) }}</div>
    </div>

    @if($grades_by_subject->count() > 0)
        @foreach($grades_by_subject as $subjectName => $subjectGrades)
        <div class="subject-section">
            <div class="subject-header">{{ $subjectName }}</div>
            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Assessment Type</th>
                        <th>Date</th>
                        <th>Score</th>
                        <th>Max Score</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Weightage</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjectGrades as $grade)
                    <tr>
                        <td>{{ ucfirst($grade->assessment_type) }}</td>
                        <td>{{ $grade->recorded_at ? $grade->recorded_at->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $grade->score_obtained }}</td>
                        <td>{{ $grade->max_score }}</td>
                        <td>{{ number_format(($grade->score_obtained / $grade->max_score) * 100, 1) }}%</td>
                        <td class="grade-{{ strtolower(substr($grade->grade_letter, 0, 1)) }}">
                            {{ $grade->grade_letter }}
                        </td>
                        <td>{{ $grade->weightage }}%</td>
                        <td>{{ $grade->remarks ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            @php
                $subjectAverage = $subjectGrades->avg(function($grade) {
                    return ($grade->score_obtained / $grade->max_score) * 100;
                });
            @endphp
            
            <div style="padding: 10px 15px; background-color: #f8f9fa; border-top: 1px solid #ddd;">
                <strong>Subject Average: {{ number_format($subjectAverage, 1) }}%</strong>
            </div>
        </div>
        @endforeach
    @else
    <div style="text-align: center; padding: 40px; color: #666;">
        <h3>No grades recorded for this period.</h3>
        <p>Please check with your teachers or contact the academic office.</p>
    </div>
    @endif

    <div class="footer">
        <p>This report contains detailed grade information for all assessed work.</p>
        <p>For grade inquiries or disputes, please contact your subject teacher within 7 days.</p>
        <p>Report generated automatically by the Student Performance Tracker system.</p>
    </div>
</body>
</html>
