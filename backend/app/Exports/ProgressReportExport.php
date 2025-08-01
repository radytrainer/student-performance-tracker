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

class ProgressReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $student = $this->data['student'];
        $progressByTerm = $this->data['progress_by_term'] ?? [];
        $achievements = $this->data['achievements'] ?? [];
        
        $rows = [];
        
        // Header Section
        $rows[] = ['ACADEMIC PROGRESS REPORT', '', '', '', '', ''];
        $rows[] = ['Name:', $student->user->first_name . ' ' . $student->user->last_name, '', '', '', ''];
        $rows[] = ['Student ID:', $student->student_code, '', '', '', ''];
        $rows[] = ['Class:', $student->currentClass->class_name ?? 'Not Assigned', '', '', '', ''];
        $rows[] = ['Period:', ucfirst(str_replace('_', ' ', $this->data['period'])), '', '', '', ''];
        $rows[] = ['Generated:', now()->format('F d, Y H:i'), '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Progress by Term Section
        if (!empty($progressByTerm)) {
            $rows[] = ['PROGRESS BY TERM', '', '', '', '', ''];
            $rows[] = ['Term', 'GPA', 'Attendance %', 'Total Assessments', 'Days Present', 'Total Days'];
            
            foreach ($progressByTerm as $termData) {
                $rows[] = [
                    $termData['term']->term_name,
                    number_format($termData['gpa'], 2),
                    $termData['attendance']['percentage'] . '%',
                    $termData['grades_count'],
                    $termData['attendance']['present_days'],
                    $termData['attendance']['total_days']
                ];
            }
            $rows[] = ['', '', '', '', '', ''];
        }

        // Achievements Section
        if (!empty($achievements)) {
            $rows[] = ['ACHIEVEMENTS & HIGHLIGHTS', '', '', '', '', ''];
            $rows[] = ['Achievement', 'Description', '', '', '', ''];
            
            foreach ($achievements as $achievement) {
                $rows[] = [
                    $achievement['title'],
                    $achievement['description'],
                    '',
                    '',
                    '',
                    ''
                ];
            }
            $rows[] = ['', '', '', '', '', ''];
        }

        // Overall Summary
        $rows[] = ['OVERALL SUMMARY', '', '', '', '', ''];
        $rows[] = ['Current GPA:', number_format($this->data['gpa'] ?? 0, 2), '', '', '', ''];
        $rows[] = ['Current Attendance:', ($this->data['attendance']['percentage'] ?? 0) . '%', '', '', '', ''];
        $rows[] = ['Class Rank:', ($this->data['class_rank']['rank'] ?? 0) . '/' . ($this->data['class_rank']['total'] ?? 0), '', '', '', ''];

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
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '8B5CF6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            8 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '059669']],
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
            'D' => 18,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function title(): string
    {
        return 'Progress Report';
    }
}
