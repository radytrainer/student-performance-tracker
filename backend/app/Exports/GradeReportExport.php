<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GradeReportExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Create a summary sheet
        $sheets[] = new GradeReportSummarySheet($this->data);
        
        // Create a sheet for each subject
        foreach ($this->data['grades_by_subject'] as $subjectName => $grades) {
            $sheets[] = new SubjectGradeSheet($subjectName, $grades, $this->data['student']);
        }

        return $sheets;
    }
}

class GradeReportSummarySheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $student = $this->data['student'];
        $rows = [];
        
        // Student Information
        $rows[] = ['GRADE REPORT SUMMARY', '', '', '', ''];
        $rows[] = ['Name:', $student->user->first_name . ' ' . $student->user->last_name, '', '', ''];
        $rows[] = ['Student ID:', $student->student_code, '', '', ''];
        $rows[] = ['Class:', $student->currentClass->class_name ?? 'Not Assigned', '', '', ''];
        $rows[] = ['Overall GPA:', number_format($this->data['overall_gpa'], 2), '', '', ''];
        $rows[] = ['Generated:', now()->format('F d, Y H:i'), '', '', ''];
        $rows[] = ['', '', '', '', ''];

        // Subject Summary
        $rows[] = ['SUBJECT SUMMARY', '', '', '', ''];
        $rows[] = ['Subject', 'Total Assessments', 'Average Score', 'Average Grade', 'Credits'];
        
        foreach ($this->data['grades_by_subject'] as $subjectName => $grades) {
            $totalAssessments = $grades->count();
            $averagePercentage = $grades->avg(function($grade) {
                return ($grade->score_obtained / $grade->max_score) * 100;
            });
            $averageGrade = $this->getAverageGrade($averagePercentage);
            $credits = $grades->first()->classSubject->subject->credit_hours ?? 'N/A';
            
            $rows[] = [
                $subjectName,
                $totalAssessments,
                number_format($averagePercentage, 1) . '%',
                $averageGrade,
                $credits
            ];
        }

        return $rows;
    }

    private function getAverageGrade($percentage)
    {
        if ($percentage >= 97) return 'A+';
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 65) return 'D';
        if ($percentage >= 60) return 'D-';
        return 'F';
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            8 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '7C3AED']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            9 => [
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
            'E' => 12,
        ];
    }

    public function title(): string
    {
        return 'Grade Summary';
    }
}

class SubjectGradeSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $subjectName;
    protected $grades;
    protected $student;

    public function __construct($subjectName, $grades, $student)
    {
        $this->subjectName = $subjectName;
        $this->grades = $grades;
        $this->student = $student;
    }

    public function array(): array
    {
        $rows = [];
        
        // Header information
        $rows[] = [$this->subjectName . ' - Detailed Grades', '', '', '', '', ''];
        $rows[] = ['Student:', $this->student->user->first_name . ' ' . $this->student->user->last_name, '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Grades data
        foreach ($this->grades as $grade) {
            $percentage = $grade->max_score > 0 ? round(($grade->score_obtained / $grade->max_score) * 100, 1) : 0;
            $rows[] = [
                ucfirst($grade->assessment_type),
                $grade->recorded_at ? $grade->recorded_at->format('M d, Y') : 'N/A',
                $grade->score_obtained,
                $grade->max_score,
                $percentage . '%',
                $grade->grade_letter,
                $grade->remarks ?: '-'
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Assessment Type',
            'Date',
            'Score Obtained',
            'Max Score',
            'Percentage',
            'Grade',
            'Remarks'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '1F2937']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F3F4F6']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 12,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 8,
            'G' => 20,
        ];
    }

    public function title(): string
    {
        return str_replace(['/', '\\', '?', '*', '[', ']'], '_', $this->subjectName);
    }
}
