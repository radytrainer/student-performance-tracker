<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Reminder</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #3b82f6;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .survey-info {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            margin: 20px 0;
        }
        .survey-info h3 {
            margin-top: 0;
            color: #1f2937;
        }
        .due-date {
            background-color: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
        }
        .due-date.urgent {
            background-color: #fee2e2;
            border-left-color: #ef4444;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #2563eb;
        }
        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 30px;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .instructions {
            background-color: #ecfdf5;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #10b981;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Survey Reminder</h1>
        </div>

        <div class="content">
            <p>Hello <strong>{{ $studentName }}</strong>,</p>
            
            <p>This is a friendly reminder that you have a pending feedback survey that needs your attention.</p>

            <div class="survey-info">
                <h3>📝 Survey Details</h3>
                <p><strong>Survey Title:</strong> {{ $surveyTitle }}</p>
                <p><strong>Class:</strong> {{ $className }}</p>
                @if($dueDate)
                    <p><strong>Due Date:</strong> {{ $dueDate }}</p>
                @endif
            </div>

            @if($dueDate && $daysUntilDue !== null)
                <div class="due-date {{ $daysUntilDue <= 1 ? 'urgent' : '' }}">
                    @if($daysUntilDue == 0)
                        ⚠️ <strong>Due Today!</strong> Please complete your survey as soon as possible.
                    @elseif($daysUntilDue == 1)
                        ⏰ <strong>Due Tomorrow!</strong> Don't forget to complete your survey.
                    @else
                        📅 <strong>{{ $daysUntilDue }} days remaining</strong> to complete your survey.
                    @endif
                </div>
            @endif

            @if($instructions)
                <div class="instructions">
                    <h4>📌 Special Instructions:</h4>
                    <p>{{ $instructions }}</p>
                </div>
            @endif

            <p>Your feedback is valuable and helps improve the learning experience for everyone. The survey will only take a few minutes to complete.</p>

            <div style="text-align: center;">
                <a href="{{ $surveyUrl }}" class="button">📝 Complete Survey Now</a>
            </div>

            <p><strong>What to expect:</strong></p>
            <ul>
                <li>The survey contains 5 simple questions</li>
                <li>Each question uses a 1-10 rating scale</li>
                <li>It takes approximately 2-3 minutes to complete</li>
                <li>Your responses are confidential</li>
            </ul>

            <p>If you have any questions or technical issues, please contact your teacher or the system administrator.</p>

            <p>Thank you for your participation!</p>
        </div>

        <div class="footer">
            <p>
                <strong>Student Performance Tracker</strong><br>
                This is an automated reminder. Please do not reply to this email.
            </p>
            <p>
                If you have already completed this survey, please disregard this reminder.
            </p>
        </div>
    </div>
</body>
</html>
