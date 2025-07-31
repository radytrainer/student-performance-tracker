<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Transcript</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 20px;
            color: #000;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
        }
        .school-logo {
            font-size: 28px;
            font-weight: bold;
            color: #000;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin: 15px 0;
            text-decoration: underline;
        }
        .official-seal {
            margin: 10px 0;
            font-style: italic;
            color: #666;
        }
        .student-info {
            background-color: #f5f5f5;
            padding: 20px;
            border: 2px solid #000;
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            margin-bottom: 12px;
        }
        .info-label {
            font-weight: bold;
            width: 180px;
        }
        .summary-box {
            float: right;
            width: 200px;
            border: 2px solid #000;
            padding: 15px;
            margin: 0 0 20px 20px;
            background-color: #f9f9f9;
        }
        .summary-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .term-section {
            margin: 30px 0;
            page-break-inside: avoid;
        }
        .term-header {
            background-color: #000;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 16px;
        }
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .grades-table th,
        .grades-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .grades-table th {
            background-color: #e0e0e0;
            font-weight: bold;
        }
        .grade-column {
            text-align: center;
            width: 60px;
        }
        .credits-column {
            text-align: center;
            width: 60px;
        }
        .term-summary {
            background-color: #f0f0f0;
            padding: 10px;
            border: 1px solid #000;
            margin-bottom: 20px;
        }
        .term-gpa {
            font-weight: bold;
            float: right;
        }
        .validation-section {
            margin-top: 40px;
            border-top: 2px solid #000;
            padding-top: 20px;
        }
        .signature-line {
            margin: 30px 0;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .line {
            border-bottom: 1px solid #000;
            height: 40px;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #666;
            padding-top: 15px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0,0,0,0.05);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="watermark">OFFICIAL</div>
    
    <div class="header">
        <div class="school-logo">STUDENT PERFORMANCE TRACKER INSTITUTE</div>
        <div class="official-seal">Official Academic Institution</div>
        <div class="report-title">OFFICIAL TRANSCRIPT</div>
        <div>Issued on {{ $generated_at->format('F d, Y') }}</div>
    </div>

    <div class="summary-box">
        <div class="summary-title">ACADEMIC SUMMARY</div>
        <div class="summary-item">
            <span>Overall GPA:</span>
            <span><strong>{{ number_format($overall_gpa, 2) }}</strong></span>
        </div>
        <div class="summary-item">
            <span>Total Credits:</span>
            <span><strong>{{ $total_credits }}</strong></span>
        </div>
        <div class="summary-item">
            <span>Status:</span>
            <span><strong>{{ $graduation_status }}</strong></span>
        </div>
    </div>

    <div class="student-info">
        <div class="info-row">
            <span class="info-label">Full Name:</span>
            <span>{{ strtoupper($student->user->first_name . ' ' . $student->user->last_name) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Student ID:</span>
            <span>{{ $student->student_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date of Birth:</span>
            <span>{{ $student->date_of_birth ? $student->date_of_birth->format('F d, Y') : 'Not Provided' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Current Class:</span>
            <span>{{ $student->currentClass->class_name ?? 'Not Assigned' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Enrollment Date:</span>
            <span>{{ $student->enrollment_date ? $student->enrollment_date->format('F d, Y') : 'Not Provided' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Transcript Date:</span>
            <span>{{ $generated_at->format('F d, Y') }}</span>
        </div>
    </div>

    <div style="clear: both;"></div>

    @if(isset($grades_by_term) && $grades_by_term->count() > 0)
        @foreach($grades_by_term as $termName => $termGrades)
        <div class="term-section">
            <div class="term-header">{{ strtoupper($termName) }}</div>
            
            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th class="credits-column">Credits</th>
                        <th class="grade-column">Grade</th>
                        <th>Assessment Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($termGrades as $grade)
                    <tr>
                        <td>{{ $grade->classSubject->subject->subject_code }}</td>
                        <td>{{ $grade->classSubject->subject->subject_name }}</td>
                        <td class="credits-column">{{ $grade->classSubject->subject->credit_hours ?? 1 }}</td>
                        <td class="grade-column"><strong>{{ $grade->grade_letter }}</strong></td>
                        <td>{{ ucfirst($grade->assessment_type) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            @php
                $termCredits = $termGrades->sum(function($grade) {
                    return $grade->classSubject->subject->credit_hours ?? 1;
                });
                $termGpaPoints = 0;
                $termCreditHours = 0;
                foreach($termGrades as $grade) {
                    $gradePoints = match($grade->grade_letter) {
                        'A+', 'A' => 4.0,
                        'A-' => 3.7,
                        'B+' => 3.3,
                        'B' => 3.0,
                        'B-' => 2.7,
                        'C+' => 2.3,
                        'C' => 2.0,
                        'C-' => 1.7,
                        'D+' => 1.3,
                        'D' => 1.0,
                        'D-' => 0.7,
                        default => 0.0
                    };
                    $credits = $grade->classSubject->subject->credit_hours ?? 1;
                    $termGpaPoints += $gradePoints * $credits;
                    $termCreditHours += $credits;
                }
                $termGpa = $termCreditHours > 0 ? $termGpaPoints / $termCreditHours : 0;
            @endphp
            
            <div class="term-summary">
                <span>Term Credits: {{ $termCredits }}</span>
                <span class="term-gpa">Term GPA: {{ number_format($termGpa, 2) }}</span>
            </div>
        </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 40px; font-style: italic;">
            No academic records found for this student.
        </div>
    @endif

    <div class="validation-section">
        <h3>TRANSCRIPT VALIDATION</h3>
        <p>This is to certify that the above is a true and complete record of the academic work completed by the above-named student at this institution.</p>
        
        <div class="signature-line">
            <div class="signature-box">
                <div class="line"></div>
                <div>Registrar's Signature</div>
            </div>
            <div class="signature-box">
                <div class="line"></div>
                <div>Date</div>
            </div>
            <div class="signature-box">
                <div class="line"></div>
                <div>Official Seal</div>
            </div>
        </div>
        
        <p style="margin-top: 20px; font-weight: bold; text-align: center;">
            THIS TRANSCRIPT IS INVALID WITHOUT THE OFFICIAL SEAL AND SIGNATURE
        </p>
    </div>

    <div class="footer">
        <p><strong>STUDENT PERFORMANCE TRACKER INSTITUTE</strong></p>
        <p>Official Academic Records • This document was generated electronically</p>
        <p>For verification purposes, contact the Academic Records Office</p>
        <p>Transcript ID: TR-{{ $student->student_code }}-{{ $generated_at->format('Ymd-His') }}</p>
    </div>
</body>
</html>
