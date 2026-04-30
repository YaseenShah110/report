<?php
// app/Exports/ReportTablesExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportTablesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $tables;

    public function __construct(array $tables)
    {
        $this->tables = $tables;
    }

    public function collection()
    {
        $data = collect();
        
        foreach ($this->tables as $table) {
            $data->push(['', '=== ' . $table['title'] . ' (Page ' . $table['page'] . ') ===', '', '']);
            $data->push($table['columns']);
            
            foreach ($table['data'] as $row) {
                $rowData = [];
                foreach ($table['columns'] as $col) {
                    $rowData[] = $row[$col] ?? '';
                }
                $data->push($rowData);
            }
            
            $data->push(['', '', '', '']);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return ['Export Date: ' . now()->format('Y-m-d H:i:s'), '', '', ''];
    }

    public function map($row): array
    {
        if (is_array($row)) {
            return $row;
        }
        return (array) $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}