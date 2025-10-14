<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithTitle
{
    protected $orders;
    protected $status;
    protected $dateFrom;
    protected $dateTo;

    // Custom status display names
    protected $statusNames = [
        'pending' => 'Pending',
        'hold' => 'On Hold',
        'processing' => 'Order Confirmed',
        'shipped' => 'Ready to Ship',
        'courier_delivered' => 'Courier Delivered',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'all' => 'All Orders'
    ];

    public function __construct($orders, $status = 'all', $dateFrom = null, $dateTo = null)
    {
        $this->orders = $orders;
        $this->status = $status;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    // Set sheet name based on status
    public function title(): string
    {
        return $this->statusNames[$this->status] ?? 'Orders';
    }

    public function collection()
    {
        return $this->orders->load(['items.product', 'courier']);
    }

    public function headings(): array
    {
        return [
            ['Status:', $this->statusNames[$this->status] ?? 'All Orders'],
            ['Date Range:', $this->getDateRangeText()],
            ['Exported On:', now()->format('Y-m-d H:i:s')],
            [], // Empty row for spacing
            [ // Column headers
                'Order Date',
                'Order Number',
                'Customer Name',
                'Phone',
                'Address',
                'District',
                'Thana',
                'Delivery Charge',
                'Discount',
                'Total Amount',
                'Tracking ID',
                'Products (Name, Qty, Price)',
                'Size',
                'Color'
            ]
        ];
    }

    public function map($order): array
    {
        $productsInfo = [];
        $sizesInfo = [];
        $colorsInfo = [];

        foreach ($order->items as $item) {
            $productLine = $item->product_name;
            $productLine .= ' (Qty: '.$item->quantity;
            $productLine .= ', Price: '.$item->price.')';
            $productsInfo[] = $productLine;

            $sizesInfo[] = $item->size_name ?: 'N/A';
            $colorsInfo[] = $item->color_name ?: 'N/A';
        }

        // Only show tracking ID for these statuses
        $trackingId = in_array($order->status, ['shipped', 'courier_delivered', 'delivered'])
            ? $order->tracking_code ?? 'N/A'
            : '';

        return [
            $order->created_at->format('Y-m-d H:i:s'),
            $order->order_number,
            $order->name,
            $order->phone,
            $order->address,
            $order->district,
            $order->thana,
            $order->delivery_charge,
            $order->discount,
            $order->total,
            $trackingId,
            implode("\n", $productsInfo),
            implode("\n", $sizesInfo),
            implode("\n", $colorsInfo)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge title cells
        $sheet->mergeCells('B1:N1');
        $sheet->mergeCells('B2:N2');
        $sheet->mergeCells('B3:N3');

        return [
            // Title rows style
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6']
                ]
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6']
                ]
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6']
                ]
            ],
            // Column headers style (row 5)
            5 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3490dc']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ],
            // Data rows
            'A:N' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Set column widths
                $event->sheet->getColumnDimension('A')->setWidth(20); // Order Date
                $event->sheet->getColumnDimension('B')->setWidth(15); // Order Number
                $event->sheet->getColumnDimension('C')->setWidth(25); // Customer Name
                $event->sheet->getColumnDimension('D')->setWidth(15); // Phone
                $event->sheet->getColumnDimension('E')->setWidth(30); // Address
                $event->sheet->getColumnDimension('F')->setWidth(15); // District
                $event->sheet->getColumnDimension('G')->setWidth(15); // Thana
                $event->sheet->getColumnDimension('K')->setWidth(20); // Tracking ID
                $event->sheet->getColumnDimension('L')->setWidth(40); // Products
                $event->sheet->getColumnDimension('M')->setWidth(15); // Size
                $event->sheet->getColumnDimension('N')->setWidth(15); // Color

                // Add hyperlinks to tracking IDs
                $highestRow = $event->sheet->getHighestRow();
                for ($row = 6; $row <= $highestRow; $row++) {
                    $trackingCell = 'K'.$row;
                    $trackingId = $event->sheet->getCell($trackingCell)->getValue();

                    if ($trackingId && $trackingId !== 'N/A') {
                        $event->sheet->getCell($trackingCell)->setHyperlink(
                            new Hyperlink("https://steadfast.com.bd/t/{$trackingId}", $trackingId)
                        );
                        $event->sheet->getStyle($trackingCell)->applyFromArray([
                            'font' => [
                                'color' => ['rgb' => '0000FF'],
                                'underline' => 'single'
                            ]
                        ]);
                    }
                }
            }
        ];
    }

    protected function getDateRangeText(): string
    {
        if ($this->dateFrom && $this->dateTo) {
            return $this->dateFrom . ' to ' . $this->dateTo;
        }
        if ($this->dateFrom) {
            return 'From ' . $this->dateFrom;
        }
        if ($this->dateTo) {
            return 'Until ' . $this->dateTo;
        }
        return 'All Dates';
    }
}
