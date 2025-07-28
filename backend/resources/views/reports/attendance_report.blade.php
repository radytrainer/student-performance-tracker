<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #F59E0B;
            padding-bottom: 15px;
        }
        .school-logo {
            font-size: 24px;
            font-weight: bold;
            color: #F59E0B;
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
        .summary-stats {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 30%;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #F59E0B;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .attendance-table th,
        .attendance-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .attendance-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .status-present { 
            color: #10B981; 
            font-weight: bold; 
            background-color: #D1FAE5;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .status-absent { 
            color: #EF4444; 
            font-weight: bold; 
            background-color: #FEE2E2;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .status-late { 
            color: #F59E0B; 
            font-weight: bold; 
            background-color: #FEF3C7;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .status-excused { 
            color: #6B7280; 
            font-weight: bold; 
            background-color: #F3F4F6;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .attendance-chart {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .chart-bar {
            display: inline-block;
            height: 20px;
            margin: 0 2px;
            border-radius: 2px;
        }
        .bar-present { background-color: #10B981; }
        .bar-absent { background-color: #EF4444; }
        .bar-late { background-color: #F59E0B; }
        .bar-excused { background-color: #6B7280; }
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
        <div class="report-title">Attendance Report</div>
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

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-value">{{ $summary['percentage'] }}%</div>
            <div class="stat-label">Attendance Rate</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $summary['present_days'] }}</div>
            <div class="stat-label">Days Present</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $summary['total_days'] }}</div>
            <div class="stat-label">Total Days</div>
        </div>
    </div>

    @if($attendance_records->count() > 0)
    <h3>Attendance Details</h3>
    <table class="attendance-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Class</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Recorded By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendance_records as $record)
            <tr>
                <td>{{ $record->date->format('M d, Y') }}</td>
                <td>{{ $record->date->format('l') }}</td>
                <td>{{ $record->class->class_name ?? 'N/A' }}</td>
                <td>
                    <span class="status-{{ $record->status }}">
                        {{ ucfirst($record->status) }}
                    </span>
                </td>
                <td>{{ $record->notes ?: '-' }}</td>
                <td>{{ $record->recordedBy->first_name ?? 'System' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $statusCounts = $attendance_records->groupBy('status')->map->count();
        $presentCount = $statusCounts->get('present', 0);
        $absentCount = $statusCounts->get('absent', 0);
        $lateCount = $statusCounts->get('late', 0);
        $excusedCount = $statusCounts->get('excused', 0);
        $total = $attendance_records->count();
    @endphp

    <div class="attendance-chart">
        <h4>Attendance Pattern</h4>
        <div style="margin: 20px 0;">
            @for($i = 0; $i < $presentCount; $i++)
                <div class="chart-bar bar-present" style="width: 8px;" title="Present"></div>
            @endfor
            @for($i = 0; $i < $lateCount; $i++)
                <div class="chart-bar bar-late" style="width: 8px;" title="Late"></div>
            @endfor
            @for($i = 0; $i < $excusedCount; $i++)
                <div class="chart-bar bar-excused" style="width: 8px;" title="Excused"></div>
            @endfor
            @for($i = 0; $i < $absentCount; $i++)
                <div class="chart-bar bar-absent" style="width: 8px;" title="Absent"></div>
            @endfor
        </div>
        
        <div style="font-size: 12px; color: #666;">
            <span style="color: #10B981;">■</span> Present ({{ $presentCount }}) &nbsp;
            <span style="color: #F59E0B;">■</span> Late ({{ $lateCount }}) &nbsp;
            <span style="color: #6B7280;">■</span> Excused ({{ $excusedCount }}) &nbsp;
            <span style="color: #EF4444;">■</span> Absent ({{ $absentCount }})
        </div>
    </div>
    @else
    <div style="text-align: center; padding: 40px; color: #666;">
        <h3>No attendance records found for this period.</h3>
        <p>Please contact the academic office if you believe this is an error.</p>
    </div>
    @endif

    <div class="footer">
        <p>This report shows your attendance records for the selected period.</p>
        <p>For attendance corrections or questions, please contact your class teacher.</p>
        <p>Report generated automatically by the Student Performance Tracker system.</p>
    </div>
</body>
</html>
