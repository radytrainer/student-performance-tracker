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
use PhpOffice\PhpSpreadsheet\Style\Border;

class TranscriptExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Main transcript sheet
        $sheets[] = new TranscriptMainSheet($this->data);
        
        // Term-by-term breakdown
        foreach ($this->data['grades_by_term'] as $termName => $grades) {
            $sheets[] = new TermGradesSheet($termName, $grades, $this->data['student']);
        }

        return $sheets;
    }
}

class TranscriptMainSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
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
        
        // Official Header
        $rows[] = ['OFFICIAL ACADEMIC TRANSCRIPT', '', '', '', '', ''];
        $rows[] = ['Student Performance Tracker Institute', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Student Information
        $rows[] = ['STUDENT INFORMATION', '', '', '', '', ''];
        $rows[] = ['Full Name:', strtoupper($student->user->first_name . ' ' . $student->user->last_name), '', '', '', ''];
        $rows[] = ['Student ID:', $student->student_code, '', '', '', ''];
        $rows[] = ['Date of Birth:', $student->date_of_birth ? $student->date_of_birth->format('F d, Y') : 'Not Provided', '', '', '', ''];
        $rows[] = ['Current Class:', $student->currentClass->class_name ?? 'Not Assigned', '', '', '', ''];
        $rows[] = ['Enrollment Date:', $student->enrollment_date ? $student->enrollment_date->format('F d, Y') : 'Not Provided', '', '', '', ''];
        $rows[] = ['Transcript Date:', now()->format('F d, Y'), '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Academic Summary
        $rows[] = ['ACADEMIC SUMMARY', '', '', '', '', ''];
        $rows[] = ['Overall GPA:', number_format($this->data['overall_gpa'], 2), '', '', '', ''];
        $rows[] = ['Total Credits Earned:', $this->data['total_credits'], '', '', '', ''];
        $rows[] = ['Graduation Status:', $this->data['graduation_status'], '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Term-by-Term Summary
        $rows[] = ['ACADEMIC RECORD BY TERM', '', '', '', '', ''];
        $rows[] = ['Term', 'Credits Attempted', 'Credits Earned', 'Term GPA', 'Cumulative GPA', 'Status'];
        
        $cumulativeGPA = 0;
        $totalTerms = count($this->data['grades_by_term']);
        $termIndex = 0;
        
        foreach ($this->data['grades_by_term'] as $termName => $grades) {
            $termIndex++;
            $termCredits = $grades->sum(function($grade) {
                return $grade->classSubject->subject->credit_hours ?? 1;
            });
            
            $termGPA = $this->calculateTermGPA($grades);
            $cumulativeGPA = (($cumulativeGPA * ($termIndex - 1)) + $termGPA) / $termIndex;
            
            $rows[] = [
                $termName,
                $termCredits,
                $termCredits, // Assuming all attempted credits are earned for simplicity
                number_format($termGPA, 2),
                number_format($cumulativeGPA, 2),
                'Completed'
            ];
        }

        $rows[] = ['', '', '', '', '', ''];

        // All Courses Summary
        $rows[] = ['COMPLETE COURSE RECORD', '', '', '', '', ''];
        $rows[] = ['Course Code', 'Course Title', 'Credits', 'Grade', 'Quality Points', 'Term'];
        
        foreach ($this->data['grades_by_term'] as $termName => $grades) {
            foreach ($grades as $grade) {
                $gradePoints = $this->getGradePoints($grade->grade_letter);
                $credits = $grade->classSubject->subject->credit_hours ?? 1;
                $qualityPoints = $gradePoints * $credits;
                
                $rows[] = [
                    $grade->classSubject->subject->subject_code,
                    $grade->classSubject->subject->subject_name,
                    $credits,
                    $grade->grade_letter,
                    number_format($qualityPoints, 2),
                    $termName
                ];
            }
        }

        return $rows;
    }

    private function calculateTermGPA($grades)
    {
        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($grades as $grade) {
            $gradePoints = $this->getGradePoints($grade->grade_letter);
            $credits = $grade->classSubject->subject->credit_hours ?? 1;
            
            $totalPoints += $gradePoints * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? $totalPoints / $totalCredits : 0;
    }

    private function getGradePoints($gradeLetter)
    {
        $gradeScale = [
            'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
            'F' => 0.0
        ];

        return $gradeScale[$gradeLetter] ?? 0;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Official header
            1 => [
                'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Section headers
            4 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '3B82F6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            12 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 12,
            'D' => 12,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function title(): string
    {
        return 'Official Transcript';
    }
}

class TermGradesSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $termName;
    protected $grades;
    protected $student;

    public function __construct($termName, $grades, $student)
    {
        $this->termName = $termName;
        $this->grades = $grades;
        $this->student = $student;
    }

    public function array(): array
    {
        $rows = [];
        
        // Header
        $rows[] = [$this->termName . ' - Detailed Grades', '', '', '', '', ''];
        $rows[] = ['Student:', $this->student->user->first_name . ' ' . $this->student->user->last_name, '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Grades
        foreach ($this->grades as $grade) {
            $percentage = $grade->max_score > 0 ? round(($grade->score_obtained / $grade->max_score) * 100, 1) : 0;
            $rows[] = [
                $grade->classSubject->subject->subject_code,
                $grade->classSubject->subject->subject_name,
                ucfirst($grade->assessment_type),
                $grade->score_obtained . '/' . $grade->max_score,
                $percentage . '%',
                $grade->grade_letter
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Course Code',
            'Course Name',
            'Assessment Type',
            'Score',
            'Percentage',
            'Grade'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '7C3AED']],
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
            'A' => 15,
            'B' => 25,
            'C' => 18,
            'D' => 12,
            'E' => 12,
            'F' => 8,
        ];
    }

    public function title(): string
    {
        return str_replace(['/', '\\', '?', '*', '[', ']'], '_', $this->termName);
    }
}
