@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h3>Profit Analysis: {{ $product->name }} ({{ $product->sku }})</h3>
        <div>
            <a href="{{ route('admin.product-costs.index') }}" class="btn btn-secondary">
                ← Back to List
            </a>
            <a href="{{ route('admin.product-costs.create') }}?product_id={{ $product->id }}"
               class="btn btn-primary">
                + Add Cost for This Product
            </a>
        </div>
    </div>

    {{-- Date Filter for Details Page --}}
    <form method="GET" class="row g-2 mb-4" style="width: 100%; border:none; background:none;">
        <div class="col-md-3">
            <label>From Date</label>
            <input type="date" name="from_date" class="form-control"
                   value="{{ request('from_date') }}">
        </div>
        <div class="col-md-3">
            <label>To Date</label>
            <input type="date" name="to_date" class="form-control"
                   value="{{ request('to_date') }}">
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button class="btn btn-primary form-control">Apply Filter</button>
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <a href="{{ route('admin.product-costs.show', $product->id) }}"
               class="btn btn-outline-danger form-control">
                Clear Filter
            </a>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6>Unit Buy Price</h6>
                    <h3>{{ number_format($product->buy_price, 2) }}৳</h3>
                    <small>Per unit</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Total Sold</h6>
                    <h3>{{ $totalSold }}</h3>
                    <small>Units</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Total Sell Price</h6>
                    <h3>{{ number_format($totalRevenue, 2) }}৳</h3>
                    <small>Revenue</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h6>Total Buy Price</h6>
                    <h3>{{ number_format($totalBuyPrice, 2) }}৳</h3>
                    <small>Cost of Goods</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Additional Cost</h6>
                    <h3>{{ number_format($totalAdditionalCost, 2) }}৳</h3>
                    <small>Extra Costs</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-{{ $totalProfit >= 0 ? 'info' : 'danger' }} text-white">
                <div class="card-body text-center">
                    <h6>Net Profit</h6>
                    <h3>{{ number_format($totalProfit, 2) }}৳</h3>
                    <small>{{ number_format($profitMargin, 1) }}%</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Details Table --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sales Details (Delivered Orders)</h5>
            <span class="badge bg-light text-dark">
                {{ $sales->count() }} Orders • {{ $totalSold }} Units Sold
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>Date</th>
                            <th>Qty</th>
                            <th>Unit Sell Price</th>
                            <th>Unit Buy Price</th>
                            <th>Sell Total</th>
                            <th>Buy Total</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            @php
                                $sellTotal = $sale->price * $sale->quantity;
                                $buyTotal = $product->buy_price * $sale->quantity;
                                $additionalCostPerUnit = $totalSold > 0 ? $totalAdditionalCost / $totalSold : 0;
                                $additionalCostForThisOrder = $additionalCostPerUnit * $sale->quantity;
                                $orderProfit = $sellTotal - ($buyTotal + $additionalCostForThisOrder);
                            @endphp
                            <tr>
                                <td>{{ $sale->order->order_number }}</td>
                                <td>{{ $sale->order->created_at->format('Y-m-d') }}</td>
                                <td>{{ $sale->quantity }} units</td>
                                <td>{{ number_format($sale->price, 2) }}৳</td>
                                <td class="text-warning">{{ number_format($product->buy_price, 2) }}৳</td>
                                <td class="text-success">
                                    <strong>{{ number_format($sellTotal, 2) }}৳</strong>
                                </td>
                                <td class="text-danger">
                                    {{ number_format($buyTotal, 2) }}৳
                                </td>
                                <td class="{{ $orderProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    <strong>{{ number_format($orderProfit, 2) }}৳</strong><br>
                                    {{-- <small>{{ number_format($orderProfit / $sale->quantity, 2) }}৳/unit</small> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-shopping-cart fa-2x mb-2"></i><br>
                                        No sales recorded
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="2"><strong>Totals</strong></td>
                            <td><strong>{{ $totalSold }} units</strong></td>
                            <td></td>
                            <td></td>
                            <td><strong>{{ number_format($totalRevenue, 2) }}৳</strong></td>
                            <td><strong>{{ number_format($totalBuyPrice, 2) }}৳</strong></td>
                            <td class="{{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                <strong>{{ number_format($totalProfit, 2) }}৳</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Product Cost Details with Edit Button --}}
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Additional Product Cost Details</h5>
            <span class="badge bg-light text-dark">
                {{ $costs->count() }} Cost Entries • Total: {{ number_format($totalAdditionalCost, 2) }}৳
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Cost Type</th>
                            <th>Amount</th>
                            <th>Product Buy Price</th>
                            <th>Comment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($costs as $index => $cost)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $cost->cost_date }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $cost->cost_type }}</span>
                                </td>
                                <td class="text-danger">
                                    <strong>{{ number_format($cost->amount, 2) }}৳</strong>
                                </td>
                                <td>{{ number_format($cost->product_buy_price, 2) }}৳</td>
                                <td>{{ $cost->comment ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.product-costs.edit', $cost->id) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.product-costs.destroy', $cost->id) }}"
                                        method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-receipt fa-2x mb-2"></i><br>
                                        No additional costs recorded
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
