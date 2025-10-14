<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\ProductVariantOption;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Date calculations
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $currentMonth = Carbon::now()->startOfMonth();

        // Initialize all variables with default values
        $todayOrders = $yesterdayOrders = $monthlyOrders = $totalOrders = 0;
        $todayProfit = $yesterdayProfit = $monthlyProfit = $totalProfit = 0;
        $todayAmount = $yesterdayAmount = $monthlyAmount = $totalAmount = 0;
        $totalProducts = $lowStockProducts = $totalProductsSold = 0;

        // Order statistics
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $yesterdayOrders = Order::whereDate('created_at', $yesterday)->count();
        $monthlyOrders = Order::where('created_at', '>=', $currentMonth)->count();
        $totalOrders = Order::count();

        // Amount calculations (total order value)
        $todayAmount = Order::whereDate('created_at', $today)->sum('total');
        $yesterdayAmount = Order::whereDate('created_at', $yesterday)->sum('total');
        $monthlyAmount = Order::where('created_at', '>=', $currentMonth)->sum('total');
        $totalAmount = Order::sum('total');

        // Profit calculations (updated to consider buy price)
        $todayProfit = $this->calculateProfit(Order::whereDate('created_at', $today)->with('items')->get());
        $yesterdayProfit = $this->calculateProfit(Order::whereDate('created_at', $yesterday)->with('items')->get());
        $monthlyProfit = $this->calculateProfit(Order::where('created_at', '>=', $currentMonth)->with('items')->get());
        $totalProfit = $this->calculateProfit(Order::with('items')->get());

        $totalProducts = Product::count();
        $totalProductsSold = OrderItem::sum('quantity');

        $lowStockProductsList = Product::with(['variants.options'])
            ->where(function ($query) {
                $query->whereDoesntHave('variants', function ($q) {
                    $q->has('options');
                })->where('total_stock', '<', 6);

                $query->orWhereHas('variants.options', function ($q) {
                    $q->where('stock', '<', 6);
                });
            })
            ->orderBy('name', 'asc')
            ->get();

        $lowStockProducts = $lowStockProductsList->count();


        // Total products sold calculation
        $totalProductsSold = OrderItem::sum('quantity');

        // Recent orders for the activity feed
        $recentOrders = Order::with('items')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Best selling products
        $bestSellers = OrderItem::selectRaw('product_id, product_name, price, sum(quantity) as total_sold')
            ->with('product')
            ->groupBy('product_id', 'product_name', 'price')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // Date range statistics (if filter is applied)
        $dateFilter = $request->input('date_filter');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $dateRangeOrders = null;
        $dateRangeProductsSold = null;
        $dateRangeProfit = null;
        $dateRangeAmount = null;

        if ($dateFilter && $startDate && $endDate) {
            $dateRangeOrders = Order::whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])->count();

            $dateRangeProductsSold = OrderItem::whereHas('order', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            })->sum('quantity');

            $dateRangeOrdersCollection = Order::whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])->with('items')->get();

            $dateRangeProfit = $this->calculateProfit($dateRangeOrdersCollection);
            $dateRangeAmount = Order::whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])->sum('total');
        }

        $data = [
            // Orders
            'todayOrders' => $todayOrders,
            'yesterdayOrders' => $yesterdayOrders,
            'monthlyOrders' => $monthlyOrders,
            'totalOrders' => $totalOrders,

            // Amounts
            'todayAmount' => $todayAmount,
            'yesterdayAmount' => $yesterdayAmount,
            'monthlyAmount' => $monthlyAmount,
            'totalAmount' => $totalAmount,

            // Profits
            'todayProfit' => $todayProfit,
            'yesterdayProfit' => $yesterdayProfit,
            'monthlyProfit' => $monthlyProfit,
            'totalProfit' => $totalProfit,

            // Products
            'totalProducts' => $totalProducts,
            'lowStockProducts' => $lowStockProducts,
            'totalProductsSold' => $totalProductsSold,
            'lowStockProductsList' => $lowStockProductsList,

            // Recent orders and best sellers
            'recentOrders' => $recentOrders,
            'bestSellers' => $bestSellers,

            // Date range filter
            'dateFilter' => $dateFilter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateRangeOrders' => $dateRangeOrders,
            'dateRangeProductsSold' => $dateRangeProductsSold,
            'dateRangeProfit' => $dateRangeProfit,
            'dateRangeAmount' => $dateRangeAmount,
        ];

        return view('admin.dashboard', $data);
    }

    /**
     * Calculate profit by subtracting buy price from order price for each item
     */
    private function calculateProfit($orders)
    {
        $profit = 0;

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $buyPrice = 0;

                if ($item->variant_id) {
                    // For variant products, get the buy price from the variant option
                    $variantOption = ProductVariantOption::find($item->option_id);
                    if ($variantOption) {
                        $buyPrice = $variantOption->buy_price ?? 0;
                    }
                } else {
                    // For regular products, get the buy price from the product
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $buyPrice = $product->buy_price ?? 0;
                    }
                }

                // Calculate profit for this item: (sale price - buy price) * quantity
                $itemProfit = ($item->price - $buyPrice) * $item->quantity;
                $profit += $itemProfit;
            }

            // Subtract any discount from the profit
            $profit -= $order->discount;
        }

        return $profit;
    }
}
