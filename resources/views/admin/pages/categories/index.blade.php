@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="card-title">All Categories</div>
        </div>
        <div class="card-body table-responsive">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary mb-4">
                <i class="fas fa-plus"></i> Create New Category
            </a>

            <div class="mt-4">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
            </div>

            <table class="table table-striped table-hover table-head-bg-primary mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Slug</th>
                        <th>Image</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->parent->name ?? '-' }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>
                            @if($category->image)
                                <img src="{{ Storage::url($category->image) }}" width="50" alt="Category Image">
                            @else
                                No image
                            @endif
                        </td>
                        <td>{{ $category->products_count }}</td>
                        <td>
                            @include('admin.pages.categories.partials.__actions', ['category' => $category])
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No categories found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

             @include('admin.modal.deletemodal')

            <div class="mt-4">
                {{ $categories->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
