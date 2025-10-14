@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Couriers Management</h3>
            <div class="card-tools">
                <a href="{{ route('admin.couriers.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Courier
                </a>
            </div>
        </div>
        <div class="card-body table-responsive">
            @include('admin.layouts.partials.__alerts')

            <table class="table table-striped table-hover table-head-bg-primary mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Base URL</th>
                        <th>Order Endpoint</th>
                        <th>API Key</th>
                        <th>Secret Key</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($couriers as $key => $courier)
                    <tr>
                        <td>{{ $loop->iteration + ($couriers->currentPage() - 1) * $couriers->perPage() }}</td>
                        <td>{{ $courier->name }}</td>
                        <td>{{ $courier->base_url }}</td>
                        <td>{{ $courier->create_order_endpoint }}</td>
                        <td>{{ Str::limit($courier->api_key, 20) }}</td>
                        <td>{{ Str::limit($courier->secret_key, 20) }}</td>
                        <td>
                            @if($courier->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        {{-- <td>{{ $courier->created_at->format('Y-m-d') }}</td> --}}
                        <td>
                            @include('admin.pages.couriers.partials.__actions')
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No courier found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


             @include('admin.modal.deletemodal')

        <div class="card-footer clearfix">
            <div class="mt-4">
                {{ $couriers->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
