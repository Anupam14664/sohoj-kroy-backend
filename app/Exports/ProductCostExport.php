<?php

namespace App\Exports;

use App\Models\ProductCost;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class ProductCostExport implements FromCollection, WithHeadings, WithEvents, WithStartRow
{
    protected $from;
    protected $to;
    protected $search;
    protected $selected_ids;

    public function __construct($from = null, $to = null, $search = null, $selected_ids = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->search = $search;
        $this->selected_ids = $selected_ids;
    }

    public function collection()
    {
        $query = ProductCost::with('product');

        if ($this->selected_ids && $this->selected_ids !== 'all') {
            $ids = json_decode($this->selected_ids, true);
            $query->whereIn('id', $ids);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('product', function ($p) {
                    $p->where('name', 'like', "%{$this->search}%")
                      ->orWhere('sku', 'like', "%{$this->search}%");
                })
                ->orWhere('cost_type', 'like', "%{$this->search}%");
            });
        }

        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }

        $costs = $query->orderBy('product_id')->orderBy('created_at')->get();

        $grouped = $costs->groupBy('product_id');

        $rows = new Collection();

        foreach ($grouped as $productCosts) {
            $first = true;
            $productName = '';
            $productSku = '';
            $productPrice = '';

            foreach ($productCosts as $cost) {
                if ($first) {
                    $productName = $cost->product->name;
                    $productSku = $cost->product->sku;
                    $productPrice = number_format($cost->product_buy_price, 2);
                }

                $rows->push([
                    'Product' => $first ? "{$productName} (SKU: {$productSku})" : '',
                    'Cost Type' => $cost->cost_type,
                    'Cost Amount' => number_format($cost->amount, 2),
                    'Product Price' => $first ? $productPrice : '',
                    'Details' => $cost->comment ?? '-',
                    'Date' => $cost->created_at->format('Y-m-d'),
                ]);
                $first = false;
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Product',
            'Cost Type',
            'Cost Amount',
            'Product Price',
            'Details',
            'Date',
        ];
    }

    public function startRow(): int
    {
        return 1;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->removeRow(1, 100);

                $currentRow = 1;

                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'Product Cost Report');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFE6E6E6');

                $currentRow = 2;

                if ($this->from || $this->to) {
                    $dateRange = '';
                    if ($this->from && $this->to) {
                        $dateRange = "{$this->from} to {$this->to}";
                    } elseif ($this->from) {
                        $dateRange = "From: {$this->from}";
                    } elseif ($this->to) {
                        $dateRange = "To: {$this->to}";
                    }

                    $sheet->setCellValue('A' . $currentRow, 'Date Range:');
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                    $sheet->mergeCells('B' . $currentRow . ':F' . $currentRow);
                    $sheet->setCellValue('B' . $currentRow, $dateRange);
                    $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                    $currentRow++;
                }

                $headingRow = $currentRow;
                $headings = $this->headings();
                $sheet->fromArray($headings, null, 'A' . $headingRow);
                $sheet->getStyle("A{$headingRow}:F{$headingRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$headingRow}:F{$headingRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$headingRow}:F{$headingRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFE6E6E6');

                $currentRow++;

                $data = $this->collection()->toArray();
                if (!empty($data)) {
                    $sheet->fromArray($data, null, 'A' . $currentRow);
                }
                foreach (range('A', 'F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                $dataContentStartRow = $headingRow + 1;
                $endRow = $sheet->getHighestRow();

                if ($endRow >= $dataContentStartRow) {
                    $groups = [];
                    $currentGroup = null;
                    for ($row = $dataContentStartRow; $row <= $endRow; $row++) {
                        $productValue = $sheet->getCell('A' . $row)->getValue();
                        $priceValue = $sheet->getCell('D' . $row)->getValue();
                        if (!empty($productValue)) {
                            if ($currentGroup !== null) {
                                $groups[] = $currentGroup;
                            }
                            $currentGroup = [
                                'start' => $row,
                                'end' => $row,
                                'hasProduct' => true,
                                'hasPrice' => !empty($priceValue)
                            ];
                        } else {
                            if ($currentGroup !== null) {
                                $currentGroup['end'] = $row;
                                if (!empty($priceValue)) {
                                    $currentGroup['hasPrice'] = true;
                                }
                            }
                        }
                    }
                    if ($currentGroup !== null) {
                        $groups[] = $currentGroup;
                    }
                    foreach ($groups as $group) {
                        $start = $group['start'];
                        $end = $group['end'];
                        if ($end > $start) {
                            $sheet->mergeCells("A{$start}:A{$end}");
                            $sheet->getStyle("A{$start}:A{$end}")->getAlignment()
                                  ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                            $sheet->getStyle("A{$start}")->getFont();
                        }
                        if ($end > $start && $group['hasPrice']) {
                            $sheet->mergeCells("D{$start}:D{$end}");
                            $sheet->getStyle("D{$start}:D{$end}")->getAlignment()
                                  ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        }
                    }
                    for ($row = $dataContentStartRow; $row <= $endRow; $row++) {
                        $sheet->getStyle('C' . $row)->getAlignment()
                              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('D' . $row)->getAlignment()
                              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('F' . $row)->getAlignment()
                              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }
                }
                if ($endRow >= $headingRow) {
                    $dataRange = "A{$headingRow}:F{$endRow}";
                    $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                          ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
            }
        ];
    }
}
