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
use PhpOffice\PhpSpreadsheet\Style\Font;

class AcademicSummaryExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $student = $this->data['student'];
        $grades = $this->data['grades'];
        $attendance = $this->data['attendance'];
        $classRank = $this->data['class_rank'];

        $rows = [];
        
        // Student Information Section
        $rows[] = ['STUDENT INFORMATION', '', '', '', '', ''];
        $rows[] = ['Name:', $student->user->first_name . ' ' . $student->user->last_name, '', '', '', ''];
        $rows[] = ['Student ID:', $student->student_code, '', '', '', ''];
        $rows[] = ['Class:', $student->currentClass->class_name ?? 'Not Assigned', '', '', '', ''];
        $rows[] = ['Generated:', now()->format('F d, Y'), '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Academic Summary Section
        $rows[] = ['ACADEMIC SUMMARY', '', '', '', '', ''];
        $rows[] = ['Overall GPA:', number_format($this->data['gpa'], 2), '', '', '', ''];
        $rows[] = ['Attendance Rate:', $attendance['percentage'] . '%', '', '', '', ''];
        $rows[] = ['Class Rank:', $classRank['rank'] . '/' . $classRank['total'], '', '', '', ''];
        $rows[] = ['Present Days:', $attendance['present_days'] . '/' . $attendance['total_days'], '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Grades Section
        $rows[] = ['SUBJECT PERFORMANCE', '', '', '', '', ''];
        $rows[] = ['Subject', 'Assessment Type', 'Score', 'Max Score', 'Percentage', 'Grade'];
        
        foreach ($grades as $grade) {
            $percentage = $grade->max_score > 0 ? round(($grade->score_obtained / $grade->max_score) * 100, 1) : 0;
            $rows[] = [
                $grade->classSubject->subject->subject_name,
                ucfirst($grade->assessment_type),
                $grade->score_obtained,
                $grade->max_score,
                $percentage . '%',
                $grade->grade_letter
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return []; // We handle headers manually in the array
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the headers
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            7 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            13 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DC2626']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            14 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '1F2937']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F3F4F6']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 12,
        ];
    }

    public function title(): string
    {
        return 'Academic Summary';
    }
}
