<?php

namespace App\Http\Controllers\Admin;

use id;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use App\Models\CourierService;
use App\Models\DeliveryOption;
use App\Models\GeneralSetting;
use Illuminate\Support\Carbon;
use App\Models\BlockedCustomer;
// use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CustomersExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ProductVariantOption;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Mpdf\Mpdf;
use Mpdf\Config\FontVariables;
use Mpdf\Config\ConfigVariables;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items', 'coupon', 'items.variantOption',  'deliveryOption' ])
            ->latest();


        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }


        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('district')) {
            $query->where('district', 'like', '%' . $request->district . '%');
        }

        if ($request->filled('thana')) {
            $query->where('thana', 'like', '%' . $request->thana . '%');
        }

        if ($request->filled('product_search')) {
            $searchTerm = $request->product_search;

            $query->whereHas('items.product', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $searchTerm . '%');
            });
        }

        $orders = $query->paginate(10);

        $status = $request->status ?? 'all';
        $dateFrom = $request->date_from ?? '';
        $dateTo = $request->date_to ?? '';
        $district = $request->district ?? '';
        $thana = $request->thana ?? '';
        $productSearch = $request->product_search ?? '';


        $couriers = CourierService::all();
        return view('admin.pages.orders.index', compact(
            'orders', 'status', 'dateFrom', 'dateTo', 'district', 'thana', 'productSearch', 'couriers'
        ));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:incomplete,pending,hold,processing,shipped,courier_delivered,delivered,cancelled',
            'courier_service_id' => 'nullable|required_if:status,shipped|exists:courier_services,id',
            'delivery_note' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'custom_link' => 'nullable|url|max:255',
        ]);

        $allowedTransitions = [
            'incomplete' => ['pending', 'hold', 'processing', 'cancelled'],
            'pending' => ['hold', 'processing', 'cancelled'],
            'hold' => ['processing', 'cancelled'],
            'processing' => ['shipped','courier_delivered', 'cancelled'],
            'shipped' => ['courier_delivered', 'cancelled'],
            'courier_delivered' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];


        $currentStatus = $order->status;
        $newStatus = $validated['status'];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return back()->with('error', 'Invalid status transition from '.$currentStatus.' to '.$newStatus);
        }

        DB::beginTransaction();

        if ($validated['status'] === 'courier_delivered' || $validated['status'] === 'delivered') {
            $order->custom_link = $request->custom_link ?? $order->custom_link;
        }

        try {
            if ($validated['status'] === 'shipped' && $order->status === 'shipped') {
                throw new \Exception('This order is already marked as shipped.');
            }

            if ($validated['status'] === 'cancelled' && $order->status !== 'cancelled') {
                $order->returnStock();
            }

            $order->status = $validated['status'];
            if (isset($validated['comment'])) {
                $order->comment = $validated['comment'];
            }


            if ($validated['status'] === 'shipped') {
                $courier = CourierService::findOrFail($validated['courier_service_id']);

                $payload = [
                    'invoice' => $order->order_number,
                    'recipient_name' => $order->name,
                    'recipient_phone' => $order->phone,
                    'recipient_address' => $order->address . ', ' . $order->thana . ', ' . $order->district,
                    'cod_amount' => $order->total,
                    'note' => $validated['delivery_note'] ?? 'Handle with care',
                    'item_description' => 'N/A',
                    'delivery_type' => 0,
                ];

                $response = Http::withHeaders([
                    'Api-Key' => $courier->api_key,
                    'Secret-Key' => $courier->secret_key,
                    'Content-Type' => 'application/json',
                ])->post($courier->base_url . '/' . $courier->create_order_endpoint, $payload);

                $data = $response->json();

                if (!$response->successful() || !isset($data['consignment']['tracking_code'])) {
                    throw new \Exception('Courier API Error: ' . ($data['message'] ?? 'Unknown error'));
                }

                $order->courier_service_id = $courier->id;
                $order->tracking_code = $data['consignment']['tracking_code'];
                $order->consignment_id = $data['consignment']['consignment_id'];
                $order->courier_response = $data;
            }

            $order->save();
            DB::commit();

            return back()->with('success', 'Order updated successfully.' .
                ($order->tracking_code ? ' Tracking: ' . $order->tracking_code : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order status update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }




    public function edit(Order $order)
    {
        $order->load(['items.product', 'items.variantOption', 'coupon', 'deliveryOption']);
        $couriers = CourierService::all();
        $deliveryOptions = DeliveryOption::where('is_active', true)->get();

        return view('admin.pages.orders.edit', compact('order', 'couriers', 'deliveryOptions'));
    }


    public function destroy($id)
{
    $order = Order::findOrFail($id);

    if ($order->status !== 'cancelled') {
        return redirect()->back()->with('error', 'Only cancelled orders can be deleted.');
    }

    $order->delete();

    return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
}

    public function show(Order $order)
    {
        $order->load(['items.product', 'coupon']);
        $couriers = CourierService::where('is_active', true)->get();

        return view('admin.pages.orders.show', compact('order', 'couriers'));
    }

    public function customerList(Request $request)
    {
        $customerBase = Order::select([
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

        foreach ($customerBase as $customer) {
            $customerIdMap[$customer->phone] = $startingId++;
        }

        $query = Order::select([
                'orders.name',
                'orders.phone',
                DB::raw('MIN(orders.address) as primary_address'),
                DB::raw('MIN(orders.district) as district'),
                DB::raw('MIN(orders.thana) as thana'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                DB::raw('SUM(order_items.quantity) as total_products'),
                DB::raw('SUM(orders.total) as total_spent'),
                DB::raw('MAX(orders.created_at) as last_order_at')
            ])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('product_variant_options', 'product_variants.id', '=', 'product_variant_options.variant_id')
            ->where('orders.status', 'delivered')
            ->groupBy('orders.name', 'orders.phone');

        // Filters
        if ($request->filled('phone')) {
            $query->where('orders.phone', 'like', '%' . $request->phone . '%');
        }
        if ($request->filled('district')) {
            $query->where('orders.district', 'like', '%' . $request->district . '%');
        }
        if ($request->filled('thana')) {
            $query->where('orders.thana', 'like', '%' . $request->thana . '%');
        }

        // Search
        if ($request->filled('search')) {
            $searchTerm = strtolower($request->search);

            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(orders.name) LIKE ?', ["%{$searchTerm}%"])
                ->orWhereRaw('LOWER(orders.phone) LIKE ?', ["%{$searchTerm}%"])
                ->orWhereIn('orders.phone', function($sub) use ($searchTerm) {
                    $sub->select('orders.phone')
                        ->from('orders')
                        ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                        ->join('products', 'order_items.product_id', '=', 'products.id')
                        ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
                        ->leftJoin('product_variant_options', 'product_variants.id', '=', 'product_variant_options.variant_id')
                        ->whereRaw('LOWER(products.name) LIKE ?', ["%{$searchTerm}%"])
                        ->orWhereRaw('LOWER(products.sku) LIKE ?', ["%{$searchTerm}%"])
                        ->orWhereRaw('LOWER(product_variant_options.sku) LIKE ?', ["%{$searchTerm}%"]);
                });
            });
        }

        $allCustomers = $query->orderBy('orders.phone')->get();

        $customersWithIds = $allCustomers->map(function ($customer) use ($customerIdMap) {
            return [
                'customer_id' => $customerIdMap[$customer->phone] ?? null,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'district' => $customer->district,
                'thana' => $customer->thana,
                'primary_address' => $customer->primary_address,
                'order_count' => $customer->order_count,
                'total_products' => $customer->total_products,
                'total_spent' => number_format($customer->total_spent, 2),
                'last_order_at' => Carbon::parse($customer->last_order_at)->format('M d, Y'),
            ];
        })->filter(function ($customer) {
            return !is_null($customer['customer_id']);
        });

        $customersWithIds = $customersWithIds->sortBy('customer_id')->values();

        // Pagination
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $currentPageItems = $customersWithIds->slice(($page - 1) * $perPage, $perPage)->values();

        $customers = new LengthAwarePaginator(
            $currentPageItems,
            $customersWithIds->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('admin.pages.customers.index', [
            'customers' => $customers,
            'phone' => $request->phone,
            'district' => $request->district,
            'thana' => $request->thana,
            'search' => $request->search,
        ]);
    }



    public function customerOrdersDetail($phone)
    {
        // Fetch delivered orders for the customer
        $orders = Order::where('phone', $phone)
            ->where('status', 'delivered')
            ->with(['items.product', 'items.variantOption.variant.color', 'items.variantOption.size'])
            ->orderBy('created_at', 'desc')
            ->get();

        $customerName = $orders->first()?->name ?? 'Unknown Customer';

        return view('admin.pages.customers.orders_detail', compact('orders', 'customerName', 'phone'));
    }

    public function download($id)
    {
        $order = Order::with(['items.product', 'deliveryOption', 'courier'])->findOrFail($id);
        $courier = CourierService::all();

        $generalSettings = GeneralSetting::first();
        $order->company_name = $generalSettings->app_name ?? 'Company Name';
        $order->company_phone = $generalSettings->contact_number_1 ?? 'N/A';

        $html = view('admin.layouts.invoice', compact('order','courier'))->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
                    'default_font' => 'solaimanlipi',
                'fontDir' => array_merge($fontDirs, [
                    public_path('assets/admin/fonts'),
                ]),
                'fontdata' => $fontData + [
                    'solaimanlipi' => [
                        'R' => 'SolaimanLipi.ttf',
                        'useOTL' => 0xFF,
                    ]
                ],
                'default_font_size' => 9,
                'margin_left' => 3,
                'margin_right' => 3,
                'margin_top' => 3,
                'margin_bottom' => 3,
                'margin_header' => 0,
                'margin_footer' => 0,
            ]);

            $mpdf->WriteHTML($html);

            return $mpdf->Output('order-'.$order->order_number.'.pdf', 'D');
    }
public function update(Request $request, Order $order)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'required|string|max:1000',
        'district' => 'required|string',
        'thana'    => 'required|string',
        'admin_comment'    => 'nullable|string',
    ]);

    $order->update([
        'name' => $request->name,
        'phone' => $request->phone,
        'address' => $request->address,
        'district' => $request->district,
        'thana'    => $request->thana,
        'admin_comment'    => $request->admin_comment,
    ]);

    return redirect()
        ->route('admin.orders.edit', $order)
        ->with('success', 'Customer information updated successfully.');
}

public function updateDeliveryCharge(Request $request, Order $order)
{
    $request->validate([
        'delivery_option_id' => 'required|exists:delivery_options,id',
        'delivery_charge' => 'required|numeric|min:0',
        'admin_discount' => 'required|numeric|min:0',
    ]);

    $order->update([
    'delivery_option_id' => $request->delivery_option_id,
    'delivery_charge' => $request->delivery_charge,
    'admin_discount' => $request->admin_discount,
    'total' => max(0, $order->subtotal - $order->discount - $request->admin_discount + $request->delivery_charge),
]);

    return back()->with('success', 'Delivery information updated successfully.');
}

public function updateItems(Request $request, Order $order)
{
    // Remove deleted items
    if($request->has('removed_ids')){
        OrderItem::whereIn('id',$request->removed_ids)->where('order_id',$order->id)->delete();
    }

    if($request->has('items')){
        foreach($request->items as $itemData){
            if(!empty($itemData['id'])){
                $item = OrderItem::find($itemData['id']);
                if($item){
                    $item->quantity = $itemData['quantity'] ?? $item->quantity;
                    $item->save();
                }
            } else {
                $product = Product::find($itemData['product_id']);
                $variant = isset($itemData['variant_option_id']) ? ProductVariantOption::find($itemData['variant_option_id']) : null;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $variant?->price ?? ($product->discount_price ?? $product->regular_price),
                    'quantity' => $itemData['quantity'] ?? 1,
                    'variant_option_id' => $variant?->id,
                    'size_name' => $variant?->size->name ?? $product->size?->name ?? null,
                    'color_name' => $variant?->variant->color->name ?? null,
                ]);
            }
        }
    }

    $this->recalculateOrderTotals($order);

    return back()->with('success','Order items updated successfully.');
}


protected function recalculateOrderTotals(Order $order)
{
    $subtotal = $order->items()->sum(DB::raw('price * quantity'));
    $order->subtotal = $subtotal;
    $order->total = ($subtotal - $order->discount) + $order->delivery_charge;
    $order->save();
}

public function skuSearch(Request $request)
{
    $query = $request->input('query');

    $products = Product::where('sku', 'like', "%$query%")
        ->select('id', 'sku', 'name', 'regular_price', 'discount_price')
        ->limit(5)
        ->get()
        ->map(function ($product) {
            return [
                'type' => 'product',
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => $product->discount_price ?? $product->regular_price,
                'has_variants' => $product->has_variants
            ];
        });

    $variants = ProductVariantOption::where('sku', 'like', "%$query%")
        ->with(['variant.product', 'variant.color', 'size'])
        ->select('id', 'sku', 'variant_id', 'price', 'size_id')
        ->limit(5)
        ->get()
        ->map(function ($variant) {
            return [
                'type' => 'variant',
                'id' => $variant->id,
                'sku' => $variant->sku,
                'name' => $variant->variant->product->name,
                'product_id' => $variant->variant->product->id,
                'price' => $variant->price,
                'color' => $variant->variant->color->name ?? null,
                'color_code' => $variant->variant->color->code ?? null,
                'size' => $variant->size->name ?? null,
                'has_variants' => true
            ];
        });

    return response()->json([...$products, ...$variants]);
}


public function create()
{
    $deliveryOptions = DeliveryOption::where('is_active', true)->get();
    return view('admin.pages.orders.create', compact('deliveryOptions'));
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'phone' => 'required|string',
        'district' => 'required|string',
        'thana' => 'required|string',
        'address' => 'nullable|string',
        'delivery_option_id' => 'required|exists:delivery_options,id',
        'products' => 'required|array',
    ]);

    DB::beginTransaction();

    try {
        $delivery = DeliveryOption::find($request->delivery_option_id);
        $subtotal = 0;

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'name' => $request->name,
            'phone' => $request->phone,
            'district' => $request->district,
            'thana' => $request->thana,
            'address' => $request->address,
            'delivery_charge' => $delivery->charge,
            'subtotal' => 0,
            'total' => 0,
            'status' => 'pending',
        ]);

        foreach ($request->products as $item) {
            $product = Product::find($item['product_id']);
            $variant = isset($item['variant_option_id']) ? ProductVariantOption::find($item['variant_option_id']) : null;

            $price = $variant ? $variant->price : $product->discount_price ?? $product->regular_price;
            $subtotal += $price * $item['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $price,
                'quantity' => $item['quantity'],
                'size_name' => $variant?->size?->name,
                'color_name' => $variant?->variant?->color?->name,
                'variant_option_id' => $variant?->id,
            ]);

            if ($variant) {
                $variant->decrement('stock', $item['quantity']);
                $product->updateStock();
            } else {
                $product->decrement('total_stock', $item['quantity']);
            }
        }

        $order->subtotal = $subtotal;
        $order->total = $subtotal + $delivery->charge;
        $order->save();

        DB::commit();
        return redirect()->route('admin.orders.index')->with('success', 'Order created successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed: ' . $e->getMessage());
    }
}


public function search(Request $request)
{
    $keyword = $request->q;

    if (!$keyword) {
        return response()->json([]);
    }

    $variantMatch = ProductVariantOption::where('sku', $keyword)->with('variant.product')->first();

    if ($variantMatch) {
        $product = $variantMatch->variant->product;
        return response()->json([
            [
                'type' => 'variant',
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $variantMatch->sku,
                'price' => $variantMatch->price,
                'has_variants' => true,
            ]
        ]);
    }

    $products = Product::where('name', 'LIKE', "%$keyword%")
        ->orWhere('sku', 'LIKE', "%$keyword%")
        ->limit(10)
        ->get()
        ->map(function ($product) {
            return [
                'type' => 'product',
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->discount_price ?? $product->regular_price,
                'has_variants' => $product->has_variants,
            ];
        });

    return response()->json($products);
}


public function getVariants(Product $product)
{
    $variants = $product->variants()
        ->with(['color', 'options.size'])
        ->get()
        ->flatMap(function ($variant) {
            return $variant->options->map(function ($option) use ($variant) {
                return [
                    'id' => $option->id,
                    'sku' => $option->sku,
                    'price' => $option->price,
                    'color' => $variant->color->name ?? null,
                    'size' => $option->size->name ?? null,
                ];
            });
        });

    return response()->json($variants);
}

    public function shippedOrders(Request $request)
    {
        $query = Order::with([
                'items',
                'coupon',
                'items.variantOption',
                'deliveryOption',
                'courier'
            ])
            ->whereIn('status', ['shipped', 'courier_delivered'])
            ->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('courier_service_id')) {
            $query->where('courier_service_id', $request->courier_service_id);
        }

        if ($request->filled('tracking_code')) {
            $query->where('tracking_code', 'like', '%' . $request->tracking_code . '%');
        }

        $orders = $query->paginate(10);

        // Live fetch delivery status for each order
        foreach($orders as $order) {
            $status = getDeliveryStatus($order);
            $order->delivery_status_code = $status['code'];
            $order->delivery_status_description = $status['description'];
        }

        $dateFrom = $request->date_from ?? '';
        $dateTo = $request->date_to ?? '';
        $courierServiceId = $request->courier_service_id ?? '';
        $trackingCode = $request->tracking_code ?? '';

        $couriers = CourierService::all();

        return view('admin.pages.orders.courier_order', compact(
            'orders',
            'dateFrom',
            'dateTo',
            'courierServiceId',
            'trackingCode',
            'couriers'
        ));
    }




public function blockedCustomers(Request $request)
{
    $query = BlockedCustomer::query()->latest();

    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $query->where(function($q) use ($searchTerm) {
            $q->where('phone', 'like', '%' . $searchTerm . '%')
              ->orWhere('ip_address', 'like', '%' . $searchTerm . '%');
        });
    }

    $blockedCustomers = $query->paginate(10)
        ->appends(['search' => $request->search]);

    return view('admin.pages.customers.customer_block', [
        'blockedCustomers' => $blockedCustomers,
        'search' => $request->search
    ]);
}
public function blockCustomer(Request $request)
{
    $request->validate([
        'phone' => 'required_without:ip_address',
        'ip_address' => 'required_without:phone',
        'reason' => 'nullable|string'
    ]);

    BlockedCustomer::create([
        'phone' => $request->phone,
        'ip_address' => $request->ip_address,
        'reason' => $request->reason,
    ]);

    return back()->with('success', 'Customer blocked successfully');
}

public function unblockCustomer(Request $request)
{
    $request->validate([
        'id' => 'required|exists:blocked_customers'
    ]);

    BlockedCustomer::find($request->id)->delete();

    return back()->with('success', 'Customer unblocked successfully');
}
public function export(Request $request)
{
    if ($request->has('all_filtered')) {
        // সার্ভার সাইডে ফিল্টার করা সব অর্ডার
        $orders = Order::query()
            ->whereIn('status', ['processing', 'shipped', 'courier_delivered', 'delivered'])
            ->when($request->status !== 'all', fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->district, fn($q) => $q->where('district', $request->district))
            ->when($request->thana, fn($q) => $q->where('thana', $request->thana))
            ->with(['items.product', 'courier'])
            ->orderBy('created_at', 'desc')
            ->get();
    } else {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
        ]);

        $orders = Order::whereIn('id', $request->order_ids)
            ->with(['items.product', 'courier'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    if ($orders->isEmpty()) {
        return back()->with('error', 'No valid orders selected for export.');
    }

    $fileName = 'orders-export-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

    $status = $request->status ?? 'all';

return Excel::download(
    new OrdersExport($orders, $status, $request->date_from, $request->date_to),
    'orders-export-' . now()->format('Y-m-d-H-i-s') . '.xlsx'
);

}



public function bulkDelete(Request $request)
{
    $request->validate([
        'order_ids' => 'required|array|min:1',
        'order_ids.*' => 'exists:orders,id',
    ]);

    $orders = Order::whereIn('id', $request->order_ids)
        ->whereIn('status', ['cancelled', 'incomplete'])
        ->get();

    if ($orders->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'Only cancelled or incomplete orders can be deleted.'
        ], 400);
    }

    $deletedCount = 0;
    foreach ($orders as $order) {
        try {
            $order->delete();
            $deletedCount++;
        } catch (\Exception $e) {
            continue;
        }
    }

    return response()->json([
        'status' => true,
        'message' => "Successfully deleted $deletedCount order(s)."
    ]);
}


public function exportCustomers(Request $request)
{
    $exportType = $request->export_type;
    $filters = [
        'phone' => $request->phone_filter,
        'district' => $request->district_filter,
        'thana' => $request->thana_filter,
        'search' => $request->search_filter,
    ];

    if ($exportType === 'selected') {
        $selectedPhones = $request->selected_customers ?? [];
        return Excel::download(new CustomersExport($selectedPhones, $filters), 'selected_customers.xlsx');
    }

    return Excel::download(new CustomersExport(null, $filters), 'all_customers.xlsx');
}

    public function incompleteOrders(Request $request)
    {
        $orders = Order::where('status', 'incomplete')
            ->with('items', 'deliveryOption')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.pages.orders.incomplete_order', compact('orders'));
    }

    public function exportOrder(Request $request)
    {
        $query = Order::with(['items.product', 'items.variantOption.variant.color', 'deliveryOption', 'courier']);

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['processing','shipped','courier_delivered','delivered']);
        }

        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);

        if ($request->has('order_ids') && count($request->order_ids) > 0) {
            $query->whereIn('id', $request->order_ids);
        }

        $orders = $query->get();
        $courier = CourierService::all();
        $generalSettings = GeneralSetting::first();

        // Assign company info
        foreach ($orders as $order) {
            $order->company_name = $generalSettings->app_name ?? 'Company Name';
            $order->company_phone = $generalSettings->contact_number_1 ?? 'N/A';
            $order->company_logo = $generalSettings->logo ?? 'N/A';
        }

        $html = view('admin.layouts.order_report', compact('orders','courier'))->render();

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'solaimanlipi',
            'fontDir' => array_merge($fontDirs, [public_path('assets/admin/fonts')]),
            'fontdata' => $fontData + [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                ]
            ],
            'default_font_size' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('orders_report.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }

}
