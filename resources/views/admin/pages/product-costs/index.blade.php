@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h3>Product Cost & Profit Analysis</h3>
        <a href="{{ route('admin.product-costs.create') }}" class="btn btn-primary ">
            + Add New Cost
        </a>
    </div>

    @include('admin.layouts.partials.__alerts')

    {{-- Search + Filter --}}
    <form method="GET" class="row g-2 mb-3" style="width: 100%; background:none; border: none;">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control"
                   placeholder="Search Product Name or SKU"
                   value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="from_date" class="form-control"
                   value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="to_date" class="form-control"
                   value="{{ request('to_date') }}">
        </div>
        <div class="col-md-2 d-flex">
            <button class="btn btn-primary me-2">Filter</button>
            @if(request('search') || request('from_date') || request('to_date'))
                <a href="{{ route('admin.product-costs.index') }}"
                   class="btn btn-outline-danger"
                   title="Reset">
                    <i class="fas fa-sync-alt"></i>
                </a>
            @endif
        </div>
        <div class="col-md-3">
            <button type="button" id="downloadSelected"
                    class="btn btn-success">
                Download Excel
            </button>
        </div>
    </form>

    {{-- Hidden Excel Form --}}
    <form id="downloadForm" action="{{ route('admin.product-costs.export') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="selected_ids" id="selected_ids">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="from_date" value="{{ request('from_date') }}">
        <input type="hidden" name="to_date" value="{{ request('to_date') }}">
    </form>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover table-head-bg-primary mt-4">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>#</th>
                    <th>Product</th>
                    <th>Sold</th>
                    <th>Sell Price</th>
                    <th>Buy Price</th>
                    <th>Additional Cost</th>
                    {{-- <th>Total Cost</th> --}}
                    <th>Profit</th>
                    {{-- <th>Margin</th> --}}
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    @php
                        $margin = $product->total_revenue > 0
                            ? ($product->total_profit / $product->total_revenue) * 100
                            : 0;
                    @endphp
                    <tr>
                        <td><input type="checkbox" class="row-check" value="{{ $product->id }}"></td>
                        <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                        <td>
                            <strong>{{ $product->name }}</strong><br>
                            <small class="text-muted">SKU: {{ $product->sku }}</small>
                            {{-- <small>Unit Buy: {{ number_format($product->buy_price, 2) }}৳</small> --}}
                        </td>
                        <td>
                            {{ $product->total_sold }} Pcs
                        </td>
                        <td class="text-success">
                            <strong>{{ number_format($product->total_revenue, 2) }}৳</strong><br>
                            {{-- <small>Avg: {{ $product->total_sold > 0 ? number_format($product->total_revenue / $product->total_sold, 2) : 0 }}৳/unit</small> --}}
                        </td>
                        <td class="text-warning">
                            {{ number_format($product->total_buy_price, 2) }}৳<br>
                            {{-- <small>{{ number_format($product->buy_price, 2) }}৳ × {{ $product->total_sold }}</small> --}}
                        </td>
                        <td class="text-danger">
                            {{ number_format($product->total_additional_cost, 2) }}৳<br>
                            {{-- <small>Extra costs</small> --}}
                        </td>
                        {{-- <td class="text-danger">
                            <strong>{{ number_format($product->total_cost, 2) }}৳</strong><br>
                            <small>Buy + Additional</small>
                        </td> --}}
                        <td class="{{ $product->total_profit >= 0 ? 'text-success' : 'text-danger' }}">
                            <strong>{{ number_format($product->total_profit, 2) }}৳</strong><br>
                            {{-- <small>Per unit: {{ $product->total_sold > 0 ? number_format($product->total_profit / $product->total_sold, 2) : 0 }}৳</small> --}}
                        </td>
                        {{-- <td>
                            <span class="badge bg-{{ $margin >= 0 ? 'success' : 'danger' }}">
                                {{ number_format($margin, 1) }}%
                            </span>
                        </td> --}}
                        <td>
                            <a href="{{ route('admin.product-costs.show', $product->id) }}?from_date={{ request('from_date') }}&to_date={{ request('to_date') }}"
                               class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-muted text-center">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <th colspan="2">Totals</th>
                    <th>{{ $products->sum('total_sold') }} units</th>
                    <th>{{ number_format($products->sum('total_revenue'), 2) }}৳</th>
                    <th>{{ number_format($products->sum('total_buy_price'), 2) }}৳</th>
                    <th>{{ number_format($products->sum('total_additional_cost'), 2) }}৳</th>
                    {{-- <th>{{ number_format($products->sum('total_cost'), 2) }}৳</th> --}}
                    <th class="{{ $products->sum('total_profit') >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($products->sum('total_profit'), 2) }}৳
                    </th>
                    <th colspan="2">
                        @php
                            $totalRevenue = $products->sum('total_revenue');
                            $totalProfit = $products->sum('total_profit');
                            $totalMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
                        @endphp
                        {{-- <span class="badge bg-{{ $totalMargin >= 0 ? 'success' : 'danger' }}">
                            {{ number_format($totalMargin, 1) }}%
                        </span> --}}
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{ $products->links('admin.layouts.partials.__pagination') }}
</div>

<script>
    // Check All
    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(ch => ch.checked = this.checked);
    });

    // Download Selected
    document.getElementById('downloadSelected').addEventListener('click', function () {
        const selected = [...document.querySelectorAll('.row-check:checked')]
            .map(ch => ch.value);

        document.getElementById('selected_ids').value =
            selected.length ? JSON.stringify(selected) : "all";

        document.getElementById('downloadForm').submit();
    });
</script>
@endsection
