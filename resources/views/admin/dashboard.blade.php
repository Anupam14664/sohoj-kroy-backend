@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Overview</h1>
    </div>

    <!-- Section 0: Low Stock Products Slider -->
    @if($lowStockProductsList->count() > 0)
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        Low Stock Products ({{ $lowStockProducts }})
                    </h6>
                </div>
                <div class="card-body">
                    <div id="lowStockCarousel" class="carousel slide" data-bs-interval="false">
                        <div class="carousel-inner">
                            @foreach($lowStockProductsList->chunk(3) as $chunkIndex => $productsChunk)
                            <div class="carousel-item @if($chunkIndex == 0) active @endif px-4">
                                <div class="row px-4 mx-0">
                                    @foreach($productsChunk as $product)
                                    <div class="col-md-4 mb-2">
                                        <div class="card border-warning m-0">
                                            <div class="card-body d-flex flex-column">
                                                <h6 class="card-title text-small" style="font-size: 18px">
                                                    {{ $product->name }}
                                                    <span class="text-muted">({{ $product->sku }})</span>
                                                </h6>

                                                @if($product->variants->count() > 0)
                                                    @foreach($product->variants as $variant)
                                                        @foreach($variant->options->where('stock', '<', 6) as $option)
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <p class="card-text mb-1" style="font-size: 14px">
                                                                    <strong>SKU:</strong> {{ $option->sku ?? 'N/A' }} |
                                                                    <strong>Stock:</strong> {{ $option->stock }}
                                                                </p>
                                                            </div>
                                                        @endforeach
                                                    @endforeach
                                                @else
                                                    {{-- যদি প্রোডাক্টে কোনো ভ্যারিয়েন্ট না থাকে --}}
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <p class="card-text mb-1">
                                                            Stock: <strong>{{ $product->total_stock }}</strong>
                                                        </p>
                                                    </div>
                                                @endif

                                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                                class="btn btn-sm btn-warning mt-2">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach

                            <!-- Left & Right Buttons -->
                            <button class="carousel-control-prev bg-black mb-2" type="button" data-bs-target="#lowStockCarousel" data-bs-slide="prev" style="width: 40px;">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next bg-black mb-2" type="button" data-bs-target="#lowStockCarousel" data-bs-slide="next" style="width: 40px;">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif





    <!-- Section 1: Summary Cards -->
    <div class="row mb-4">
        <!-- Today's Orders -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayOrders }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Today's Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">&#2547;{{ number_format($todayAmount, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProducts }}</div>
                            @if($lowStockProducts > 0)
                            <div class="mt-2">
                                <span class="badge badge-warning">{{ $lowStockProducts }} low stock</span>
                            </div>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Profit -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Today's Profit</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">&#2547;{{ number_format($todayProfit, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Date Filtered Data -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex  align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Date Range Report</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="mb-4" id="dateFilterForm">
                        <div class="row">
                            <!-- Start Date -->
                            <div class="col-md-4 col-sm-6 mb-3 mb-md-0">
                                <div class="form-group">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                        value="{{ request('start_date') }}" required>
                                </div>
                            </div>

                            <!-- End Date -->
                            <div class="col-md-4 col-sm-6 mb-3 mb-md-0">
                                <div class="form-group">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                        value="{{ request('end_date') }}" required>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group h-100 d-flex align-items-end">
                                    <div class="d-flex w-100">
                                        <input type="hidden" name="date_filter" value="1">
                                        <button type="submit" class="btn btn-primary flex-grow-1 me-2">
                                            <i class="fas fa-filter me-1"></i> Generate Report
                                        </button>
                                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary flex-grow-1" id="resetFilter">
                                            <i class="fas fa-undo me-1"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <!-- Orders Card -->
                        <div class="col-md-3 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-shopping-cart fa-2x text-primary mb-3"></i>
                                    <h5 class="card-title">Total Orders</h5>
                                    <h2 class="mb-0">
                                        @if(request('date_filter'))
                                            {{ $dateRangeOrders }}
                                        @else
                                            {{ $totalOrders }}
                                        @endif
                                    </h2>
                                </div>
                            </div>
                        </div>

                        <!-- Revenue Card -->
                        <div class="col-md-3 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-money-bill-wave fa-2x text-success mb-3"></i>
                                    <h5 class="card-title">Total Revenue</h5>
                                    <h2 class="mb-0">
                                        @if(request('date_filter'))
                                        &#2547;{{ number_format($dateRangeAmount, 2) }}
                                        @else
                                        &#2547;{{ number_format($totalAmount, 2) }}
                                        @endif
                                    </h2>
                                </div>
                            </div>
                        </div>

                        <!-- Products Sold Card -->
                        <div class="col-md-3 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-boxes fa-2x text-warning mb-3"></i>
                                    <h5 class="card-title">Products Sold</h5>
                                    <h2 class="mb-0">
                                        @if(request('date_filter'))
                                            {{ $dateRangeProductsSold }}
                                        @else
                                            {{ $totalProductsSold }}
                                        @endif
                                    </h2>
                                </div>
                            </div>
                        </div>

                        <!-- Profit Card -->
                        <div class="col-md-3 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-2x text-danger mb-3"></i>
                                    <h5 class="card-title">Total Profit</h5>
                                    <h2 class="mb-0">
                                        @if(request('date_filter'))
                                        &#2547;{{ number_format($dateRangeProfit, 2) }}
                                        @else
                                        &#2547;{{ number_format($totalProfit, 2) }}
                                        @endif
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Recent Orders -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-head-bg-primary mt-4" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->name }}</td>
                                    <td>{{ $order->phone }}</td>
                                    <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                                    <td>&#2547;{{ number_format($order->total, 2) }}</td>
                                    <td>
                                        <span class="badge
                                            @if($order->status == 'completed') badge-success
                                            @elseif($order->status == 'processing') badge-primary
                                            @elseif($order->status == 'cancelled') badge-danger
                                            @else badge-secondary
                                            @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No recent orders found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Clear form inputs when reset button is clicked
        document.getElementById('resetFilter').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('dateFilterForm').reset();
            window.location.href = "{{ route('admin.dashboard') }}";
        });

        // Clear the filter when page is refreshed without form submission
        if(performance.navigation.type == 1) {
            if(!window.location.search.includes('date_filter')) {
                document.getElementById('dateFilterForm').reset();
            }
        }
    });
</script>
@endsection


<style>
    #dateFilterForm {
        width: 100%;
    }

    @media (max-width: 768px) {
        #dateFilterForm .form-group {
            margin-bottom: 15px;
        }

        #dateFilterForm .btn {
            padding: 8px 12px;
            font-size: 14px;
        }
    }

    @media (max-width: 576px) {
        #dateFilterForm .d-flex {
            flex-direction: column;
        }

        #dateFilterForm .me-2 {
            margin-right: 0 !important;
            margin-bottom: 10px;
        }
    }
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    }
    .border-left-primary { border-left: 0.25rem solid #4e73df !important; }
    .border-left-success { border-left: 0.25rem solid #1cc88a !important; }
    .border-left-info { border-left: 0.25rem solid #36b9cc !important; }
    .border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
    .border-left-secondary { border-left: 0.25rem solid #858796 !important; }
    .table-responsive { overflow-x: auto; }
    .table { font-size: 14px; }
    .table th { white-space: nowrap; }
    .badge { font-size: 12px; padding: 5px 10px; font-weight: 600; }
</style>

