<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #8B5CF6;
            padding-bottom: 15px;
        }
        .school-logo {
            font-size: 24px;
            font-weight: bold;
            color: #8B5CF6;
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
        .progress-chart {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .term-section {
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .term-header {
            background-color: #8B5CF6;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
        }
        .term-stats {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background-color: #f8f9fa;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #8B5CF6;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
        }
        .achievements-section {
            background-color: #FEF3C7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .achievement-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .achievement-icon {
            width: 30px;
            height: 30px;
            background-color: #F59E0B;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-weight: bold;
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
        <div class="report-title">Academic Progress Report</div>
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

    @if(isset($progress_by_term) && count($progress_by_term) > 0)
    <h3>Progress by Term</h3>
    
    @foreach($progress_by_term as $termData)
    <div class="term-section">
        <div class="term-header">{{ $termData['term']->term_name }}</div>
        <div class="term-stats">
            <div class="stat-item">
                <div class="stat-value">{{ number_format($termData['gpa'], 1) }}</div>
                <div class="stat-label">GPA</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $termData['attendance']['percentage'] }}%</div>
                <div class="stat-label">Attendance</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $termData['grades_count'] }}</div>
                <div class="stat-label">Assessments</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $termData['attendance']['present_days'] }}/{{ $termData['attendance']['total_days'] }}</div>
                <div class="stat-label">Days Present</div>
            </div>
        </div>
    </div>
    @endforeach
    @endif

    @if(isset($achievements) && count($achievements) > 0)
    <div class="achievements-section">
        <h3 style="margin-top: 0; color: #92400E;">🏆 Achievements & Highlights</h3>
        @foreach($achievements as $achievement)
        <div class="achievement-item">
            <div class="achievement-icon">★</div>
            <div>
                <div style="font-weight: bold; color: #92400E;">{{ $achievement['title'] }}</div>
                <div style="font-size: 14px; color: #78350F;">{{ $achievement['description'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="progress-chart">
        <h4>Academic Progress Trend</h4>
        <div style="text-align: center; color: #666; margin: 20px 0;">
            @if(isset($progress_by_term) && count($progress_by_term) > 0)
                @php
                    $gpas = array_column($progress_by_term, 'gpa');
                    $trend = count($gpas) > 1 ? ($gpas[0] > $gpas[count($gpas)-1] ? 'improving' : 'declining') : 'stable';
                @endphp
                <p>Overall Trend: <strong style="color: {{ $trend === 'improving' ? '#10B981' : ($trend === 'declining' ? '#EF4444' : '#6B7280') }};">{{ ucfirst($trend) }}</strong></p>
                
                <div style="display: flex; justify-content: space-between; align-items: end; margin: 20px 0; height: 100px;">
                    @foreach($progress_by_term as $index => $termData)
                        @php
                            $height = ($termData['gpa'] / 4.0) * 80; // Scale to 80px max height
                        @endphp
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            <div style="width: 40px; background-color: #8B5CF6; margin-bottom: 5px; height: {{ $height }}px;"></div>
                            <div style="font-size: 10px; transform: rotate(-45deg); margin-top: 10px;">{{ $termData['term']->term_name }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <p>No progress data available for the selected period.</p>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>This progress report shows your academic development over time.</p>
        <p>For academic guidance or support, please contact your academic advisor.</p>
        <p>Report generated automatically by the Student Performance Tracker system.</p>
    </div>
</body>
</html>
