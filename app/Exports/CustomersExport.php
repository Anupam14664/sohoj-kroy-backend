<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected $selectedPhones;
    protected $filters;
    protected $customerIdMap;
    protected $reportTitle;

    public function __construct($selectedPhones = null, $filters = [])
    {
        $this->selectedPhones = $selectedPhones;
        $this->filters = $filters;
        $this->customerIdMap = $this->buildCustomerIdMap();
        $this->reportTitle = 'CUSTOMER ORDERS REPORT';
    }

    protected function buildCustomerIdMap()
    {
        $customers = Order::select([
                'phone',
                DB::raw('MIN(name) as name'),
                DB::raw('MIN(created_at) as first_order_at')
            ])
            ->where('status', 'delivered')
            ->groupBy('phone')
            ->orderBy('first_order_at')
            ->get();

        $customerIdMap = [];
        $startingId = 101;

        foreach ($customers as $customer) {
            $customerIdMap[$customer->phone] = $startingId++;
        }

        return $customerIdMap;
    }

    public function collection()
    {
        $query = Order::with(['items.product', 'items.variantOption'])
            ->where('status', 'delivered')
            ->orderBy('created_at', 'desc');

        if ($this->selectedPhones) {
            $query->whereIn('phone', $this->selectedPhones);
        }

        if (!empty($this->filters['phone'])) {
            $query->where('phone', 'like', '%' . $this->filters['phone'] . '%');
        }

        if (!empty($this->filters['district'])) {
            $query->where('district', 'like', '%' . $this->filters['district'] . '%');
        }

        if (!empty($this->filters['thana'])) {
            $query->where('thana', 'like', '%' . $this->filters['thana'] . '%');
        }

        if (!empty($this->filters['search'])) {
            $searchTerm = strtolower($this->filters['search']);
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) like ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('LOWER(phone) like ?', ['%' . $searchTerm . '%']);
            });
        }

        return $query->get();
    }

    public function title(): string
    {
        return 'Customer Orders';
    }

    public function headings(): array
    {
        return [
            [$this->reportTitle], // Main title row
            ['Generated on: ' . now()->format('F j, Y h:i A')], // Date row
            [''], // Empty row for spacing
            [ // Column headers
                'Customer ID',
                'Customer Name',
                'Phone',
                'Address',
                'District',
                'Thana',
                'Order ID',
                'Order Date',
                'Products',
                'Quantity',
                'Item Total',
                'Delivery Charge',
                'Discount',
                'Grand Total',
                'Status'
            ]
        ];
    }

    public function map($order): array
    {
        $customerId = $this->customerIdMap[$order->phone] ?? 'N/A';

        // Group products with their details
        $products = [];
        foreach ($order->items as $item) {
            $products[] = sprintf(
                "%s%s - %d x %s = %s",
                $item->product->name ?? 'N/A',
                $item->variantOption ? ' ('.$item->variantOption->name.')' : '',
                $item->quantity,
                number_format($item->price, 2),
                number_format($item->price * $item->quantity, 2)
            );
        }

        $productList = implode("\n", $products);

        $totalQuantity = $order->items->sum('quantity');

        return [
            $customerId,
            $order->name,
            $order->phone,
            $order->address,
            $order->district,
            $order->thana,
            $order->order_number,
            $order->created_at->format('Y-m-d H:i:s'),
            $productList,
            $totalQuantity,
            number_format($order->items->sum(function($item) {
                return $item->price * $item->quantity;
            }), 2),
            number_format($order->delivery_charge, 2),
            number_format($order->discount, 2),
            number_format($order->total, 2),
            ucfirst($order->status)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Title row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => 'center',
                ]
            ],
            // Date row
            2 => [
                'font' => [
                    'italic' => true,
                ],
                'alignment' => [
                    'horizontal' => 'center',
                ]
            ],
            // Column headers
            4 => [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => 'center',
                ]
            ],
            // Data rows
            'A:O' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => 'top'
                ]
            ],
            'I' => [ // Products column
                'alignment' => [
                    'wrapText' => true,
                ]
            ],
            // Numeric columns
            'K:O' => [
                'alignment' => [
                    'horizontal' => 'right',
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Set column widths
                $event->sheet->getColumnDimension('A')->setWidth(12); // Customer ID
                $event->sheet->getColumnDimension('B')->setWidth(25); // Name
                $event->sheet->getColumnDimension('C')->setWidth(15); // Phone
                $event->sheet->getColumnDimension('D')->setWidth(30); // Address
                $event->sheet->getColumnDimension('E')->setWidth(15); // District
                $event->sheet->getColumnDimension('F')->setWidth(15); // Thana
                $event->sheet->getColumnDimension('G')->setWidth(12); // Order ID
                $event->sheet->getColumnDimension('H')->setWidth(18); // Order Date
                $event->sheet->getColumnDimension('I')->setWidth(40); // Products
                $event->sheet->getColumnDimension('J')->setWidth(10); // Quantity
                $event->sheet->getColumnDimension('K')->setWidth(12); // Item Total
                $event->sheet->getColumnDimension('L')->setWidth(15); // Delivery Charge
                $event->sheet->getColumnDimension('M')->setWidth(12); // Discount
                $event->sheet->getColumnDimension('N')->setWidth(12); // Grand Total
                $event->sheet->getColumnDimension('O')->setWidth(12); // Status

                // Merge title cells
                $event->sheet->mergeCells('A1:O1');
                $event->sheet->mergeCells('A2:O2');

                // Set title row height
                $event->sheet->getRowDimension(1)->setRowHeight(30);
                $event->sheet->getRowDimension(2)->setRowHeight(20);
                $event->sheet->getRowDimension(4)->setRowHeight(25);

                // Style the title row
                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'color' => ['argb' => Color::COLOR_WHITE],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF203764'], // Dark blue background
                    ],
                ]);

                // Style the date row
                $event->sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'italic' => true,
                        'color' => ['argb' => Color::COLOR_WHITE],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'], // Medium blue background
                    ],
                ]);

                // Style the header row
                $event->sheet->getStyle('A4:O4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => Color::COLOR_WHITE],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF9BBB59'], // Green background
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Style data rows
                $lastRow = $event->sheet->getHighestRow();

                // Add borders to all data cells
                $event->sheet->getStyle('A5:O'.$lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD3D3D3'],
                        ],
                    ],
                ]);

                // Alternate row colors
                // for ($i = 5; $i <= $lastRow; $i++) {
                //     $fillColor = $i % 2 == 0 ? 'FFE6E6E6' : 'FFFFFFFF';
                //     $event->sheet->getStyle('A'.$i.':O'.$i)->applyFromArray([
                //         'fill' => [
                //             'fillType' => Fill::FILL_SOLID,
                //             'startColor' => ['argb' => $fillColor],
                //         ],
                //     ]);
                // }

                // Freeze the header row
                $event->sheet->freezePane('A5');

                // Set auto filter
                $event->sheet->setAutoFilter('A4:O4');

                // Add company logo (uncomment if you want to add an image)
                /*
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo');
                $drawing->setPath(public_path('images/logo.png'));
                $drawing->setHeight(50);
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($event->sheet->getDelegate());
                */
            },
        ];
    }
}