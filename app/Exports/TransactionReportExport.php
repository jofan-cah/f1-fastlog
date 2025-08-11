<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransactionReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $dateFrom;
    protected $dateTo;
    protected $transactionType;
    protected $format;

    public function __construct($dateFrom, $dateTo, $transactionType = null, $format = 'summary')
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->transactionType = $transactionType;
        $this->format = $format;
    }

    public function collection()
    {
        $query = Transaction::with(['item', 'createdBy', 'approvedBy', 'transactionDetails.itemDetail'])
            ->whereBetween('transaction_date', [
                $this->dateFrom . ' 00:00:00',
                $this->dateTo . ' 23:59:59'
            ]);

        if ($this->transactionType) {
            $query->where('transaction_type', $this->transactionType);
        }

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    public function headings(): array
    {
        $baseHeadings = [
            'No',
            'Transaction Number',
            'Type',
            'Status',
            'Item Name',
            'Item Code',
            'Quantity',
            'Created By',
            'Approved By',
            'Transaction Date',
            'Approved Date',
            'Notes'
        ];

        if ($this->format === 'detailed') {
            $baseHeadings = array_merge($baseHeadings, [
                'Serial Numbers',
                'From Location',
                'To Location'
            ]);
        }

        if ($this->format === 'damage_analysis' || $this->transactionType === 'DAMAGED') {
            $baseHeadings = array_merge($baseHeadings, [
                'Damage Level',
                'Damage Reason',
                'Repair Estimate'
            ]);
        }

        return $baseHeadings;
    }

    public function map($transaction): array
    {
        static $no = 0;
        $no++;

        $typeInfo = $transaction->getTypeInfo();
        $statusInfo = $transaction->getStatusInfo();

        $baseData = [
            $no,
            $transaction->transaction_number,
            $typeInfo['text'],
            $statusInfo['text'],
            $transaction->item->item_name ?? 'N/A',
            $transaction->item->item_code ?? 'N/A',
            $transaction->quantity,
            $transaction->createdBy->full_name ?? 'N/A',
            $transaction->approvedBy->full_name ?? '-',
            $transaction->transaction_date->format('Y-m-d H:i:s'),
            $transaction->approved_date ? $transaction->approved_date->format('Y-m-d H:i:s') : '-',
            $transaction->notes ?? '-'
        ];

        if ($this->format === 'detailed') {
            $serialNumbers = $transaction->transactionDetails
                ->pluck('itemDetail.serial_number')
                ->filter()
                ->join(', ');

            $baseData = array_merge($baseData, [
                $serialNumbers ?: '-',
                $transaction->from_location ?? '-',
                $transaction->to_location ?? '-'
            ]);
        }

        if ($this->format === 'damage_analysis' || $this->transactionType === 'DAMAGED') {
            $damageLevels = Transaction::getDamageLevels();
            $damageReasons = Transaction::getDamageReasons();

            $baseData = array_merge($baseData, [
                $transaction->damage_level ? ($damageLevels[$transaction->damage_level] ?? $transaction->damage_level) : '-',
                $transaction->damage_reason ? ($damageReasons[$transaction->damage_reason] ?? $transaction->damage_reason) : '-',
                $transaction->repair_estimate ? $transaction->repair_estimate : '-'
            ]);
        }

        return $baseData;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'], // Red color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set row height for header
                $sheet->getRowDimension('1')->setRowHeight(25);

                // Add borders to all cells
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Center align certain columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
                $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Quantity

                // Add title above headers
                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'TRANSACTION REPORT');
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => 'DC2626'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->setCellValue('A2', 'Period: ' . $this->dateFrom . ' to ' . $this->dateTo);
                if ($this->transactionType) {
                    $types = Transaction::getTransactionTypes();
                    $sheet->setCellValue('A2', $sheet->getCell('A2')->getValue() . ' | Type: ' . ($types[$this->transactionType] ?? $this->transactionType));
                }
                $sheet->mergeCells('A2:' . $lastColumn . '2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => false,
                        'size' => 12,
                        'color' => ['rgb' => '666666'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->setCellValue('A3', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
                $sheet->mergeCells('A3:' . $lastColumn . '3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'bold' => false,
                        'size' => 10,
                        'color' => ['rgb' => '999999'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Set row heights
                $sheet->getRowDimension('1')->setRowHeight(30);
                $sheet->getRowDimension('2')->setRowHeight(20);
                $sheet->getRowDimension('3')->setRowHeight(15);
                $sheet->getRowDimension('4')->setRowHeight(25); // Header row
            },
        ];
    }
}
