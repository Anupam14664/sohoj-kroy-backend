<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\ProductCost;
use App\Models\Order;
use App\Models\OrderItem;
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
    protected $overallTotalRevenue = 0;
    protected $overallTotalBuyPrice = 0;
    protected $overallTotalAdditionalCost = 0;
    protected $overallTotalCost = 0;
    protected $overallTotalProfit = 0;
    protected $dataRows = [];

    public function __construct($from = null, $to = null, $search = null, $selected_ids = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->search = $search;
        $this->selected_ids = $selected_ids;
    }

    public function collection()
    {
        // Start with products query
        $query = Product::with(['costs', 'orderItems.order' => function($q) {
            $q->where('status', 'delivered');
        }]);

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        // Selected products filter - if "all" then no filter
        if ($this->selected_ids && $this->selected_ids !== 'all') {
            $ids = json_decode($this->selected_ids, true);
            if (is_array($ids) && count($ids) > 0) {
                $query->whereIn('id', $ids);
            }
        }

        // Get all products that have either costs or delivered orders in the date range
        if ($this->from && $this->to) {
            $query->where(function($q) {
                $q->whereHas('costs', function($costQuery) {
                    $costQuery->whereBetween('cost_date', [$this->from, $this->to]);
                })
                ->orWhereHas('orderItems.order', function($orderQuery) {
                    $orderQuery->where('status', 'delivered')
                               ->whereBetween('created_at', [$this->from, $this->to]);
                });
            });
        }

        $products = $query->orderBy('name')->get();

        $rows = new Collection();

        foreach ($products as $product) {
            // Get costs for this product within date range
            $costsQuery = $product->costs();
            if ($this->from && $this->to) {
                $costsQuery->whereBetween('cost_date', [$this->from, $this->to]);
            }
            $costs = $costsQuery->get();
            $totalAdditionalCost = $costs->sum('amount');

            // Get delivered orders for this product within date range
            $deliveredOrders = Order::where('status', 'delivered')
                ->whereHas('items', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                });

            if ($this->from && $this->to) {
                $deliveredOrders->whereBetween('created_at', [$this->from, $this->to]);
            }

            $deliveredOrders = $deliveredOrders->get();

            // Calculate product sales with correct pricing logic
            $totalSold = 0;
            $totalRevenue = 0;

            // For Total Buy Price calculation - product buy_price × total quantity sold
            // We need to calculate total quantity sold first
            foreach ($deliveredOrders as $order) {
                foreach ($order->items as $item) {
                    if ($item->product_id == $product->id) {
                        $totalSold += $item->quantity;
                        // Total Revenue: Use the actual price from order item (including discount/variant price)
                        $totalRevenue += ($item->price * $item->quantity);
                    }
                }
            }

            // Total Buy Price = product buy_price × total quantity sold
            $totalBuyPrice = $product->buy_price * $totalSold;

            // Total Cost = Additional Costs (Cost Amount এর যোগফল)
            $totalCost = $totalAdditionalCost;

            // Total Profit = Total Revenue - (Total Buy Price + Total Cost)
            $totalProfit = $totalRevenue - ($totalBuyPrice + $totalCost);

            // Add to overall totals
            $this->overallTotalRevenue += $totalRevenue;
            $this->overallTotalBuyPrice += $totalBuyPrice;
            $this->overallTotalAdditionalCost += $totalAdditionalCost;
            $this->overallTotalCost += $totalAdditionalCost; // Total Cost is just additional costs
            $this->overallTotalProfit += $totalProfit;

            // Only add to export if there are costs OR sales
            if ($costs->count() > 0 || $totalSold > 0) {
                $productRowAdded = false;
                $costRowCount = 0;

                // Add cost details for this product
                foreach ($costs as $cost) {
                    if (!$productRowAdded) {
                        // First row with product details
                        $rows->push([
                            'Product' => $product->name . " (SKU: " . $product->sku . ")",
                            'Cost Type' => $cost->cost_type,
                            'Cost Amount' => number_format($cost->amount, 2),
                            'Details' => $cost->comment ?? '-',
                            'Date' => $cost->cost_date ? date('Y-m-d', strtotime($cost->cost_date)) : '',
                            'Product Price' => number_format($product->buy_price, 2),
                            'Total Sold' => $totalSold,
                            'Total Revenue' => number_format($totalRevenue, 2),
                            'Total Buy Price' => number_format($totalBuyPrice, 2),
                            'Total Cost' => number_format($totalCost, 2),
                            'Total Profit' => number_format($totalProfit, 2),
                        ]);
                        $productRowAdded = true;
                    } else {
                        // Subsequent cost rows without product details
                        $rows->push([
                            'Product' => '',
                            'Cost Type' => $cost->cost_type,
                            'Cost Amount' => number_format($cost->amount, 2),
                            'Details' => $cost->comment ?? '-',
                            'Date' => $cost->cost_date ? date('Y-m-d', strtotime($cost->cost_date)) : '',
                            'Product Price' => '',
                            'Total Sold' => '',
                            'Total Revenue' => '',
                            'Total Buy Price' => '',
                            'Total Cost' => '',
                            'Total Profit' => '',
                        ]);
                    }
                    $costRowCount++;
                }

                // If product has no costs but has sales
                if (!$productRowAdded && $totalSold > 0) {
                    $rows->push([
                        'Product' => $product->name . " (SKU: " . $product->sku . ")",
                        'Cost Type' => '',
                        'Cost Amount' => '',
                        'Details' => '',
                        'Date' => '',
                        'Product Price' => number_format($product->buy_price, 2),
                        'Total Sold' => $totalSold,
                        'Total Revenue' => number_format($totalRevenue, 2),
                        'Total Buy Price' => number_format($totalBuyPrice, 2),
                        'Total Cost' => number_format($totalCost, 2),
                        'Total Profit' => number_format($totalProfit, 2),
                    ]);
                }

                // Add blank row between products
                $rows->push([
                    'Product' => '',
                    'Cost Type' => '',
                    'Cost Amount' => '',
                    'Details' => '',
                    'Date' => '',
                    'Product Price' => '',
                    'Total Sold' => '',
                    'Total Revenue' => '',
                    'Total Buy Price' => '',
                    'Total Cost' => '',
                    'Total Profit' => '',
                ]);
            }
        }

        // Add overall totals row at the end
        if ($this->overallTotalRevenue > 0 || $this->overallTotalCost > 0 || $this->overallTotalProfit != 0) {
            $rows->push([
                'Product' => 'GRAND TOTAL',
                'Cost Type' => '',
                'Cost Amount' => '',
                'Details' => '',
                'Date' => '',
                'Product Price' => '',
                'Total Sold' => '',
                'Total Revenue' => number_format($this->overallTotalRevenue, 2),
                'Total Buy Price' => number_format($this->overallTotalBuyPrice, 2),
                'Total Cost' => number_format($this->overallTotalCost, 2),
                'Total Profit' => number_format($this->overallTotalProfit, 2),
            ]);
        }

        // Store data for later use in registerEvents
        $this->dataRows = $rows->toArray();

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Product',
            'Cost Type',
            'Cost Amount',
            'Details',
            'Date',
            'Product Price',
            'Total Sold',
            'Total Revenue',
            'Total Buy Price',
            'Total Cost',
            'Total Profit',
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

                // Main title
                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue('A1', 'Product Cost & Profit Analysis Report');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFE6E6E6');

                $currentRow = 2;

                // Date range
                if ($this->from || $this->to) {
                    $dateRange = '';
                    if ($this->from && $this->to) {
                        $dateRange = "Date Range: {$this->from} to {$this->to}";
                    } elseif ($this->from) {
                        $dateRange = "From: {$this->from}";
                    } elseif ($this->to) {
                        $dateRange = "To: {$this->to}";
                    }

                    $sheet->mergeCells('A' . $currentRow . ':K' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, $dateRange);
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    $currentRow++;
                }

                // Search term
                if ($this->search) {
                    $sheet->mergeCells('A' . $currentRow . ':K' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, "Search: " . $this->search);
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    $currentRow++;
                }

                // Empty row for spacing
                $currentRow++;

                // Headers
                $headingRow = $currentRow;
                $headings = $this->headings();
                $sheet->fromArray($headings, null, 'A' . $headingRow);

                // Style headers
                $sheet->getStyle("A{$headingRow}:K{$headingRow}")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("A{$headingRow}:K{$headingRow}")->getAlignment()
                      ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                      ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A{$headingRow}:K{$headingRow}")->getFill()
                      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FF2F5496');
                $sheet->getStyle("A{$headingRow}:K{$headingRow}")->getFont()->getColor()->setARGB('FFFFFFFF');

                // Set row height for headers
                $sheet->getRowDimension($headingRow)->setRowHeight(25);

                // Column grouping headers
                $currentRow++;
                $groupHeaderRow = $currentRow;

                // Cost Details Section
                $sheet->mergeCells("A{$groupHeaderRow}:E{$groupHeaderRow}");
                $sheet->setCellValue("A{$groupHeaderRow}", "Cost Details");
                $sheet->getStyle("A{$groupHeaderRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$groupHeaderRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$groupHeaderRow}:E{$groupHeaderRow}")->getFill()
                      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFF2F2F2');

                // Sales & Profit Section
                $sheet->mergeCells("F{$groupHeaderRow}:K{$groupHeaderRow}");
                $sheet->setCellValue("F{$groupHeaderRow}", "Sales & Profit Analysis");
                $sheet->getStyle("F{$groupHeaderRow}")->getFont()->setBold(true);
                $sheet->getStyle("F{$groupHeaderRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$groupHeaderRow}:K{$groupHeaderRow}")->getFill()
                      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFF2F2F2');

                $currentRow++;

                // Add data directly from collection - DO NOT call collection() again
                $dataStartRow = $currentRow;

                // Write data row by row to avoid duplicate data
                foreach ($this->dataRows as $rowData) {
                    $col = 'A';
                    foreach ($rowData as $value) {
                        $sheet->setCellValue($col . $currentRow, $value);
                        $col++;
                    }
                    $currentRow++;
                }

                // Auto size columns with some adjustments
                $sheet->getColumnDimension('A')->setWidth(30); // Product
                $sheet->getColumnDimension('B')->setWidth(20); // Cost Type
                $sheet->getColumnDimension('C')->setWidth(15); // Cost Amount
                $sheet->getColumnDimension('D')->setWidth(25); // Details
                $sheet->getColumnDimension('E')->setWidth(15); // Date
                $sheet->getColumnDimension('F')->setWidth(15); // Product Price
                $sheet->getColumnDimension('G')->setWidth(12); // Total Sold
                $sheet->getColumnDimension('H')->setWidth(15); // Total Revenue
                $sheet->getColumnDimension('I')->setWidth(15); // Total Buy Price
                $sheet->getColumnDimension('J')->setWidth(15); // Total Cost
                $sheet->getColumnDimension('K')->setWidth(15); // Total Profit

                $endRow = $sheet->getHighestRow();

                if ($endRow >= $dataStartRow) {
                    // Apply styling to data rows
                    for ($row = $dataStartRow; $row <= $endRow; $row++) {
                        $productValue = $sheet->getCell('A' . $row)->getValue();
                        $profitValue = $sheet->getCell('K' . $row)->getValue();

                        // Style for product rows (where product name exists and not GRAND TOTAL)
                        if (!empty($productValue) && $productValue != 'GRAND TOTAL') {
                            $sheet->getStyle("A{$row}:K{$row}")->getFill()
                                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                  ->getStartColor()->setARGB('FFF2F2F2');
                            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                        }

                        // Style for GRAND TOTAL row
                        if ($productValue == 'GRAND TOTAL') {
                            $sheet->getStyle("A{$row}:K{$row}")->getFill()
                                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                  ->getStartColor()->setARGB('FF4472C4');
                            $sheet->getStyle("A{$row}:K{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));

                            // Style the 4 total columns
                            $totalColumns = ['H', 'I', 'J', 'K']; // Total Revenue, Total Buy Price, Total Cost, Total Profit
                            foreach ($totalColumns as $col) {
                                $sheet->getStyle($col . $row)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                            }
                        }

                        // Style profit column for individual products (not GRAND TOTAL)
                        if (!empty($profitValue) && $productValue != 'GRAND TOTAL') {
                            $profitNum = floatval(str_replace([',', ' '], '', $profitValue));
                            $color = $profitNum >= 0 ? 'FF00B050' : 'FFFF0000';
                            $sheet->getStyle("K{$row}")->getFont()->setBold(true)->getColor()->setARGB($color);
                        }

                        // Style profit column for GRAND TOTAL
                        if ($productValue == 'GRAND TOTAL') {
                            $profitNum = floatval(str_replace([',', ' '], '', $profitValue));
                            $color = $profitNum >= 0 ? 'FF92D050' : 'FFFFC000'; // Light green or yellow for GRAND TOTAL
                            $sheet->getStyle("K{$row}")->getFont()->setBold(true)->getColor()->setARGB($color);
                        }

                        // Center align numeric columns
                        $numericCols = ['C', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
                        foreach ($numericCols as $col) {
                            $sheet->getStyle($col . $row)->getAlignment()
                                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        }

                        // Left align text columns
                        $textCols = ['A', 'B', 'D'];
                        foreach ($textCols as $col) {
                            $sheet->getStyle($col . $row)->getAlignment()
                                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        }
                    }

                    // Merge cells for product name in multi-cost rows
                    $currentProductStartRow = null;
                    $currentProductName = null;

                    for ($row = $dataStartRow; $row <= $endRow; $row++) {
                        $productName = $sheet->getCell('A' . $row)->getValue();

                        if (!empty($productName) && $productName != 'GRAND TOTAL') {
                            // If we have a previous product with multiple rows, merge the cells
                            if ($currentProductStartRow !== null && $currentProductStartRow < $row - 1) {
                                $mergeRows = $row - $currentProductStartRow;
                                if ($mergeRows > 1) {
                                    // Merge Product cells
                                    $sheet->mergeCells("A{$currentProductStartRow}:A" . ($row - 1));
                                    $sheet->getStyle("A{$currentProductStartRow}")->getAlignment()
                                          ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                                    // Merge other cells as needed
                                    $cellsToMerge = ['F', 'G', 'H', 'I', 'J', 'K'];
                                    foreach ($cellsToMerge as $col) {
                                        $cellValue = $sheet->getCell($col . $currentProductStartRow)->getValue();
                                        if (!empty($cellValue)) {
                                            $sheet->mergeCells("{$col}{$currentProductStartRow}:{$col}" . ($row - 1));
                                            $sheet->getStyle("{$col}{$currentProductStartRow}")->getAlignment()
                                                  ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                        }
                                    }
                                }
                            }

                            // Start new product
                            $currentProductStartRow = $row;
                            $currentProductName = $productName;
                        }
                    }

                    // Merge cells for the last product (excluding GRAND TOTAL)
                    if ($currentProductStartRow !== null && $currentProductStartRow < $endRow) {
                        $lastRow = $endRow;
                        $productName = $sheet->getCell('A' . $lastRow)->getValue();
                        if ($productName == 'GRAND TOTAL') {
                            $lastRow = $endRow - 1;
                        }

                        $productName = $sheet->getCell('A' . $lastRow)->getValue();
                        if (empty($productName) && $lastRow > $currentProductStartRow) {
                            $mergeRows = $lastRow - $currentProductStartRow + 1;
                            if ($mergeRows > 1) {
                                // Merge Product cells
                                $sheet->mergeCells("A{$currentProductStartRow}:A{$lastRow}");
                                $sheet->getStyle("A{$currentProductStartRow}")->getAlignment()
                                      ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                                // Merge other cells as needed
                                $cellsToMerge = ['F', 'G', 'H', 'I', 'J', 'K'];
                                foreach ($cellsToMerge as $col) {
                                    $cellValue = $sheet->getCell($col . $currentProductStartRow)->getValue();
                                    if (!empty($cellValue)) {
                                        $sheet->mergeCells("{$col}{$currentProductStartRow}:{$col}{$lastRow}");
                                        $sheet->getStyle("{$col}{$currentProductStartRow}")->getAlignment()
                                              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                    }
                                }
                            }
                        }
                    }
                }

                // Add borders
                if ($endRow >= $headingRow) {
                    $dataRange = "A{$headingRow}:K{$endRow}";
                    $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                          ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }

                // Add note
                $noteRow = $endRow + 2;
                $sheet->mergeCells("A{$noteRow}:K{$noteRow}");
                $sheet->setCellValue("A{$noteRow}", "Note: Total Revenue = Order Item Price (including discount/variant prices) × Quantity Sold. Total Buy Price = Product Buy Price × Quantity Sold. Total Cost = Sum of Additional Costs. Total Profit = Total Revenue - (Total Buy Price + Total Cost). Based on delivered orders within selected date range.");
                $sheet->getStyle("A{$noteRow}")->getFont()->setItalic(true)->setSize(10);
                $sheet->getStyle("A{$noteRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Add generated timestamp
                $timestampRow = $noteRow + 1;
                $sheet->mergeCells("A{$timestampRow}:K{$timestampRow}");
                $sheet->setCellValue("A{$timestampRow}", "Generated on: " . date('Y-m-d H:i:s'));
                $sheet->getStyle("A{$timestampRow}")->getFont()->setItalic(true)->setSize(9);
                $sheet->getStyle("A{$timestampRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        ];
    }
}
