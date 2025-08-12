<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\TransactionDetail;
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

    /**
     * ✅ FIXED: Export per TransactionDetail, bukan per Transaction
     * Jadi setiap item detail dapat row sendiri
     */
    public function collection()
    {
        $query = TransactionDetail::with([
            'transaction.createdBy',
            'transaction.approvedBy',
            'itemDetail.item'
        ])->whereHas('transaction', function($q) {
            $q->whereBetween('transaction_date', [
                $this->dateFrom . ' 00:00:00',
                $this->dateTo . ' 23:59:59'
            ]);

            if ($this->transactionType) {
                $q->where('transaction_type', $this->transactionType);
            }
        });

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        $baseHeadings = [
            'No',
            'Transaction Number',
            'Transaction Type',
            'Status',
            'Item Name',        // ✅ Sekarang per item detail
            'Item Code',        // ✅ Sekarang per item detail
            'Serial Number',    // ✅ Individual serial number
            'Status Before',    // ✅ Status item sebelum transaksi
            'Status After',     // ✅ Status item setelah transaksi
            'Created By',
            'Approved By',
            'Transaction Date',
            'Approved Date',
            'Notes'
        ];

        if ($this->format === 'detailed') {
            $baseHeadings = array_merge($baseHeadings, [
                'From Location',
                'To Location',
                'Item Notes'  // Notes specific untuk item ini
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

    /**
     * ✅ FIXED: Map per TransactionDetail
     */
    public function map($transactionDetail): array
    {
        static $no = 0;
        $no++;

        $transaction = $transactionDetail->transaction;
        $itemDetail = $transactionDetail->itemDetail;
        $item = $itemDetail->item ?? null;

        $typeInfo = $transaction->getTypeInfo();
        $statusInfo = $transaction->getStatusInfo();

        $baseData = [
            $no,
            $transaction->transaction_number,
            $typeInfo['text'],
            $statusInfo['text'],
            $item->item_name ?? 'Unknown Item',           // ✅ Nama item spesifik
            $item->item_code ?? 'N/A',                    // ✅ Code item spesifik
            $itemDetail->serial_number ?? 'N/A',         // ✅ Serial number spesifik
            $transactionDetail->status_before ?? '-',     // ✅ Status before per item
            $transactionDetail->status_after ?? '-',      // ✅ Status after per item
            $transaction->createdBy->full_name ?? 'N/A',
            $transaction->approvedBy->full_name ?? '-',
            $transaction->transaction_date->format('Y-m-d H:i:s'),
            $transaction->approved_date ? $transaction->approved_date->format('Y-m-d H:i:s') : '-',
            $transaction->notes ?? '-'
        ];

        if ($this->format === 'detailed') {
            $baseData = array_merge($baseData, [
                $transaction->from_location ?? '-',
                $transaction->to_location ?? '-',
                $transactionDetail->notes ?? '-'  // ✅ Notes per item detail
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
                    'color' => ['rgb' => '000'],
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

                // Add title above headers
                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'TRANSACTION REPORT - DETAILED ITEMS');
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

                $sheet->setCellValue('A3', 'Generated on: ' . now()->format('Y-m-d H:i:s') . ' | Each row = 1 item detail');
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

                // ✅ BONUS: Group rows by transaction number for readability
                $this->addTransactionGrouping($sheet);
            },
        ];
    }

    /**
     * ✅ BONUS: Add visual grouping untuk transaction yang sama
     */
    private function addTransactionGrouping($sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $currentTransactionNumber = '';
        $groupStartRow = 4; // After headers

        for ($row = 4; $row <= $lastRow; $row++) {
            $transactionNumber = $sheet->getCell('B' . $row)->getValue();

            if ($transactionNumber !== $currentTransactionNumber) {
                // New transaction group
                if ($row > 4) {
                    // Add subtle background untuk previous group
                    $groupEndRow = $row - 1;
                    if (($groupStartRow - 4) % 2 == 0) {
                        $sheet->getStyle('A' . $groupStartRow . ':' . $sheet->getHighestColumn() . $groupEndRow)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('F8F9FA');
                    }
                }

                $currentTransactionNumber = $transactionNumber;
                $groupStartRow = $row;
            }
        }

        // Handle last group
        if ($groupStartRow <= $lastRow) {
            if (($groupStartRow - 4) % 2 == 0) {
                $sheet->getStyle('A' . $groupStartRow . ':' . $sheet->getHighestColumn() . $lastRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F8F9FA');
            }
        }
    }
}
