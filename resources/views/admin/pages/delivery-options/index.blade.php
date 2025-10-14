@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Delivery Options</h3>
            <div class="card-tools">
                <a href="{{ route('admin.delivery-options.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Delivery Option
                </a>
            </div>
        </div>
        <div class="card-body">
            @include('admin.layouts.partials.__alerts')

            <table class="table table-striped table-hover table-head-bg-primary mt-4">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Charge</th>
                        <th>Free Delivery</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveryOptions as $option)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $option->name }}</td>
                        <td>{{ number_format($option->charge, 2) }}</td>
                        <td>
                            @if($option->is_free_for_products)
                                <span class="badge badge-success">Yes</span>
                                <a href="{{ route('admin.delivery-options.manage-products', $option->id) }}"
                                   class="btn btn-xs btn-info ml-2">
                                    Manage Products
                                </a>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $option->is_active ? 'success' : 'danger' }}">
                                {{ $option->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @include('admin.pages.delivery-options.partials.__actions')
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No Delivery Options found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            <div class="mt-4">
                {{ $deliveryOptions->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection