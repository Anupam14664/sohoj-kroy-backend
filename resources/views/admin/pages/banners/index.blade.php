@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="card-title">All Banners</div>
        </div>
        <div class="card-body table-responsive">
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary mb-4">
                <i class="fas fa-plus"></i> Create New Banners
            </a>

            <div class="mt-4">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            </div>

            <table class="table table-striped table-hover table-head-bg-primary mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Image</th>
                        <th>Page Type</th>
                        <th>Position</th>
                        {{-- <th>Order</th> --}}
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($banners as $banner)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $banner->title }}</td>
                        <td>
                            <img src="{{ $banner->image }}" alt="{{ $banner->title }}" style="max-width: 100px; max-height: 50px;">
                        </td>
                        <td>{{ ucfirst($banner->page_type) }}</td>
                        <td>{{ $banner->position ? ucfirst($banner->position) : 'N/A' }}</td>
                        {{-- <td>{{ $banner->order }}</td> --}}
                        <td>
                            <span class="badge badge-{{ $banner->is_active ? 'success' : 'danger' }}">
                                {{ $banner->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            {{-- <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this banner?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form> --}}
                            @include('admin.pages.banners.partials.__actions')
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No banners found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @include('admin.modal.deletemodal')
            <div class="mt-4">
                {{ $banners->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
