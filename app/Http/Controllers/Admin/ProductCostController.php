<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\ProductCost;
use Illuminate\Http\Request;
use App\Exports\ProductCostExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ProductCostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Products with their costs and order items
        $query = Product::with(['costs', 'orderItems.order' => function($q) {
            $q->where('status', 'delivered');
        }]);
        $query->where(function($q) {
            $q->whereHas('orderItems.order', function ($sub) {
                $sub->where('status', 'delivered');
            })
            ->orWhereHas('costs');
        });

        // Search filter
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Get delivered orders for date filter
        $deliveredOrdersQuery = Order::where('status', 'delivered');

        // Date filter
        if ($request->from_date && $request->to_date) {
            $fromDate = $request->from_date;
            $toDate = $request->to_date;

            $query->where(function($q) use ($fromDate, $toDate) {
                $q->whereHas('costs', function($costQuery) use ($fromDate, $toDate) {
                    $costQuery->whereBetween('created_at', [$fromDate, $toDate]);
                })
                ->orWhereHas('orderItems.order', function($orderQuery) use ($fromDate, $toDate) {
                    $orderQuery->where('status', 'delivered')
                               ->whereBetween('created_at', [$fromDate, $toDate]);
                });
            });

            $deliveredOrdersQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $products = $query->paginate(15)->withQueryString();

        // Calculate totals for each product
        $products->each(function ($product) use ($request, $deliveredOrdersQuery) {
            // Total costs with date filter
            $costsQuery = $product->costs();
            if ($request->from_date && $request->to_date) {
                $costsQuery->whereBetween('created_at', [$request->from_date, $request->to_date]);
            }
            $totalAdditionalCost = $costsQuery->sum('amount');
            $product->total_additional_cost = $totalAdditionalCost;

            // Get delivered orders for this product with date filter
            $deliveredOrders = clone $deliveredOrdersQuery;
            $deliveredOrders->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            });

            // Calculate product sales
            $totalSold = 0;
            $totalRevenue = 0; // Total sell price
            $totalBuyPrice = 0; // Total buy price

            $deliveredOrdersList = $deliveredOrders->get();
            foreach ($deliveredOrdersList as $order) {
                foreach ($order->items as $item) {
                    if ($item->product_id == $product->id) {
                        $totalSold += $item->quantity;
                        $totalRevenue += ($item->price * $item->quantity); // Sell price
                        $totalBuyPrice += ($product->buy_price * $item->quantity); // Buy price
                    }
                }
            }

            // Calculate profit: Sell Price - (Buy Price + Additional Costs)
            $totalCost = $totalBuyPrice + $totalAdditionalCost;
            $totalProfit = $totalRevenue - $totalCost;

            $product->total_sold = $totalSold;
            $product->total_revenue = $totalRevenue; // Total sell price
            $product->total_buy_price = $totalBuyPrice; // Total buy price
            $product->total_cost = $totalCost; // Buy price + Additional costs
            $product->total_profit = $totalProfit;
        });

        return view('admin.pages.product-costs.index', compact('products'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::select('id', 'name', 'sku', 'buy_price', 'main_image')->get();

        return view('admin.pages.product-costs.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'cost_type'  => 'required',
            'cost_date' => 'required|date',
            'amount'     => 'required|numeric',
            'comment'    => 'nullable'
        ]);

        $product = Product::find($request->product_id);

        ProductCost::create([
            'product_id' => $product->id,
            'cost_type' => $request->cost_type,
            'cost_date' => $request->cost_date,
            'amount' => $request->amount,
            'product_buy_price' => $product->buy_price ?? 0,
            'comment' => $request->comment,
        ]);

        return redirect()->route('admin.product-costs.index')->with('success', 'Product Cost Added Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $product = Product::with(['costs', 'orderItems.order'])
                     ->findOrFail($id);

        // Date filtering for costs
        $costsQuery = $product->costs();
        if ($request->from_date && $request->to_date) {
            $costsQuery->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }
        $costs = $costsQuery->orderBy('created_at', 'desc')->get();
        $totalAdditionalCost = $costs->sum('amount');

        // Get delivered order items for this product
        $salesQuery = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) {
                $q->where('status', 'delivered');
            });

        if ($request->from_date && $request->to_date) {
            $salesQuery->whereHas('order', function ($q) use ($request) {
                $q->whereBetween('created_at', [$request->from_date, $request->to_date]);
            });
        }

        $sales = $salesQuery->with('order')->get();

        // Calculate totals
        $totalSold = $sales->sum('quantity');
        $totalRevenue = $sales->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $totalBuyPrice = $totalSold * $product->buy_price;
        $totalCost = $totalBuyPrice + $totalAdditionalCost;
        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        return view('admin.pages.product-costs.show', compact(
            'product',
            'costs',
            'sales',
            'totalAdditionalCost',
            'totalBuyPrice',
            'totalCost',
            'totalSold',
            'totalRevenue',
            'totalProfit',
            'profitMargin'
        ));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cost = ProductCost::with('product')->findOrFail($id);
        $products = Product::select('id', 'name', 'sku', 'buy_price', 'main_image')->get();

        return view('admin.pages.product-costs.edit', compact('cost', 'products'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'cost_type'  => 'required',
            'cost_date' => 'required|date',
            'amount'     => 'required|numeric',
            'comment'    => 'nullable'
        ]);

        $cost = ProductCost::findOrFail($id);
        $product = Product::find($request->product_id);

        $cost->update([
            'product_id' => $product->id,
            'cost_type' => $request->cost_type,
            'cost_date' => $request->cost_date,
            'amount' => $request->amount,
            'product_buy_price' => $product->buy_price ?? 0,
            'comment' => $request->comment,
        ]);

        return redirect()->route('admin.product-costs.show', $product->id)
                         ->with('success', 'Product Cost Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cost = ProductCost::findOrFail($id);
        $productId = $cost->product_id;
        $cost->delete();

        return redirect()->route('admin.product-costs.show', $productId)
                         ->with('success', 'Product Cost Deleted Successfully');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new ProductCostExport(
                $request->from_date,
                $request->to_date,
                $request->search,
                $request->selected_ids
            ),
            'product_costs.xlsx'
        );
    }

}
