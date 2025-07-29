<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $student = $this->data['student'];
        $attendanceRecords = $this->data['attendance_records'];
        $summary = $this->data['summary'];
        
        $rows = [];
        
        // Header Section
        $rows[] = ['ATTENDANCE REPORT', '', '', '', '', ''];
        $rows[] = ['Name:', $student->user->first_name . ' ' . $student->user->last_name, '', '', '', ''];
        $rows[] = ['Student ID:', $student->student_code, '', '', '', ''];
        $rows[] = ['Class:', $student->currentClass->class_name ?? 'Not Assigned', '', '', '', ''];
        $rows[] = ['Period:', ucfirst(str_replace('_', ' ', $this->data['period'])), '', '', '', ''];
        $rows[] = ['Generated:', now()->format('F d, Y H:i'), '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Summary Section
        $rows[] = ['ATTENDANCE SUMMARY', '', '', '', '', ''];
        $rows[] = ['Overall Attendance Rate:', $summary['percentage'] . '%', '', '', '', ''];
        $rows[] = ['Total Days:', $summary['total_days'], '', '', '', ''];
        $rows[] = ['Days Present:', $summary['present_days'], '', '', '', ''];
        $rows[] = ['Days Absent:', $summary['total_days'] - $summary['present_days'], '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Detailed Records Section
        $rows[] = ['DETAILED ATTENDANCE RECORDS', '', '', '', '', ''];
        $rows[] = ['Date', 'Day', 'Class', 'Status', 'Notes', 'Recorded By'];
        
        foreach ($attendanceRecords as $record) {
            $rows[] = [
                $record->date->format('M d, Y'),
                $record->date->format('l'),
                $record->class->class_name ?? 'N/A',
                ucfirst($record->status),
                $record->notes ?: '-',
                $record->recordedBy->first_name ?? 'System'
            ];
        }

        // Statistics Section
        $statusCounts = $attendanceRecords->groupBy('status')->map->count();
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['ATTENDANCE STATISTICS', '', '', '', '', ''];
        $rows[] = ['Status', 'Count', 'Percentage', '', '', ''];
        
        $total = $attendanceRecords->count();
        foreach (['present', 'late', 'absent', 'excused'] as $status) {
            $count = $statusCounts->get($status, 0);
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $rows[] = [
                ucfirst($status),
                $count,
                $percentage . '%',
                '',
                '',
                ''
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Main headers
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F59E0B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            8 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            14 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '7C3AED']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            15 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '1F2937']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F3F4F6']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 12,
            'C' => 15,
            'D' => 12,
            'E' => 25,
            'F' => 15,
        ];
    }

    public function title(): string
    {
        return 'Attendance Report';
    }
}
