<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
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
        $query = ProductCost::with('product');

        if ($request->search) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // product name & sku
                $q->whereHas('product', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
                });

                // cost type
                $q->orWhere('cost_type', 'like', "%{$search}%");
            });
        }

        // date filter
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }

        $costs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.pages.product-costs.index', compact('costs'));
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
            'amount'     => 'required|numeric',
            'comment'    => 'nullable'
        ]);

        $product = Product::find($request->product_id);

        ProductCost::create([
            'product_id' => $product->id,
            'cost_type' => $request->cost_type,
            'amount' => $request->amount,
            'product_buy_price' => $product->buy_price ?? 0,
            'comment' => $request->comment,
        ]);

        return redirect()->route('admin.product-costs.index')->with('success', 'Product Cost Added Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
