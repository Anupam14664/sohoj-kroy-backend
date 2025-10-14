@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Shipped Orders</h3>


                </div>

                <div class="card-body">

                    <div class="card-tools">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <!-- Combined Filter Form -->
                            <form style="width: 700px; background:none; border:none;" action="{{ route('admin.orders.shipped') }}" method="GET" class="mb-0 d-flex flex-wrap align-items-end gap-2">
                                <!-- Courier Select -->
                                <div class="form-group mb-0">
                                    <label class="form-label">Courier</label>
                                    <select name="courier_service_id" class="form-control form-control-sm">
                                        <option value="">All Couriers</option>
                                        @foreach($couriers as $courier)
                                            <option value="{{ $courier->id }}" {{ request('courier_service_id') == $courier->id ? 'selected' : '' }}>
                                                {{ $courier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Date From -->
                                <div class="form-group mb-0">
                                    <label class="form-label">From</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" style="width: 120px;">
                                </div>

                                <!-- Date To -->
                                <div class="form-group mb-0">
                                    <label class="form-label">To</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}" style="width: 120px;">
                                </div>

                                <!-- Filter Button -->
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </form>

                            <!-- Compact Search Form -->
                            <form style="width: 200px; background:none; border:none;" action="{{ route('admin.orders.shipped') }}" method="GET" class="mb-2 d-flex">
                                <div class="input-group input-group-sm" style="width: 180px;">
                                    <input type="text"
                                           name="tracking_code"
                                           class="form-control"
                                           placeholder="Tracking #"
                                           value="{{ request('tracking_code') }}"
                                           aria-label="Search by tracking code">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-outline-secondary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>


                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-head-bg-primary mt-4">
                            <thead>
                                <tr>
                                    <th>Order#</th>
                                    <th>Customer</th>
                                    <th>Courier</th>
                                    <th>Tracking Code</th>
                                    <th>Shipping Date</th>
                                    <th>Status</th>
                                    <th>Track Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->name }}<br>{{ $order->phone }}</td>
                                    <td>

                                            {{ $order->courier->name ?? 'N/A' }}

                                    </td>
                                    <td>{{ $order->tracking_code ?? 'N/A' }}</td>
                                    <td>{{ $order->updated_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $order->delivery_status_code }}</span>
                                        {{-- <br>
                                        <small>{{ $order->delivery_status_description }}</small> --}}
                                    </td>

                                    <td>
                                        @if($order->tracking_code)
                                            <a href="https://steadfast.com.bd/t/{{ $order->tracking_code }}"
                                            target="_blank"
                                            class="btn btn-sm btn-info">
                                            Track
                                            </a>
                                        @else
                                            <span class="text-muted">No tracking</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer clearfix">
                    {{ $orders->appends(request()->query())->links('admin.layouts.partials.__pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
