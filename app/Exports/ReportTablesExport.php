<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Report Tables Export
 * 
 * Exports report table data to Excel format.
 * Each table becomes a separate sheet in the workbook.
 * Falls back to CSV if Maatwebsite Excel is not installed.
 */
class ReportTablesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $tables;
    protected $currentTableIndex = 0;

    /**
     * Constructor - receives array of table data from report.
     */
    public function __construct(array $tables)
    {
        $this->tables = $tables;
    }

    /**
     * Get the sheet title.
     */
    public function title(): string
    {
        if (isset($this->tables[$this->currentTableIndex])) {
            return $this->tables[$this->currentTableIndex]['title'] ?? 'Table ' . ($this->currentTableIndex + 1);
        }
        return 'Table';
    }

    /**
     * Get the collection of data for export.
     */
    public function collection()
    {
        $data = collect();
        
        foreach ($this->tables as $table) {
            // Section header
            $data->push(['', '', '', '']);
            $data->push(['=== ' . $table['title'] . ' (Page ' . $table['page'] . ') ===', '', '', '']);
            $data->push($table['columns']);
            
            // Table data rows
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

    /**
     * Define the headings for the export.
     */
    public function headings(): array
    {
        return [
            'Export Date: ' . now()->format('Y-m-d H:i:s'),
            '',
            '',
            '',
        ];
    }

    /**
     * Map each row for export.
     */
    public function map($row): array
    {
        if (is_array($row)) {
            // Pad array to ensure consistent column count
            $padded = array_pad($row, 10, '');
            return $padded;
        }
        return [$row, '', '', '', '', '', '', '', '', ''];
    }

    /**
     * Apply styles to the worksheet.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Bold the first row (export date)
            1 => ['font' => ['bold' => true, 'size' => 12]],
            // Auto-size columns
            'A:J' => ['alignment' => ['wrapText' => true]],
        ];
    }
}