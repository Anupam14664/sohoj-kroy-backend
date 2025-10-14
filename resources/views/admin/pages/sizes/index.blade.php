


@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sizes Management</h3>
            <div class="card-tools">
                <a href="{{ route('admin.sizes.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Size
                </a>
            </div>
        </div>
        <div class="card-body">
            @include('admin.layouts.partials.__alerts')

            <table class="table table-striped table-hover table-head-bg-primary mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Size Code</th>
                        <th>Display Name</th>
                        {{-- <th>Products</th> --}}
                        {{-- <th>Sort Order</th> --}}
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sizes as $size)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ ucfirst($size->type) }}</td>
                        <td>{{ $size->name }}</td>
                        <td>{{ $size->display_name ?? '-' }}</td>
                        {{-- <td>{{ $size->products_count }}</td> --}}
                        {{-- <td>{{ $size->sort_order }}</td> --}}
                        <td>
                            @include('admin.pages.sizes.partials.__actions')
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No sizes found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $sizes->links() }}
        </div>
    </div>
</div>
@endsection