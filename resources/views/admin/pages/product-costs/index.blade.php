@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Product Cost List</h3>
        <a href="{{ route('admin.product-costs.create') }}" class="btn btn-primary">
            + Add New Cost
        </a>
    </div>


    @include('admin.layouts.partials.__alerts')
    {{-- Search + Filter --}}
    <form method="GET" class="row g-2 mb-3" style="width: 100%;">

        {{-- ONE SEARCH INPUT --}}
        <div class="col-md-4">
            <input type="text" name="search" class="form-control"
                   placeholder="Search: Product Name / SKU / Cost Type"
                   value="{{ request('search') }}">
        </div>

        {{-- From Date --}}
        <div class="col-md-2">
            <input type="date" name="from_date" class="form-control"
                   value="{{ request('from_date') }}">
        </div>

        {{-- To Date --}}
        <div class="col-md-2">
            <input type="date" name="to_date" class="form-control"
                   value="{{ request('to_date') }}">
        </div>

        {{-- Search Button --}}
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Search</button>
        </div>

        {{-- Download Button --}}
        <div class="col-md-2">
            <button type="button" id="downloadSelected"
                    class="btn btn-success w-100">
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
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Cost Type</th>
                    <th>Amount</th>
                    <th>Buy Price</th>
                    <th>Comment</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($costs as $cost)
                    <tr>
                        <td><input type="checkbox" class="row-check" value="{{ $cost->id }}"></td>

                        <td>{{ $cost->id }}</td>

                        <td>
                            <strong>{{ $cost->product->name }}</strong><br>
                            <small class="text-muted">SKU: {{ $cost->product->sku }}</small>
                        </td>

                        <td>{{ $cost->cost_type }}</td>

                        <td>{{ number_format($cost->amount, 2) }} ৳</td>

                        <td>{{ number_format($cost->product_buy_price, 2) }} ৳</td>

                        <td>{{ $cost->comment ?? '-' }}</td>

                        <td>{{ $cost->created_at->format('Y-m-d') }}</td>
                    </tr>

                @empty
                    <tr><td colspan="8" class="text-muted text-center">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $costs->links() }}

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
